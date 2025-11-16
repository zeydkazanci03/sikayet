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
 * Şikayet oluşturulduğunda tetiklenen event
 * Event triggered when a complaint is created
 */
class ComplaintCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Complaint $complaint;

    /**
     * Yeni event instance oluştur
     * Create a new event instance.
     */
    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
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
            new PrivateChannel('complaints'),
            new PrivateChannel('admin-notifications'),
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
            'title' => $this->complaint->title,
            'brand_name' => $this->complaint->brand->name,
            'user_name' => $this->complaint->user->name,
            'created_at' => $this->complaint->created_at->toIso8601String(),
        ];
    }

    /**
     * Broadcast event adını al
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'complaint.created';
    }
}
