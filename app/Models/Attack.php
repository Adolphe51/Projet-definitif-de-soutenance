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
        'source_scope',
        'source_channel',
        'source_label',
        'is_geolocatable',
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
        'is_geolocatable' => 'boolean',
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

    public function resolveSourceScope(): string
    {
        if (in_array($this->source_scope, ['internal', 'external'], true)) {
            return $this->source_scope;
        }

        if ($this->resolveSourceChannel() === 'intranet') {
            return 'internal';
        }

        return $this->isPrivateOrReservedSourceIp() ? 'internal' : 'external';
    }

    public function resolveSourceChannel(): string
    {
        if (in_array($this->source_channel, ['intranet', 'network', 'honeypot', 'simulation'], true)) {
            return $this->source_channel;
        }

        if ($this->is_simulation) {
            return 'simulation';
        }

        $description = strtolower((string) $this->description);

        if (str_contains($description, 'pattern sql suspect') || str_contains($description, 'pattern xss suspect')) {
            return 'intranet';
        }

        return $this->isPrivateOrReservedSourceIp() ? 'intranet' : 'network';
    }

    public function resolveSourceLabel(): string
    {
        if ($this->source_label) {
            return $this->source_label;
        }

        return match ($this->resolveSourceChannel()) {
            'intranet' => 'Application metier',
            'honeypot' => 'Honeypot',
            'simulation' => 'Simulation',
            default => 'Trafic reseau',
        };
    }

    public function isGeolocatable(): bool
    {
        if ($this->is_geolocatable !== null) {
            return $this->is_geolocatable
                && $this->latitude !== null
                && $this->longitude !== null;
        }

        return $this->resolveSourceScope() === 'external'
            && $this->latitude !== null
            && $this->longitude !== null;
    }

    private function isPrivateOrReservedSourceIp(): bool
    {
        return !filter_var(
            $this->source_ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
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
