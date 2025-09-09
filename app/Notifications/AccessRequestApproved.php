<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestApproved extends Notification implements ShouldQueue
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
        $fileUrl = $this->accessRequest->share_link 
            ? route('user.share.access', $this->accessRequest->share_link) 
            : route('user.parameter-contents.show', $this->accessRequest->file);
        
        $message = (new MailMessage)
            ->subject('Access Request Approved - ' . ($this->accessRequest->file->parameter->title ?? 'Parameter Content'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your access request has been approved.')
            ->line('**File:** ' . ($this->accessRequest->file->parameter->title ?? 'Parameter Content'))
            ->line('**Approved by:** ' . ($this->accessRequest->approver->name ?? 'Administrator'))
            ->line('**Approved on:** ' . $this->accessRequest->approved_at->format('M d, Y H:i'))
            ->action('Access File', $fileUrl);

        if ($this->accessRequest->share_link && $this->accessRequest->share_link_expires_at) {
            $message->line('**Note:** This access link will expire on ' . 
                          $this->accessRequest->share_link_expires_at->format('M d, Y H:i'));
        }

        return $message->line('Thank you for using our system!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'access_request_id' => $this->accessRequest->id,
            'file_name' => $this->accessRequest->file->parameter->title ?? 'Parameter Content',
            'approver_name' => $this->accessRequest->approver->name ?? 'Administrator',
            'approved_at' => $this->accessRequest->approved_at,
            'share_link' => $this->accessRequest->share_link,
            'expires_at' => $this->accessRequest->share_link_expires_at,
            'message' => 'Your access request for "' . 
                        ($this->accessRequest->file->parameter->title ?? 'Parameter Content') . 
                        '" has been approved.',
        ];
    }
}