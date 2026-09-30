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
        return self::sendLink('employee_welcome', $user, 'set_password_link', $user->email ? self::passwordLink($user) : '');
    }

    public static function passwordReset(User $user)
    {
        return self::sendLink('employee_password_reset', $user, 'set_password_link', $user->email ? self::passwordLink($user) : '');
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

    private static function sendLink($key, User $user, $linkKey, $link)
    {
        $pair = StaffMessages::userRecipients([$user]);
        $values = MessageValues::employee($user) + ['username' => (string) $user->username, $linkKey => $link];
        $options = ['related' => ['user', $user->id], 'secret' => $link === '' ? [] : [$link]];
        return SystemMessenger::send($key, $pair[0][1], $values, $options);
    }
}
