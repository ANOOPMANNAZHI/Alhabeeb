<?php

namespace Modules\BackOffice\Services\Bulk;

use App\User;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\MessageValues;
use Modules\BackOffice\Services\StaffMessages;

/**
 * Bulk send recipients: an audience (tenant_active, tenant_past, landlord,
 * staff) plus filters (locations, buildings, roles) -> one row per person
 * with the placeholder values for their message. The review screen and the
 * start of a send use the same code, so counts match what is sent.
 */
class RecipientFinder
{
    public static function templateAudience($audience)
    {
        if ($audience === 'landlord' || $audience === 'staff') {
            return $audience;
        }
        return 'tenant';
    }

    public static function people($audience, array $filters)
    {
        switch ($audience) {
            case 'tenant_active':
                return self::tenants(true, $filters);
            case 'tenant_past':
                return self::tenants(false, $filters);
            case 'landlord':
                return self::landlords($filters);
            case 'staff':
                return self::staff($filters);
        }
        throw new \InvalidArgumentException('Unknown audience: ' . $audience);
    }

    public static function count($audience, array $filters)
    {
        $email = 0;
        $mobile = 0;
        $people = self::people($audience, $filters);
        foreach ($people as $p) {
            if ($p['email']) { $email++; }
            if ($p['mobile']) { $mobile++; }
        }
        return ['total' => count($people), 'email' => $email, 'mobile' => $mobile];
    }

    public static function person($audience, $key)
    {
        $all = self::people($audience, []);
        return isset($all[$key]) ? $all[$key] : null;
    }

    public static function search($audience, $term, $limit = 20)
    {
        $term = mb_strtolower(trim((string) $term), 'UTF-8');
        if ($term === '') {
            return [];
        }
        $out = [];
        foreach (self::people($audience, []) as $key => $p) {
            if (mb_strpos(mb_strtolower($p['name'], 'UTF-8'), $term, 0, 'UTF-8') !== false) {
                $out[] = ['key' => $key, 'name' => $p['name'], 'email' => $p['email'], 'mobile' => $p['mobile']];
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return $out;
    }

    /** null = no building filter; [] = the filter matches no building. */
    public static function buildingIds(array $filters)
    {
        $buildings = self::ints(isset($filters['buildings']) ? $filters['buildings'] : []);
        if ($buildings) {
            return $buildings;
        }
        $locations = self::ints(isset($filters['locations']) ? $filters['locations'] : []);
        if ($locations) {
            return self::ints(DB::table('buildings')->whereIn('location_id', $locations)->pluck('id')->all());
        }
        return null;
    }

    private static function ints($list)
    {
        $out = [];
        foreach ((array) $list as $v) {
            if ((int) $v > 0) {
                $out[] = (int) $v;
            }
        }
        return array_values(array_unique($out));
    }

    /** building_id: the building of the contract used for this person (null for staff / no contract). */
    private static function row($type, $id, $name, $email, $mobile, array $values, $buildingId = null)
    {
        $email = trim((string) $email);
        $mobile = trim((string) $mobile);
        return [
            'person_type' => $type, 'person_id' => (int) $id, 'name' => trim((string) $name),
            'email' => $email !== '' ? $email : null, 'mobile' => $mobile !== '' ? $mobile : null, 'values' => $values,
            'building_id' => $buildingId !== null && $buildingId !== '' ? (int) $buildingId : null,
        ];
    }

    private static function dash($v)
    {
        $v = trim((string) $v);
        return $v === '' ? '-' : $v;
    }

    private static function date($v)
    {
        return $v ? date('d/m/Y', strtotime($v)) : '-';
    }

    /** Same keys and formats as MessageValues::contract(). */
    public static function tenantValues($r)
    {
        return [
            'tenant_name'    => self::dash($r->tenant_name),
            'contract_no'    => self::dash($r->tenant_contract_no),
            'building'       => self::dash($r->building_name),
            'unit'           => self::dash($r->unit_no),
            'location'       => self::dash($r->locations_name),
            'rent'           => ($r->tenant_contract_rent !== null && $r->tenant_contract_rent !== '') ? number_format((float) $r->tenant_contract_rent, 3) : '-',
            'contract_start' => self::date($r->tenant_contract_start_date),
            'contract_end'   => self::date($r->tenant_contract_valid_to_date),
            'mobile'         => self::dash($r->tenant_contact_no ? $r->tenant_contact_no : $r->tenant_residence_tel),
        ];
    }

    private static function tenants($active, array $filters)
    {
        $buildingIds = self::buildingIds($filters);
        if ($buildingIds === []) {
            return [];
        }
        $rows = DB::table('tenant_contracts as c')
            ->join('tenant as t', 't.id', '=', 'c.tenant_id')
            ->leftJoin('buildings as b', 'b.id', '=', 'c.building_id')
            ->leftJoin('locations as l', 'l.id', '=', 'b.location_id')
            ->leftJoin('units as u', 'u.id', '=', 'c.unit_id')
            ->select('t.id as tenant_id', 't.tenant_name', 't.tenant_status', 't.tenant_contact_email', 't.tenant_personal_email',
                     't.tenant_contact_no', 't.tenant_residence_tel', 'c.id as contract_id', 'c.building_id', 'c.tenant_contract_no',
                     'c.tenant_contract_status', 'c.tenant_contract_rent', 'c.tenant_contract_start_date',
                     'c.tenant_contract_effective_date', 'c.tenant_contract_valid_to_date', 'b.building_name', 'l.locations_name', 'u.unit_no')
            ->orderBy('c.tenant_contract_effective_date', 'desc')->orderBy('c.id', 'desc');
        // Narrow in SQL (memory): active tenants need only their active contracts; past tenants
        // are those without any active contract but need all their contracts (12-month rule).
        if ($active) {
            $rows->where('c.tenant_contract_status', 1);
            if ($buildingIds !== null) {
                $rows->whereIn('c.building_id', $buildingIds);
            }
        } else {
            $rows->whereNotIn('c.tenant_id', function ($q) {
                // NOT NULL: one NULL in a NOT IN list would make it match nobody
                $q->select('tenant_id')->from('tenant_contracts')->where('tenant_contract_status', 1)->whereNotNull('tenant_id');
            });
        }
        $rows = $rows->get();

        $byTenant = [];
        foreach ($rows as $r) {
            $byTenant[$r->tenant_id][] = $r;
        }
        $cutoff = date('Y-m-d', strtotime('-12 months'));
        $out = [];
        foreach ($byTenant as $tenantId => $contracts) {
            $activeOnes = [];
            $lastEnd = '';
            foreach ($contracts as $c) {
                if ((int) $c->tenant_contract_status === 1) {
                    $activeOnes[] = $c;
                }
                $end = substr((string) $c->tenant_contract_valid_to_date, 0, 10);
                if ($end > $lastEnd) {
                    $lastEnd = $end;
                }
            }
            if ($active) {
                if ((int) $contracts[0]->tenant_status !== 1 || !$activeOnes) {
                    continue;
                }
                $pool = $activeOnes;
            } else {
                if ($activeOnes || $lastEnd === '' || $lastEnd < $cutoff) {
                    continue;
                }
                $pool = $contracts;
            }
            if ($buildingIds !== null) {
                $pool = array_values(array_filter($pool, function ($c) use ($buildingIds) {
                    return in_array((int) $c->building_id, $buildingIds, true);
                }));
                if (!$pool) {
                    continue;
                }
            }
            $c = $pool[0];
            $email = $c->tenant_contact_email ? $c->tenant_contact_email : $c->tenant_personal_email;
            $mobile = $c->tenant_contact_no ? $c->tenant_contact_no : $c->tenant_residence_tel;
            $out['tenant:' . $tenantId] = self::row('tenant', $tenantId, $c->tenant_name, $email, $mobile, self::tenantValues($c), $c->building_id);
        }
        return $out;
    }

    private static function landlords(array $filters)
    {
        $buildingIds = self::buildingIds($filters);
        if ($buildingIds === []) {
            return [];
        }
        $contracts = DB::table('landlord_contract as lc')
            ->join('buildings as b', 'b.id', '=', 'lc.building_id')
            ->leftJoin('locations as l', 'l.id', '=', 'b.location_id')
            ->where('lc.landlord_contract_status', 1)
            ->orderBy('lc.landlord_contract_valid_to_date', 'desc')->orderBy('lc.id', 'desc')
            ->get(['lc.vendor_id', 'lc.building_id', 'b.building_name', 'l.locations_name']);
        $byVendor = [];
        foreach ($contracts as $c) {
            $byVendor[$c->vendor_id][] = $c;
        }
        $vendors = DB::table('vendors')->where('vendor_type_id', 2)->where('vendor_status', 1)->orderBy('vendor_name')
            ->get(['id', 'vendor_name', 'vendor_contact_email', 'vendor_contact_no']);
        $out = [];
        foreach ($vendors as $v) {
            $pool = isset($byVendor[$v->id]) ? $byVendor[$v->id] : [];
            if ($buildingIds !== null) {
                $pool = array_values(array_filter($pool, function ($c) use ($buildingIds) {
                    return in_array((int) $c->building_id, $buildingIds, true);
                }));
                if (!$pool) {
                    continue;
                }
            }
            $c = $pool ? $pool[0] : null;
            $out['landlord:' . $v->id] = self::row('landlord', $v->id, $v->vendor_name, $v->vendor_contact_email, $v->vendor_contact_no, [
                'landlord_name' => self::dash($v->vendor_name),
                'building'      => $c ? self::dash($c->building_name) : '-',
                'location'      => $c ? self::dash($c->locations_name) : '-',
            ], $c ? $c->building_id : null);
        }
        return $out;
    }

    private static function staff(array $filters)
    {
        $roles = array_values(array_filter(isset($filters['roles']) ? (array) $filters['roles'] : []));
        $q = User::activeStaff()->orderBy('id');
        if ($roles) {
            $ids = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereIn('roles.name', $roles)->where('model_has_roles.model_type', 'App\User')->pluck('model_has_roles.model_id');
            $q->whereIn('id', $ids);
        }
        $out = [];
        foreach (StaffMessages::userRecipients($q->get()) as $pair) {
            $user = $pair[0];
            $to = $pair[1];
            $out['staff:' . $user->id] = self::row('staff', $user->id, $to->name, $to->email, $to->mobile, MessageValues::employee($user));
        }
        return $out;
    }
}
