<?php

namespace Modules\Maintenance\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Modules\Maintenance\Entities\AmcTask;

class AmcNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(AmcTask $amcTask)
    {
        $this->amcTask = $amcTask;
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
            'id' => $this->amcTask->id,
            'text' => $this->amcTask->text, 
            'href' => (!isset($this->amcTask->href)) ? route('amcTask.show',$this->amcTask->id) : $this->amcTask->href,     
            'icon' => $this->amcTask->icon ,   
            'icon_color' => $this->amcTask->icon_color 
        ];
    }
}
