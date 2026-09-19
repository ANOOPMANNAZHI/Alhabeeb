<?php

namespace Modules\BackOffice\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\BackOffice\Entities\TenantOnboarding;
use Modules\Sales\Entities\TenantContract;

/** Sidebar bell for back-office when an ARE completes a move-in checklist. */
class OnboardingCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(private TenantOnboarding $onboarding, private ?TenantContract $contract)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'id' => $this->onboarding->id,
            'text' => 'Tenant onboarding completed - ' . optional($this->contract)->tenant_contract_no,
            'href' => \Illuminate\Support\Facades\Route::has('tenant-onboarding.show')
                ? route('tenant-onboarding.show', $this->onboarding->id)
                : url('tenantOnboarding/' . $this->onboarding->id),
            'icon' => 'fa-clipboard-check',
            'icon_color' => 'success',
        ];
    }
}
