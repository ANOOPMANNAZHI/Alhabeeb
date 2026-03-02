<?php

namespace Modules\Sales\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Sales\Entities\TenantContract;

class TenantContractNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(TenantContract $TenantContract)
    {
        $this->tenantContract = $TenantContract;
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
            'href' => (!isset($this->tenantContract->href)) ? route('rentReceiptGeneration.show',$this->tenantContract->id) : $this->tenantContract->href,     
            'icon' => 'fa-warning' ,   
            'icon_color' => 'yellow'
        ];
    }
}
