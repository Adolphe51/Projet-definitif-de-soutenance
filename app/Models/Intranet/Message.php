<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $table = 'intranet_messages';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Relations
    public function sender()
    {
        return $this->belongsTo(Student::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Student::class, 'recipient_id');
    }
}