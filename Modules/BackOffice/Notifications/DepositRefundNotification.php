<?php

namespace Modules\BackOffice\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Modules\BackOffice\Entities\DepositRefund;

class DepositRefundNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(DepositRefund $depositRefund)
    {
        $this->depositRefund = $depositRefund;
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
            'id' => $this->depositRefund->id,
            'text' => $this->depositRefund->text, 
            'href' => (!isset($this->depositRefund->href)) ? route('depositRefund',$this->depositRefund->id) : $this->depositRefund->href,     
            'icon' => $this->depositRefund->icon ,   
            'icon_color' => $this->depositRefund->icon_color 
        ];
    }
}
