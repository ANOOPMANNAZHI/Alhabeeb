<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class TerminationDues extends Model
{
    use Sortable;

    protected $table = 'termination_dues';
    protected $guarded = [];
    protected $dates = ['termination_date', 'terminated_at', 'charges_fixed_at', 'next_promise_date', 'last_followup_at'];
    public $sortable = ['id', 'termination_date', 'total_owed', 'total_settled', 'balance', 'status', 'last_followup_at', 'next_promise_date'];

    /**
     * Columns the list page may filter on. Key = request field (relation__column
     * for related tables, as the other list pages do), value = how to apply it.
     */
    const FILTERABLE = [
        'tenantContract__tenant_contract_no' => ['relation' => 'tenantContract', 'column' => 'tenant_contract_no'],
        'tenant__tenant_name'                => ['relation' => 'tenantContract.tenant', 'column' => 'tenant_name'],
        'tenant__tenant_contact_no'          => ['relation' => 'tenantContract.tenant', 'column' => 'tenant_contact_no'],
        'building__building_name'            => ['relation' => 'tenantContract.building', 'column' => 'building_name'],
        'unit__unit_no'                      => ['relation' => 'tenantContract.unit', 'column' => 'unit_no'],
        'termination_date'                   => ['column' => 'termination_date'],
        'total_owed'                         => ['column' => 'total_owed'],
        'total_settled'                      => ['column' => 'total_settled'],
        'balance'                            => ['column' => 'balance'],
        'status'                             => ['column' => 'status'],
        'next_promise_date'                  => ['column' => 'next_promise_date'],
    ];

    /**
     * Quick column filters + the shared advance-search arrays
     * (fieldName[] / operation[] / fieldValue[] / logic[]) used by every list page.
     */
    public function scopeFilter($query, $request)
    {
        // Quick filters: text columns match anywhere, dates/status match exactly
        foreach (self::FILTERABLE as $field => $def) {
            $val = $request->input($field);
            if ($val === null || $val === '') {
                continue;
            }
            $exact = in_array($def['column'], ['termination_date', 'status', 'next_promise_date'], true) || is_numeric($val) && in_array($def['column'], ['total_owed', 'total_settled', 'balance'], true);
            $this->applyFilter($query, $def, $exact ? '=' : 'ilike', $exact ? $val : '%' . $val . '%', 'and');
        }

        // "outstanding" is a virtual status: anything with a balance left
        if ($request->input('status') === 'outstanding') {
            $query->where('balance', '>', 0);
        }

        // Advance search
        $names = (array) $request->input('fieldName', []);
        if (!empty($names)) {
            $ops    = (array) $request->input('operation', []);
            $values = (array) $request->input('fieldValue', []);
            $logics = (array) $request->input('logic', []);
            $query->where(function ($q) use ($names, $ops, $values, $logics) {
                $logic = 'and';
                foreach ($names as $i => $name) {
                    if ($name === '' || !isset(self::FILTERABLE[$name]) || !isset($values[$i]) || $values[$i] === '') {
                        continue;
                    }
                    $op  = isset($ops[$i]) ? $ops[$i] : '=';
                    $val = $values[$i];
                    if ($op === 'ilike%...%') {
                        $op = 'ilike';
                        $val = '%' . $val . '%';
                    }
                    if (!in_array($op, ['=', '!=', '>', '>=', '<', '<=', 'ilike'], true)) {
                        $op = '=';
                    }
                    $this->applyFilter($q, self::FILTERABLE[$name], $op, $val, $logic);
                    $logic = isset($logics[$i]) && strtolower($logics[$i]) === 'or' ? 'or' : 'and';
                }
            });
        }

        return $query;
    }

    private function applyFilter($query, array $def, $op, $val, $logic)
    {
        if (isset($def['relation'])) {
            $method = $logic === 'or' ? 'orWhereHas' : 'whereHas';
            $query->{$method}($def['relation'], function ($q) use ($def, $op, $val) {
                $q->where($def['column'], $op, $val);
            });
        } else {
            $method = $logic === 'or' ? 'orWhere' : 'where';
            if ($def['column'] === 'status' && $val === 'outstanding') {
                $query->{$method}('balance', '>', 0);
            } else {
                $query->{$method}($def['column'], $op, $val);
            }
        }
    }

    const STATUS_OPEN = 'open';
    const STATUS_PARTIAL = 'partial';
    const STATUS_SETTLED = 'settled';
    const STATUS_WRITTEN_OFF = 'written_off';

    public function lines()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesLine', 'termination_dues_id')->orderBy('line_order')->orderBy('id');
    }

    public function followups()
    {
        return $this->hasMany('Modules\BackOffice\Entities\TerminationDuesFollowup', 'termination_dues_id')->orderBy('followup_date', 'desc')->orderBy('id', 'desc');
    }

    public function tenantContract()
    {
        return $this->belongsTo('Modules\Sales\Entities\TenantContract', 'tenant_contract_id', 'id');
    }

    public function termination()
    {
        return $this->belongsTo('Modules\BackOffice\Entities\Termination', 'termination_id', 'id');
    }
}
