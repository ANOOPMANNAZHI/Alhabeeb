<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Entities\TenantOnboarding as TenantOnboardingModel;
use Modules\BackOffice\Services\TenantOnboarding;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Masters\Entities\Building;

/**
 * Office view of the move-in checklists the ARE completes on the phone:
 * list, detail with photos and signature, verify, reassign, PDF.
 */
class TenantOnboardingController extends Controller
{
    const PER_PAGE = 25;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:onboarding_view');
        $this->middleware('permission:onboarding_verify', ['only' => ['verify', 'reassign']]);
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 'open');
        $onboardings = TenantOnboardingModel::with('tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building', 'assignedUser.employee')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->building_id, fn ($q) => $q->where('building_id', $request->building_id))
            ->when($request->user_id, fn ($q) => $q->where('assigned_user_id', $request->user_id))
            ->when($request->q, fn ($q) => $q->whereHas('tenantContract', fn ($c) => $c
                ->where('tenant_contract_no', 'ilike', '%' . $request->q . '%')
                ->orWhereHas('tenant', fn ($t) => $t->where('tenant_name', 'ilike', '%' . $request->q . '%'))))
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->appends($request->except('page'));

        $buildings = Building::whereIn('id', TenantOnboardingModel::select('building_id'))->orderBy('building_name')->get(['id', 'building_name']);
        $ares = User::whereIn('id', TenantOnboardingModel::select('assigned_user_id'))->with('employee')->get();
        $statuses = ['open' => 'Open', 'completed' => 'Completed', 'verified' => 'Verified', 'cancelled' => 'Cancelled', 'all' => 'All'];

        return view('backoffice::Onboarding.index', compact('onboardings', 'buildings', 'ares', 'statuses', 'status'));
    }

    public function show(TenantOnboardingModel $onboarding)
    {
        $onboarding->load('tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building', 'assignedUser.employee', 'verifiedBy');
        $detail = TenantOnboarding::detail($onboarding);
        $ares = User::role(['are', 'are_team_lead'])->with('employee')->get();
        return view('backoffice::Onboarding.show', compact('onboarding', 'detail', 'ares'));
    }

    public function verify(TenantOnboardingModel $onboarding)
    {
        try {
            TenantOnboarding::verify($onboarding, \Auth::user());
            activity('Tenant Onboarding')->performedOn($onboarding)->causedBy(\Auth::user())->log('verified');
            session()->flash('success', 'Onboarding verified');
        } catch (InvalidStateException $e) {
            session()->flash('error', $e->getMessage());
        }
        return redirect()->route('tenant-onboarding.show', $onboarding);
    }

    public function reassign(Request $request, TenantOnboardingModel $onboarding)
    {
        $this->validate($request, ['user_id' => 'required|exists:users,id']);
        TenantOnboarding::reassign($onboarding, User::findOrFail($request->user_id));
        activity('Tenant Onboarding')->performedOn($onboarding)->causedBy(\Auth::user())->withProperties(['user_id' => $request->user_id])->log('reassigned');
        session()->flash('success', 'Onboarding reassigned');
        return redirect()->route('tenant-onboarding.show', $onboarding);
    }

    public function pdf(TenantOnboardingModel $onboarding)
    {
        $path = $onboarding->pdf_path && Storage::disk('local')->exists($onboarding->pdf_path)
            ? $onboarding->pdf_path
            : TenantOnboarding::pdfPath($onboarding);
        return Storage::disk('local')->response($path, 'onboarding-' . optional($onboarding->tenantContract)->tenant_contract_no . '.pdf');
    }
}
