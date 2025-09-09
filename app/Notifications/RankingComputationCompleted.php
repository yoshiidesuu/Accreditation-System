<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AreaRanking;

class RankingComputationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $period;
    protected ?int $collegeId;
    protected array $stats;
    protected float $executionTime;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $period,
        ?int $collegeId,
        array $stats,
        float $executionTime
    ) {
        $this->period = $period;
        $this->collegeId = $collegeId;
        $this->stats = $stats;
        $this->executionTime = $executionTime;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $periodName = AreaRanking::getPeriods()[$this->period] ?? 'Unknown';
        $scope = $this->collegeId ? 'college-specific' : 'system-wide';
        
        return (new MailMessage)
            ->subject('Area Rankings Computation Completed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("The {$periodName} area rankings computation has been completed successfully.")
            ->line("Scope: {$scope}")
            ->line("Execution time: {$this->executionTime} seconds")
            ->line('**Computation Summary:**')
            ->line("• Areas ranked: {$this->stats['total_areas_ranked']}")
            ->line("• Colleges affected: {$this->stats['colleges_affected']}")
            ->line("• Average completion: {$this->stats['average_completion']}%")
            ->line("• Average quality score: {$this->stats['average_quality']}")
            ->line("• Average accreditor rating: {$this->stats['average_accreditor_rating']}")
            ->when(
                $this->stats['top_ranked_area'],
                fn($mail) => $mail->line("• Top ranked area: {$this->stats['top_ranked_area']}")
            )
            ->action('View Rankings', url('/user/rankings'))
            ->line('You can now view the updated rankings in the system.');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $periodName = AreaRanking::getPeriods()[$this->period] ?? 'Unknown';
        
        return [
            'type' => 'ranking_computation_completed',
            'title' => 'Area Rankings Updated',
            'message' => "{$periodName} area rankings have been computed successfully.",
            'period' => $this->period,
            'period_name' => $periodName,
            'college_id' => $this->collegeId,
            'execution_time' => $this->executionTime,
            'stats' => $this->stats,
            'action_url' => url('/user/rankings'),
            'computed_at' => now()->toISOString()
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Determine which queues should be used for each notification channel.
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications',
            'database' => 'default'
        ];
    }
}
