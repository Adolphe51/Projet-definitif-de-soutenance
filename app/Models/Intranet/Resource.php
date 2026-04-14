<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resource extends Model
{
    use HasFactory;

    protected $table = 'intranet_resources';
    protected $keyType = 'string';
    public $incrementing = false;

    // 🔐 CORRECTION : Ajouter 'id' dans fillable (la migration utilise UUID)
    protected $fillable = [
        'id',
        'course_id',
        'title',
        'file_path',
        'file_type',
        'uploaded_by',
        'uploaded_at',
        'access_count'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'access_count' => 'integer',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }
}