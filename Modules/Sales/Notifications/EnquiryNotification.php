<?php

namespace Modules\Sales\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;


use Modules\Sales\Entities\SalesEnquiry;

class EnquiryNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(SalesEnquiry $salesEnquiry)
    {
        $this->salesEnquiry = $salesEnquiry;  
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
        'id' => $this->salesEnquiry->id,
        'text' => $this->salesEnquiry->text, 
        'href' => (!isset($this->salesEnquiry->href)) ? route('enquiry.show',$this->salesEnquiry->id) : $this->salesEnquiry->href,     
        'icon' => $this->salesEnquiry->icon ,   
        'icon_color' => $this->salesEnquiry->icon_color    
        ];
    }
}
