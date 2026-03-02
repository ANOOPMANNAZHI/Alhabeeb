<?php

namespace Modules\BackOffice\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\BackOffice\Entities\ReceiptsGeneration;

class ReceiptNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ReceiptsGeneration $ReceiptsGeneration)
    {
        $this->receiptsGeneration = $ReceiptsGeneration;
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
            'id' => $this->receiptsGeneration->id,
            'text' => $this->receiptsGeneration->text, 
            'href' => (!isset($this->receiptsGeneration->href)) ? route('rentReceiptGeneration.show',$this->receiptsGeneration->id) : $this->receiptsGeneration->href,     
            'icon' => 'fa-warning' ,   
            'icon_color' => 'yellow'
        ];
    }
}
