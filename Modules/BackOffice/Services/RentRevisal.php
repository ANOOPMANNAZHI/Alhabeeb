<?php

namespace Modules\BackOffice\Services;

use App\User;
use Carbon\Carbon;
use Modules\Sales\Entities\TenantContract;

/**
 * Rent revisal before renewal (spec 2026-10-15). A contract can be revised when it is active, ends in
 * 90-120 days (both inclusive) and its renewal has not started (tenant_renewal_termination_status 0/1/4).
 * ARE scope follows Due for Renewal's areFilter: an ARE sees the buildings assigned to them, an ARE team
 * lead (as only role) their team's buildings, everyone else all buildings.
 */
class RentRevisal
{
    const MIN_DAYS = 90;
    const MAX_DAYS = 120;
    const OPEN_STATUSES = [0, 1, 4];

    public static function windowQuery($user)
    {
        $q = TenantContract::query()
            ->where('tenant_contracts.tenant_contract_status', 1)
            ->whereIn('tenant_contracts.tenant_renewal_termination_status', self::OPEN_STATUSES)
            ->whereDate('tenant_contracts.tenant_contract_valid_to_date', '>=', Carbon::today()->addDays(self::MIN_DAYS)->toDateString())
            ->whereDate('tenant_contracts.tenant_contract_valid_to_date', '<=', Carbon::today()->addDays(self::MAX_DAYS)->toDateString());
        return self::scope($q, $user);
    }

    public static function inWindow(TenantContract $c)
    {
        if ((int) $c->tenant_contract_status !== 1 || !in_array((int) $c->tenant_renewal_termination_status, self::OPEN_STATUSES, true)) {
            return false;
        }
        $end = Carbon::parse($c->tenant_contract_valid_to_date)->startOfDay();
        $days = Carbon::today()->diffInDays($end, false);
        return $days >= self::MIN_DAYS && $days <= self::MAX_DAYS;
    }

    public static function canRevise(TenantContract $c, $user)
    {
        return self::inWindow($c) && !self::sentRevisal($c)
            && self::windowQuery($user)->where('tenant_contracts.id', $c->id)->exists();
    }

    /** The contract's revisal once it has been sent to the tenant (a draft does not count). */
    public static function sentRevisal(TenantContract $c)
    {
        return $c->rentRevisal()->where('status', \Modules\BackOffice\Entities\TenantRentRevisal::SENT)->first();
    }

    /** Is the contract's building one the user may see (ARE scope only, no window)? */
    public static function inScope(TenantContract $c, $user)
    {
        return self::scope(TenantContract::query(), $user)->where('tenant_contracts.id', $c->id)->exists();
    }

    public static function dueCount($user)
    {
        // drafts are still due: only a revisal sent to the tenant takes the contract off the count
        return self::windowQuery($user)->whereDoesntHave('rentRevisal', function ($r) {
            $r->where('status', \Modules\BackOffice\Entities\TenantRentRevisal::SENT);
        })->count();
    }

    /**
     * Save the revisal and email the tenant (Reply-To = the first ARE of the building, else the sender).
     * @return array [TenantRentRevisal|null, string message for the user]
     */
    public static function send(TenantContract $c, $user, $rent, $note, $draft = false)
    {
        if (!self::canRevise($c, $user)) {
            return [null, 'This contract cannot be revised: it is outside the 90-120 day window, already sent, or not one of your buildings.'];
        }
        $note = trim((string) $note) !== '' ? trim((string) $note) : null;
        $status = $draft ? \Modules\BackOffice\Entities\TenantRentRevisal::DRAFT : \Modules\BackOffice\Entities\TenantRentRevisal::SENT;
        $revisal = \DB::transaction(function () use ($c, $user, $rent, $note, $status) {
            // lock the contract row so two saves cannot both pass canRevise
            TenantContract::where('id', $c->id)->lockForUpdate()->first();
            $existing = $c->rentRevisal()->first();
            if ($existing && !$existing->isDraft()) {
                return null;
            }
            $fields = ['current_rent' => (float) $c->tenant_contract_rent, 'revised_rent' => $rent, 'note' => $note,
                       'status' => $status, 'created_by' => $user->id];
            if ($existing) {
                $existing->update($fields);
                return $existing;
            }
            return \Modules\BackOffice\Entities\TenantRentRevisal::create($fields + ['tenant_contract_id' => $c->id]);
        });
        if (!$revisal) {
            return [null, 'This revisal has already been sent to the tenant.'];
        }
        if ($draft) {
            return [$revisal, 'Draft saved. Nothing was sent to the tenant.'];
        }
        $are = self::areUsers($c)->first() ?: $user;
        $areEmp = $are->employee;
        $to = ComplaintMessages::tenantRecipient($c->tenant, 'contact');
        $r = SystemMessenger::send('rent_revision_tenant', $to, self::tenantValues($c, $revisal, $are), [
            'reply_to' => ['email' => $are->email, 'name' => $areEmp && $areEmp->employee_name ? $areEmp->employee_name : $are->username],
            'related' => ['contract', $c->id],
            'blocks' => ['revisal_note' => $revisal->note ? '<p>' . nl2br(e($revisal->note)) . '</p>' : ''],
        ]);
        $revisal->update(['email_status' => $r['email'], 'emailed_at' => $r['email'] === \Modules\BackOffice\Entities\MessageLog::SENT ? now() : null]);
        switch ($r['email']) {
            case \Modules\BackOffice\Entities\MessageLog::SENT:
                return [$revisal, 'Revisal saved and emailed to the tenant.'];
            case \Modules\BackOffice\Entities\MessageLog::SKIPPED_NO_ADDRESS:
                return [$revisal, 'Revisal saved. No email on file for this tenant - inform the tenant another way.'];
            case \Modules\BackOffice\Entities\MessageLog::SKIPPED_OFF:
                return [$revisal, 'Revisal saved. The tenant email is switched off in Mail > Templates.'];
            default:
                return [$revisal, 'Revisal saved, but the email could not be sent (see Mail > Sent log).'];
        }
    }

    /** Placeholder values for rent_revision_tenant (the note goes in as an HTML block). */
    public static function tenantValues(TenantContract $c, $revisal, $are)
    {
        $emp = $are ? $are->employee : null;
        return MessageValues::contract($c) + [
            'current_rent' => number_format((float) $revisal->current_rent, 3),
            'revised_rent' => number_format((float) $revisal->revised_rent, 3),
            'are_name'     => $emp && $emp->employee_name ? $emp->employee_name : ($are ? $are->username : ''),
            'are_email'    => $are ? (string) $are->email : '',
            'are_mobile'   => $emp ? (string) ($emp->employee_contact_no ?: $emp->employee_secondary_no) : '',
        ];
    }

    /** Rent the renewal screens start from: latest renewal form sent, else the revisal, else the contract rent. */
    public static function renewalRent(TenantContract $c)
    {
        $form = $c->tenantRenewalForm()->orderBy('id', 'desc')->first();
        if ($form) {
            return (float) $form->tenant_contract_rent;
        }
        $revisal = self::sentRevisal($c);   // a draft has not been told to the tenant: renewal stays on the current rent
        return $revisal ? (float) $revisal->revised_rent : (float) $c->tenant_contract_rent;
    }

    /** Daily: email the ARE(s) of every contract in the window with no revisal and no notice yet. */
    public static function notifyAres()
    {
        $sent = 0;
        $admin = User::find(1);   // all buildings: the job runs without a logged-in user
        $contracts = self::windowQuery($admin)->doesntHave('rentRevisal')
            ->whereNotIn('tenant_contracts.id', \DB::table('tenant_rent_revisal_notices')->select('tenant_contract_id'))
            ->with(['tenant', 'building', 'unit'])->get();
        foreach ($contracts as $c) {
            $ares = self::areUsers($c);
            if ($ares->isEmpty()) {
                continue;   // picked up later if an ARE is assigned while still in the window
            }
            $status = null;
            foreach (StaffMessages::userRecipients($ares) as $pair) {
                $r = SystemMessenger::send('rent_revisal_due_are', $pair[1], MessageValues::contract($c) + MessageValues::employee($pair[0])
                    + ['revisal_link' => route('rentRevisal.index')], ['related' => ['contract', $c->id]]);
                $status = $status === \Modules\BackOffice\Entities\MessageLog::SENT ? $status : $r['email'];
            }
            \Modules\BackOffice\Entities\TenantRentRevisalNotice::create(['tenant_contract_id' => $c->id, 'are_user_id' => $ares->first()->id,
                'email_status' => $status, 'sent_at' => now()]);
            $sent++;
        }
        return $sent;
    }

    /** Active users currently assigned as ARE of the contract's building. */
    public static function areUsers(TenantContract $c)
    {
        $building = $c->building;
        if (!$building) {
            return collect();
        }
        $ids = $building->areBuildings()->pluck('user_id')->filter()->unique()->values()->all();
        return User::whereIn('id', $ids)->where('user_type_status', 1)->get();
    }

    private static function scope($q, $user)
    {
        if (!$user) {
            return $q->whereRaw('1 = 0');
        }
        if ($user->hasRole('are')) {
            $uid = $user->id;
            $q->whereHas('building', function ($b) use ($uid) {
                $b->whereHas('areBuildings', function ($a) use ($uid) {
                    $a->where('user_id', $uid);
                });
            });
        } elseif (count($user->roles) == 1 && $user->hasRole('are_team_lead')) {
            $uid = $user->id;
            $q->whereHas('building', function ($b) use ($uid) {
                $b->whereHas('areBuildings', function ($a) use ($uid) {
                    $a->where(function ($w) use ($uid) {
                        $w->whereHas('user', function ($u) use ($uid) {
                            $u->whereHas('employee', function ($e) use ($uid) {
                                $e->where('head_user', $uid);
                            });
                        })->orWhere('user_id', $uid);
                    });
                });
            });
        }
        return $q;
    }
}
