<?php

namespace Modules\MobileApi\Http\Controllers;

use App\User;
use Illuminate\Routing\Controller;
use Modules\MobileApi\Entities\MobileDevice;
use Modules\MobileApi\Services\DevicePolicy;

class MobileDeviceAdminController extends Controller
{
    public function index()
    {
        $devices = MobileDevice::with('user.employee')->orderByDesc('last_seen_at')->paginate(25);
        return view('mobileapi::devices.index', compact('devices'));
    }

    public function clear(User $user)
    {
        DevicePolicy::clear($user);
        activity('mobile')->causedBy(auth()->user())->performedOn($user)->log('device cleared');
        return redirect()->route('mobile-devices.index')->with('success', 'Device cleared. The next login will register a new device.');
    }

    public function block(User $user)
    {
        DevicePolicy::block($user, auth()->user());
        activity('mobile')->causedBy(auth()->user())->performedOn($user)->log('device blocked');
        return redirect()->route('mobile-devices.index')->with('success', 'Device blocked.');
    }

    public function unblock(User $user)
    {
        DevicePolicy::unblock($user);
        activity('mobile')->causedBy(auth()->user())->performedOn($user)->log('device unblocked');
        return redirect()->route('mobile-devices.index')->with('success', 'Device unblocked.');
    }
}
