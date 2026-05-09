<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Alert extends Model
{
    protected $fillable = [
        'attack_id',
        'title',
        'message',
        'severity',
        'type',
        'acknowledged',
        'sound_played',
    ];

    protected $casts = [
        'acknowledged' => 'boolean',
        'sound_played' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => self::clearUnreadCountCache());
        static::deleted(fn () => self::clearUnreadCountCache());
    }

    public function attack()
    {
        return $this->belongsTo(Attack::class);
    }

    public static function unreadCountCacheKey(): string
    {
        return 'views.unread_alerts';
    }

    public static function getCachedUnreadCount(): int
    {
        return Cache::remember(self::unreadCountCacheKey(), now()->addSeconds(5), function () {
            return self::where('acknowledged', false)->count();
        });
    }

    public static function clearUnreadCountCache(): void
    {
        Cache::forget(self::unreadCountCacheKey());
    }
}
