<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'intranet_attendances';
    protected $keyType = 'string';
    public $incrementing = false;

    // 🔐 CORRECTION : Ajouter 'id' dans fillable (la migration utilise UUID)
    protected $fillable = [
        'id',
        'enrollment_id',
        'lecture_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'lecture_date' => 'date',
    ];

    // Relations
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}