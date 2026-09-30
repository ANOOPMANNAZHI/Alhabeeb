<?php

namespace Modules\BackOffice\Services;

use App\User;

/**
 * Legal alerts to staff (C3). Several run inside posting flows (PDC, receipts,
 * rental income, deposit refund), so they never throw for delivery problems
 * and do not need the legals row — contract values come from the contract.
 */
class LegalMessages
{
    /** @param iterable $users App\User list (duplicates are sent once) */
    public static function toUsers($key, $users, $contract, array $extra = [])
    {
        $values = MessageValues::contract($contract) + $extra;
        foreach (StaffMessages::userRecipients($users) as $pair) {
            SystemMessenger::send($key, $pair[1], $values + MessageValues::employee($pair[0]),
                ['related' => ['contract', $contract ? $contract->id : null]]);
        }
    }

    public static function toRoles($key, array $roles, $contract, array $extra = [])
    {
        self::toUsers($key, User::role($roles)->get(), $contract, $extra);
    }
}
