<?php

namespace Modules\MobileApi\Services;

use App\User;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Exceptions\DeviceException;

/**
 * One device per user. The first login binds the device; any other device
 * is refused until an administrator clears the row (office screen).
 */
class DevicePolicy
{
    /** @param array{device_name?:string,platform?:string,app_version?:string} $meta */
    public static function authorise(User $user, string $deviceUid, array $meta = []): MobileDevice
    {
        $device = MobileDevice::where('user_id', $user->id)->first();

        if ($device === null) {
            return MobileDevice::create([
                'user_id' => $user->id, 'device_uid' => $deviceUid,
                'device_name' => $meta['device_name'] ?? null, 'platform' => $meta['platform'] ?? null,
                'app_version' => $meta['app_version'] ?? null,
                'registered_at' => now(), 'last_seen_at' => now(),
            ]);
        }

        if (!hash_equals($device->device_uid, $deviceUid)) {
            throw new DeviceException(DeviceException::NOT_REGISTERED);
        }
        if ($device->isBlocked()) {
            throw new DeviceException(DeviceException::BLOCKED);
        }

        $device->forceFill(array_filter([
            'last_seen_at' => now(),
            'app_version' => $meta['app_version'] ?? null,
            'device_name' => $meta['device_name'] ?? null,
        ]))->save();

        return $device;
    }

    /** Office action: forget the device and every token it held. */
    public static function clear(User $user): void
    {
        MobileDevice::where('user_id', $user->id)->delete();
        $user->tokens()->delete();
    }

    public static function block(User $user, User $by): void
    {
        MobileDevice::where('user_id', $user->id)->update(['blocked_at' => now(), 'blocked_by' => $by->id]);
        $user->tokens()->delete();
    }

    public static function unblock(User $user): void
    {
        MobileDevice::where('user_id', $user->id)->update(['blocked_at' => null, 'blocked_by' => null]);
    }
}
