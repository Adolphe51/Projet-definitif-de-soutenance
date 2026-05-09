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

    // Relations
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
