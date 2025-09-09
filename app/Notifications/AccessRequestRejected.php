<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestRejected extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject('Access Request Declined - ' . ($this->accessRequest->file->parameter->title ?? 'Parameter Content'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We regret to inform you that your access request has been declined.')
            ->line('**File:** ' . ($this->accessRequest->file->parameter->title ?? 'Parameter Content'))
            ->line('**Declined by:** ' . ($this->accessRequest->approver->name ?? 'Administrator'))
            ->line('**Declined on:** ' . $this->accessRequest->rejected_at->format('M d, Y H:i'));

        if ($this->accessRequest->rejection_reason) {
            $message->line('**Reason:** ' . $this->accessRequest->rejection_reason);
        }

        return $message
            ->line('If you believe this decision was made in error, please contact the file owner or administrator directly.')
            ->line('Thank you for your understanding.');
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
            'rejected_at' => $this->accessRequest->rejected_at,
            'rejection_reason' => $this->accessRequest->rejection_reason,
            'message' => 'Your access request for "' . 
                        ($this->accessRequest->file->parameter->title ?? 'Parameter Content') . 
                        '" has been declined.',
        ];
    }
}