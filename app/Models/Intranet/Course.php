<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $table = 'intranet_courses';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'course_code',
        'title',
        'description',
        'department',
        'credits',
        'semester',
        'max_students',
        'status'
    ];

    protected $casts = [
        // Pas de is_active dans la migration, on utilise status
    ];

    // Relations
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    // Scopes
    // 🔐 CORRECTION : Utiliser 'status' au lieu de 'is_active' (cohérent avec la migration)
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}