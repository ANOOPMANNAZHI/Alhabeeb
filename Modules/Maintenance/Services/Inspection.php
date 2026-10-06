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
    // roles that carry out inspections (have buildings); managers verify anybody's issue
    const EXECUTIVE_ROLES = ['maintenance_executive', 'maintenance_supervisor'];
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

    /** Active users holding an executive role: id => name. */
    public static function executives()
    {
        return DB::table('users as u')
            ->join('model_has_roles as m', function ($j) { $j->on('m.model_id', '=', 'u.id')->where('m.model_type', 'App\User'); })
            ->join('roles as r', 'r.id', '=', 'm.role_id')
            ->leftJoin('employees as e', 'e.id', '=', 'u.user_type_id')
            ->whereIn('r.name', self::EXECUTIVE_ROLES)->where('u.user_type_status', 1)
            ->select('u.id', DB::raw('coalesce(e.employee_name, u.username) as name'))
            ->distinct()->orderBy('name')->pluck('name', 'id')->all();
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
