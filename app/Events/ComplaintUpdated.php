<?php

namespace App\Events;

use App\Models\Complaint;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Şikayet güncellendiğinde tetiklenen event
 * Event triggered when a complaint is updated
 */
class ComplaintUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Complaint $complaint;
    public string $oldStatus;

    /**
     * Yeni event instance oluştur
     * Create a new event instance.
     */
    public function __construct(Complaint $complaint, string $oldStatus)
    {
        $this->complaint = $complaint;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Event'in broadcast edileceği kanalları al
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('complaint.' . $this->complaint->id),
            new PrivateChannel('user.' . $this->complaint->user_id),
        ];
    }

    /**
     * Broadcast edilecek veriyi al
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->complaint->id,
            'complaint_number' => $this->complaint->complaint_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->complaint->status,
            'is_resolved' => $this->complaint->is_resolved,
            'updated_at' => $this->complaint->updated_at->toIso8601String(),
        ];
    }

    /**
     * Broadcast event adını al
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'complaint.updated';
    }
}
