<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'intranet_enrollments';
    protected $keyType = 'string';
    public $incrementing = false;

    // 🔐 CORRECTION : Ajouter 'id' dans fillable (la migration utilise UUID)
    protected $fillable = [
        'id',
        'student_id',
        'course_id',
        'semester',
        'grade',
        'final_score',
        'status'
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
    ];

    // Relations
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}