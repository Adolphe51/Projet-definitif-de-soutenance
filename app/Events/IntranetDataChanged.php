<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class IntranetDataChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $entityType;
    public string $action;
    public array $data;
    public string $ipAddress;
    public string $userAgent;
    public ?int $actorId = null;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $entityType,
        string $action,
        array $data = [],
        string $ipAddress = '',
        string $userAgent = '',
        ?int $actorId = null
    )
    {
        $this->entityType = $entityType;
        $this->action = $action;
        $this->data = $data;
        $this->ipAddress = $ipAddress ?: request()->ip();
        $this->userAgent = $userAgent ?: request()->userAgent();
        $this->actorId = $actorId ?? Auth::id();
    }
}
