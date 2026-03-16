<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attack extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'source_ip', 'target_ip', 'target_port', 'protocol',
        'severity', 'status', 'country', 'city', 'latitude', 'longitude',
        'isp', 'packet_count', 'bandwidth_mbps', 'payload', 'description',
        'is_simulation', 'alarm_triggered'
    ];

    protected $casts = [
        'is_simulation' => 'boolean',
        'alarm_triggered' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'critical' => '#ff0040',
            'high'     => '#ff6b00',
            'medium'   => '#ffcc00',
            'low'      => '#00ff88',
            default    => '#aaaaaa',
        };
    }

    public function getSeverityIconAttribute()
    {
        return match($this->severity) {
            'critical' => '💀',
            'high'     => '🔴',
            'medium'   => '🟡',
            'low'      => '🟢',
            default    => '⚪',
        };
    }

    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'DDoS'        => '🌊',
            'SQL Injection' => '💉',
            'XSS'         => '📜',
            'Brute Force' => '🔨',
            'Port Scan'   => '🔍',
            'Ransomware'  => '🔒',
            'Phishing'    => '🎣',
            'MITM'        => '👤',
            default       => '⚡',
        };
    }

    public static function severityLevels(): array
    {
        return ['low', 'medium', 'high', 'critical'];
    }

    public static function attackTypes(): array
    {
        return [
            'DDoS', 'SQL Injection', 'XSS', 'Brute Force',
            'Port Scan', 'Ransomware', 'Phishing', 'MITM',
            'Buffer Overflow', 'DNS Spoofing', 'ARP Poisoning', 'Zero Day'
        ];
    }
}
