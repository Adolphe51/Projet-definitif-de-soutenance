<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetectionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'name',
        'description',
        'attack_type',
        'default_severity',
        'detection_params',
        'enabled',
        'priority',
        'category',
        'cve_references',
    ];

    protected $casts = [
        'detection_params' => 'array',
        'enabled' => 'boolean',
    ];

    // Relations
    public function attacks()
    {
        return $this->hasMany(Attack::class, 'rule_id', 'rule_id');
    }

    /**
     * Récupérer les règles actives triées par priorité
     */
    public static function active()
    {
        return static::where('enabled', true)->orderBy('priority')->get();
    }

    /**
     * Récupérer une règle par son rule_id
     */
    public static function byRuleId(string $ruleId): ?self
    {
        return static::where('rule_id', $ruleId)->first();
    }

    /**
     * Obtenir les paramètres de détection avec des valeurs par défaut
     */
    public function getDetectionParams(array $defaults = []): array
    {
        return array_merge($defaults, $this->detection_params ?? []);
    }
}
