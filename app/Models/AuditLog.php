<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'audit_logs';

    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'previous_hash',
        'current_hash',
        'ip_address',
        'metadata',
        'resultat',
        'importance',
        'ressource',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'resultat' => 'string',
        'importance' => 'string',
    ];

    // Un audit est créé par un utilisateur (peut être null pour les actions système)
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
