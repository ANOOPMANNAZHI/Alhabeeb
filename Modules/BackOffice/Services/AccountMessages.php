<?php

namespace Modules\BackOffice\Services;

use App\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Account messages (C4): new employee welcome, admin password reset and the
 * login page "Forgot password". Passwords are never emailed: the user gets a
 * set-password link (Laravel password broker, valid config auth.passwords.users.expire
 * minutes), and the link is hidden in the sent log.
 */
class AccountMessages
{
    /** Result status: another login user has the same email, so no link was made or sent. */
    const DUPLICATE_EMAIL = 'duplicate_email';

    public static function randomPassword()
    {
        return Str::random(40);
    }

    /** New reset token for the user (replaces any older one) as a link to the reset page. */
    public static function passwordLink(User $user)
    {
        $token = Password::broker()->createToken($user);
        return self::linkFor($user, $token);
    }

    public static function welcome(User $user)
    {
        return self::sendNewLink('employee_welcome', $user);
    }

    public static function passwordReset(User $user)
    {
        return self::sendNewLink('employee_password_reset', $user);
    }

    /** A new token only when there is an address and the template is on (a token replaces any older one). */
    private static function sendNewLink($key, User $user)
    {
        if (self::emailShared($user)) {
            return self::duplicateResult();
        }
        $link = ($user->email && SystemTemplateDefaults::isActive($key)) ? self::passwordLink($user) : '';
        return self::sendLink($key, $user, 'set_password_link', $link);
    }

    /** Login page "Forgot password": the broker already made the token. */
    public static function forgot(User $user, $token)
    {
        return self::sendLink('password_forgot', $user, 'reset_link', self::linkFor($user, $token));
    }

    private static function linkFor(User $user, $token)
    {
        return route('password.reset', ['token' => $token, 'email' => $user->email]);
    }

    /**
     * The password broker finds the user by email (first match), so a link for an
     * email that another login user also has could reset the wrong account.
     */
    private static function emailShared(User $user)
    {
        $email = mb_strtolower(trim((string) $user->email), 'UTF-8');
        if ($email === '') {
            return false;
        }
        return \DB::table('users')->where('id', '<>', $user->id)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])->exists();
    }

    private static function duplicateResult()
    {
        return ['email' => self::DUPLICATE_EMAIL, 'sms' => null];
    }

    private static function sendLink($key, User $user, $linkKey, $link)
    {
        if (self::emailShared($user)) {
            return self::duplicateResult();
        }
        $pair = StaffMessages::userRecipients([$user]);
        $values = MessageValues::employee($user) + ['username' => (string) $user->username, $linkKey => $link];
        $options = ['related' => ['user', $user->id], 'secret' => $link === '' ? [] : [$link]];
        return SystemMessenger::send($key, $pair[0][1], $values, $options);
    }
}
