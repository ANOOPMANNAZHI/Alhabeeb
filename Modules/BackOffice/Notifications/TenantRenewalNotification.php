<?php

namespace Modules\BackOffice\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Modules\Sales\Entities\TenantContract;

class TenantRenewalNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(TenantContract $tenantContract)
    {
        $this->tenantContract = $tenantContract;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', 'https://laravel.com')
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->tenantContract->id,
            'text' => $this->tenantContract->text, 
            'href' => (!isset($this->tenantContract->href)) ? route('newContractShow',$this->tenantContract->id,301) : $this->tenantContract->href,     
            'icon' => $this->tenantContract->icon ,   
            'icon_color' => $this->tenantContract->icon_color 
        ];
    }
}
