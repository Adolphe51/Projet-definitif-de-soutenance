<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attack extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'rule_id',
        'incident_id',
        'source_ip',
        'target_ip',
        'target_port',
        'protocol',
        'severity',
        'status',
        'country',
        'city',
        'latitude',
        'longitude',
        'isp',
        'packet_count',
        'bandwidth_mbps',
        'payload',
        'description',
        'is_simulation',
        'alarm_triggered'
    ];

    protected $casts = [
        'is_simulation' => 'boolean',
        'alarm_triggered' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = [
        'severity_icon',
        'severity_color',
        'type_icon',
    ];

    // Relations
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function alert()
    {
        return $this->hasOne(Alert::class);
    }

    public function rule()
    {
        return $this->belongsTo(DetectionRule::class, 'rule_id', 'rule_id');
    }

    public function comments()
    {
        return $this->hasMany(AttackComment::class)->orderByDesc('created_at');
    }

    /**
     * Récupérer les attaques corrélées (même incident_id)
     */
    public function correlatedAttacks()
    {
        if (!$this->incident_id) {
            return static::where('id', $this->id);
        }
        return static::where('incident_id', $this->incident_id)->orderBy('created_at');
    }

    // Accessors
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => '#ff0040',
            'high' => '#ff6b00',
            'medium' => '#ffcc00',
            'low' => '#00ff88',
            default => '#aaaaaa',
        };
    }

    public function getSeverityIconAttribute(): string
    {
        return match ($this->severity) {
            'critical' => '💀',
            'high' => '🔴',
            'medium' => '🟡',
            'low' => '🟢',
            default => '⚪',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'DDoS' => '🌊',
            'SQL Injection' => '💉',
            'XSS' => '📜',
            'Brute Force' => '🔨',
            'Port Scan' => '🔍',
            'Ransomware' => '🔒',
            'Phishing' => '🎣',
            'MITM' => '👤',
            default => '⚡',
        };
    }

    // Scopes
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical')->where('status', '!=', 'blocked');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'detected')->where('is_simulation', false);
    }

    // Static helpers
    public static function severityLevels(): array
    {
        return ['low', 'medium', 'high', 'critical'];
    }

    public static function attackTypes(): array
    {
        return config('cyberguard.detection.monitored_types', [
            'Brute Force',
            'SQL Injection',
            'XSS',
        ]);
    }
}
