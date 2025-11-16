<?php

namespace App\Events;

use App\Models\Brand;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Marka onaylandığında tetiklenen event
 * Event triggered when a brand is approved
 */
class BrandApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Brand $brand;

    /**
     * Yeni event instance oluştur
     * Create a new event instance.
     */
    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
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
            new PrivateChannel('brands'),
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
            'id' => $this->brand->id,
            'name' => $this->brand->name,
            'slug' => $this->brand->slug,
            'status' => $this->brand->status,
            'is_active' => $this->brand->is_active,
            'updated_at' => $this->brand->updated_at->toIso8601String(),
        ];
    }

    /**
     * Broadcast event adını al
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'brand.approved';
    }
}
