<?php

namespace Modules\Maintenance\Services;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;

/**
 * Building Inspection & Maintenance: codes, who sees what, and photo storage.
 * Runs on PHP < 7.4: no arrow functions.
 */
class Inspection
{
    const PRIORITIES = [1 => 'Critical', 2 => 'High', 3 => 'Medium', 4 => 'Low'];
    const TARGET_DAYS = [1 => 1, 2 => 7, 3 => 15, 4 => 30];
    const ISSUE_STATUSES = [1 => 'Open', 2 => 'In Progress', 3 => 'Resolved', 4 => 'Closed'];
    const OPEN = 1, IN_PROGRESS = 2, RESOLVED = 3, CLOSED = 4;
    const SOURCES = [1 => 'Inspection', 2 => 'Manual'];
    const VISIT_TYPES = [1 => 'Monthly inspection', 2 => 'Follow-up', 3 => 'Additional inspection'];
    const MONTHLY = 1;
    const VISIT_STATUSES = [0 => 'Scheduled', 2 => 'Completed', 9 => 'Cancelled'];
    const SCHEDULED = 0, COMPLETED = 2, CANCELLED = 9;
    const RESULTS = [1 => 'OK', 2 => 'Issue', 3 => 'N/A'];
    const CONDITIONS = [1 => 'Still open', 2 => 'Resolved', 3 => 'Worsened'];
    const PARTIES = ['In-house technician', 'Contractor', 'Landlord', 'Tenant'];
    const PHOTO_KINDS = [1 => 'Before', 2 => 'Progress', 3 => 'After'];
    // roles that carry out inspections by role; any active employee can also be assigned (user, 07/10/2026)
    const EXECUTIVE_ROLES = ['maintenance_executive', 'maintenance_supervisor'];
    // given straight to a person while they have a building, a scheduled visit or an open issue (syncAccess)
    const EXECUTIVE_PERMISSIONS = ['inspection_view', 'inspection_visit_create', 'inspection_execute', 'inspection_issue_create', 'inspection_complaint_create'];
    const MANAGER_ROLES = ['super_admin', 'maintenance_manager'];
    const PHOTO_DIR = 'public/InspectionPhotos';

    /** Sees every building, visit and issue. */
    public static function seesAll($user = null)
    {
        $user = $user ?: \Auth::user();
        return $user->can('inspection_view_all');
    }

    public static function isManager($user = null)
    {
        $user = $user ?: \Auth::user();
        return $user->hasAnyRole(self::MANAGER_ROLES);
    }

    /** Building ids the user may work with: null = all. */
    public static function buildingIds($user = null)
    {
        $user = $user ?: \Auth::user();
        if (self::seesAll($user)) {
            return null;
        }
        return DB::table('building_executives')->whereNull('assigned_till')->where('user_id', $user->id)
            ->pluck('building_id')->map(function ($v) { return (int) $v; })->all();
    }

    /** Limit a query on a table with building_id / executive_id to what the user may see. */
    public static function scope($query, $alias, $user = null)
    {
        $user = $user ?: \Auth::user();
        $ids = self::buildingIds($user);
        if ($ids === null) {
            return $query;
        }
        return $query->where(function ($q) use ($alias, $ids, $user) {
            $q->whereIn($alias . '.building_id', $ids ?: [0])->orWhere($alias . '.executive_id', $user->id);
        });
    }

    /**
     * People who can be given buildings, visits and issues: every active employee (active login +
     * active employee record), whatever their role (user, 07/10/2026): id => name.
     */
    public static function executives()
    {
        return DB::table('users as u')
            ->join('employees as e', 'e.id', '=', 'u.user_type_id')
            ->where('u.user_type', 'employee')->where('u.user_type_status', 1)->where('e.employee_status', 1)
            ->select('u.id', DB::raw('coalesce(e.employee_name, u.username) as name'))
            ->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Screen access follows the assignment: a person with a building, a scheduled visit or an open
     * issue gets EXECUTIVE_PERMISSIONS directly (their role is untouched); with none left, those direct
     * permissions are taken away again. What their role gives them never changes.
     */
    public static function syncAccess($userIds)
    {
        $names = DB::table('permissions')->whereIn('name', self::EXECUTIVE_PERMISSIONS)->pluck('name')->all();
        if (!$names) {
            return;
        }
        foreach (array_unique(array_filter(array_map('intval', (array) $userIds))) as $userId) {
            $user = \App\User::find($userId);
            if (!$user) {
                continue;
            }
            $assigned = DB::table('building_executives')->where('user_id', $userId)->whereNull('assigned_till')->exists()
                || DB::table('inspection_visits')->where('executive_id', $userId)->where('status', self::SCHEDULED)->exists()
                || DB::table('inspection_issues')->where('executive_id', $userId)->where('status', '<>', self::CLOSED)->exists();
            $direct = $user->getDirectPermissions()->pluck('name')->all();
            if ($assigned) {
                // only what neither the role nor an earlier grant gives them
                $missing = array_values(array_diff($names, $direct, $user->getPermissionsViaRoles()->pluck('name')->all()));
                if ($missing) {
                    $user->givePermissionTo($missing);
                }
            } else {
                foreach (array_intersect($names, $direct) as $name) {
                    $user->revokePermissionTo($name);
                }
            }
        }
    }

    public static function userName($userId)
    {
        if (!$userId) {
            return '';
        }
        $r = DB::table('users as u')->leftJoin('employees as e', 'e.id', '=', 'u.user_type_id')->where('u.id', $userId)
            ->select(DB::raw('coalesce(e.employee_name, u.username) as name'))->first();
        return $r ? $r->name : '';
    }

    /** Names for many users at once: id => name. */
    public static function userNames($ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids))));
        if (!$ids) {
            return [];
        }
        return DB::table('users as u')->leftJoin('employees as e', 'e.id', '=', 'u.user_type_id')->whereIn('u.id', $ids)
            ->select('u.id', DB::raw('coalesce(e.employee_name, u.username) as name'))->pluck('name', 'id')->all();
    }

    /** Current executive (user id) of a building, or null. */
    public static function buildingExecutive($buildingId)
    {
        return DB::table('building_executives')->where('building_id', $buildingId)->whereNull('assigned_till')->value('user_id');
    }

    /** Active buildings the user may pick: id => name. */
    public static function buildings($user = null)
    {
        $ids = self::buildingIds($user);
        $q = DB::table('buildings')->where('building_status', 1);
        if ($ids !== null) {
            $q->whereIn('id', $ids ?: [0]);
        }
        return $q->orderBy('building_name')->pluck('building_name', 'id')->all();
    }

    /** Issue categories = active complaint Works (Maintenance or Both): id => name. */
    public static function categories()
    {
        return DB::table('works')->where('works_status', 1)->whereIn('works_type', [0, 1])->orderBy('works_code')
            ->pluck('works_code', 'id')->all();
    }

    public static function targetDate($priority, $from = null)
    {
        $days = isset(self::TARGET_DAYS[$priority]) ? self::TARGET_DAYS[$priority] : 15;
        return date('Y-m-d', strtotime(($from ?: date('Y-m-d')) . " +{$days} days"));
    }

    /** The executive of the issue, or a manager, may work on it. */
    public static function canWork($issue, $user = null)
    {
        $user = $user ?: \Auth::user();
        if ((int) $issue->status === self::CLOSED) {
            return false;
        }
        return (int) $issue->executive_id === (int) $user->id || self::isManager($user);
    }

    /** Verify & Close / Send back: managers any resolved issue; supervisors not their own. */
    public static function canVerify($issue, $user = null)
    {
        $user = $user ?: \Auth::user();
        if ((int) $issue->status !== self::RESOLVED || !$user->can('inspection_verify')) {
            return false;
        }
        if (self::isManager($user)) {
            return true;
        }
        return (int) $issue->executive_id !== (int) $user->id && (int) $issue->raised_by !== (int) $user->id;
    }

    /** Store an uploaded photo (resized, with thumbnail) and record it. Returns the photo id. */
    public static function storePhoto(UploadedFile $file, $issueId, $kind, $updateId = null)
    {
        $tmp = storage_path('app/inspection_tmp');
        if (!is_dir($tmp)) {
            mkdir($tmp, 0775, true);
        }
        $name = uniqid('ins', true) . '.jpg';
        $img = Image::make($file->getRealPath());
        if (function_exists('exif_read_data')) {
            $img->orientate(); // phone photos come rotated otherwise
        }
        $img->resize(1600, 1600, function ($c) { $c->aspectRatio(); $c->upsize(); })->save($tmp . '/' . $name, 80);
        $path = Storage::putFile(self::PHOTO_DIR, new File($tmp . '/' . $name), 'public');
        $img->resize(320, 320, function ($c) { $c->aspectRatio(); $c->upsize(); })->save($tmp . '/' . $name, 75);
        $thumb = Storage::putFile(self::PHOTO_DIR, new File($tmp . '/' . $name), 'public');
        @unlink($tmp . '/' . $name);
        return DB::table('inspection_photos')->insertGetId([
            'issue_id' => $issueId, 'update_id' => $updateId, 'kind' => $kind, 'path' => $path, 'thumb_path' => $thumb,
            'created_by' => \Auth::id(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public static function addUpdate($issueId, $kind, $note, $visitId = null, $condition = null)
    {
        return DB::table('inspection_issue_updates')->insertGetId([
            'issue_id' => $issueId, 'visit_id' => $visitId, 'kind' => $kind, 'condition' => $condition, 'note' => $note,
            'created_by' => \Auth::id(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Create an issue (from an inspection or by hand) with its before photo. Returns the id. */
    public static function createIssue(array $d, $photo = null)
    {
        $priority = (int) $d['priority'];
        $id = DB::table('inspection_issues')->insertGetId([
            'building_id' => $d['building_id'], 'unit_id' => isset($d['unit_id']) && $d['unit_id'] ? $d['unit_id'] : null,
            'area_id' => isset($d['area_id']) && $d['area_id'] ? $d['area_id'] : null,
            'checklist_item_id' => isset($d['checklist_item_id']) ? $d['checklist_item_id'] : null,
            'work_id' => $d['work_id'], 'priority' => $priority, 'status' => self::OPEN, 'source' => $d['source'],
            'visit_id' => isset($d['visit_id']) ? $d['visit_id'] : null, 'executive_id' => $d['executive_id'],
            'raised_by' => \Auth::id(), 'raised_at' => now(), 'target_date' => self::targetDate($priority),
            'description' => trim($d['description']), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('inspection_issues')->where('id', $id)->update(['issue_no' => 'INS-' . date('y') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT)]);
        $updateId = self::addUpdate($id, 'raised', ($d['source'] == 1 ? 'Raised during building inspection.' : 'Issue recorded directly.'),
            isset($d['visit_id']) ? $d['visit_id'] : null);
        if ($photo) {
            self::storePhoto($photo, $id, 1, $updateId);
        }
        return $id;
    }

    // fields whose changes are kept in inspection_issue_changes, with their labels
    const TRACKED = ['action_plan' => 'Action to be taken', 'responsible_party' => 'Responsible party', 'estimated_cost' => 'Estimated cost',
        'landlord_approval' => 'Landlord approval', 'target_date' => 'Target date', 'priority' => 'Priority'];

    /** Stored form of a tracked value, so "45.5" and "45.500" or true and "1" compare equal. */
    public static function normalize($field, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        switch ($field) {
            case 'estimated_cost':
                return number_format((float) $value, 3, '.', '');
            case 'landlord_approval':
                return $value ? '1' : '0';
            case 'target_date':
                return date('Y-m-d', strtotime($value));
            case 'priority':
                return (string) (int) $value;
            default:
                return trim(str_replace("\r\n", "\n", (string) $value));
        }
    }

    /** How a tracked value reads on screen. */
    public static function display($field, $value)
    {
        if ($value === null || $value === '') {
            return '—';
        }
        switch ($field) {
            case 'estimated_cost':
                return 'OMR ' . number_format((float) $value, 3);
            case 'landlord_approval':
                return $value === '1' ? 'Needed' : 'Not needed';
            case 'target_date':
                return date('d M Y', strtotime($value));
            case 'priority':
                return isset(self::PRIORITIES[(int) $value]) ? self::PRIORITIES[(int) $value] : $value;
            default:
                return $value;
        }
    }

    /**
     * Compare the issue with the new values of tracked fields and record every change.
     * @return array labels of the changed fields (empty = nothing changed)
     */
    public static function recordChanges($issue, array $new, $updateId = null)
    {
        $changed = [];
        $rows = [];
        foreach ($new as $field => $value) {
            if (!isset(self::TRACKED[$field])) {
                continue;
            }
            $old = self::normalize($field, $issue->$field);
            $value = self::normalize($field, $value);
            if ($old === $value) {
                continue;
            }
            $changed[] = self::TRACKED[$field];
            $rows[] = ['issue_id' => $issue->id, 'update_id' => $updateId, 'field' => $field, 'old_value' => $old, 'new_value' => $value,
                'changed_by' => \Auth::id(), 'changed_at' => now()];
        }
        if ($rows) {
            DB::table('inspection_issue_changes')->insert($rows);
        }
        return $changed;
    }

    /** Labels of the tracked fields that would change - without recording anything. */
    public static function wouldChange($issue, array $new)
    {
        $changed = [];
        foreach ($new as $field => $value) {
            if (isset(self::TRACKED[$field]) && self::normalize($field, $issue->$field) !== self::normalize($field, $value)) {
                $changed[] = self::TRACKED[$field];
            }
        }
        return $changed;
    }

    public static function photoUrl($path)
    {
        return asset('storage/app/' . $path);
    }

    public static function days($from, $to = null)
    {
        $a = strtotime(date('Y-m-d', strtotime($from)));
        $b = strtotime(date('Y-m-d', $to ? strtotime($to) : time()));
        return max(0, (int) floor(($b - $a) / 86400));
    }
}
