<?php

namespace Modules\Maintenance\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use Modules\Maintenance\Entities\ComplaintEnquiry;

class ComplaintEnquiryNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ComplaintEnquiry $complaintEnquiry)
    {
        $this->complaintEnquiry = $complaintEnquiry;
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
            'id' => $this->complaintEnquiry->id,
            'text' => $this->complaintEnquiry->text, 
            'href' => (!isset($this->complaintEnquiry->href)) ? route('complaint.show',$this->complaintEnquiry->id) : $this->complaintEnquiry->href,     
            'icon' => $this->complaintEnquiry->icon ,   
            'icon_color' => $this->complaintEnquiry->icon_color 
        ];
    }
}
