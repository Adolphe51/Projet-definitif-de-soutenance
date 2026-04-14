<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $table = 'intranet_students';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'student_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // Relations
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}