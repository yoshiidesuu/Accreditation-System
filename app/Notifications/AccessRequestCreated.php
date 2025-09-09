<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $accessRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(AccessRequest $accessRequest)
    {
        $this->accessRequest = $accessRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = route('user.access-requests.show', $this->accessRequest);
        
        return (new MailMessage)
            ->subject('New Access Request - ' . ($this->accessRequest->file->parameter->title ?? 'Parameter Content'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new access request has been submitted for a file you own or manage.')
            ->line('**Requester:** ' . $this->accessRequest->requester->name)
            ->line('**File:** ' . ($this->accessRequest->file->parameter->title ?? 'Parameter Content'))
            ->line('**Reason:** ' . $this->accessRequest->reason)
            ->action('Review Request', $url)
            ->line('Please review this request and take appropriate action.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'access_request_id' => $this->accessRequest->id,
            'requester_name' => $this->accessRequest->requester->name,
            'file_name' => $this->accessRequest->file->parameter->title ?? 'Parameter Content',
            'reason' => $this->accessRequest->reason,
            'message' => $this->accessRequest->requester->name . ' has requested access to "' . 
                        ($this->accessRequest->file->parameter->title ?? 'Parameter Content') . '"',
        ];
    }
}