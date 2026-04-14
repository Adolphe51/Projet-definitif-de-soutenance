<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IntranetDataChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $entityType;
    public string $action;
    public array $data;
    public string $ipAddress;
    public string $userAgent;

    /**
     * Create a new event instance.
     */
    public function __construct(string $entityType, string $action, array $data = [], string $ipAddress = '', string $userAgent = '')
    {
        $this->entityType = $entityType;
        $this->action = $action;
        $this->data = $data;
        $this->ipAddress = $ipAddress ?: request()->ip();
        $this->userAgent = $userAgent ?: request()->userAgent();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('cyberguard.intranet'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'intranet.data.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'entity_type' => $this->entityType,
            'action' => $this->action,
            'data' => $this->data,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'timestamp' => now()->toISOString(),
        ];
    }
}
