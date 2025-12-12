<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_code',
        'grade_level',
        'class_name',
        'enrollment_date',
        'parent_name',
        'parent_phone',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
    ];

    /**
     * Get the user that owns the student profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by grade level
     */
    public function scopeByGrade($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope to filter by class
     */
    public function scopeByClass($query, $className)
    {
        return $query->where('class_name', $className);
    }

    /**
     * Get formatted display name with student code
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->user->name} ({$this->student_code})";
    }
}
