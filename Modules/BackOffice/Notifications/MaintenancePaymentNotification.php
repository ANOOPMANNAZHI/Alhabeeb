<?php

namespace Modules\BackOffice\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Modules\BackOffice\Entities\MaintenancePayment;

class MaintenancePaymentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(MaintenancePayment $maintenancePayment)
    {
        $this->maintenancePayment = $maintenancePayment;
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
            'id' => $this->maintenancePayment->id,
            'text' => $this->maintenancePayment->text, 
            'href' => (!isset($this->maintenancePayment->href)) ? route('maintenancePayment',$this->maintenancePayment->id) : $this->maintenancePayment->href,     
            'icon' => $this->maintenancePayment->icon ,   
            'icon_color' => $this->maintenancePayment->icon_color 
        ];
    }
}
