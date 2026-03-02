<?php

namespace Modules\BackOffice\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Modules\BackOffice\Entities\LandlordPayment;

class LandlordPaymentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(LandlordPayment $landlordPayment)
    {
        $this->landlordPayment = $landlordPayment;
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
            'id' => $this->landlordPayment->id,
            'text' => $this->landlordPayment->text, 
            'href' => (!isset($this->landlordPayment->href)) ? route('landlordPayment',$this->landlordPayment->id) : $this->landlordPayment->href,     
            'icon' => $this->landlordPayment->icon ,   
            'icon_color' => $this->landlordPayment->icon_color 
        ];
    }
}
