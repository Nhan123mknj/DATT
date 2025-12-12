<?php

namespace App\Models;

use App\Models\Models\ApprovalQueue;
use App\Traits\HasMedia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'avatar',
    ];
    protected $appends = ['avatar_url'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'name' => $this->name,
            'is_active' => $this->is_active,
        ];
    }
    /**
     * Get avatar URL - uses media relationship if available, falls back to legacy avatar field
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('id');
    }
    public function avatar(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')->where('type', 'avatar');
    }
    public function gallery(): MorphMany
    {
        return $this->media()->where('type', 'gallery');
    }

    public function documents(): MorphMany
    {
        return $this->media()->where('type', 'document');
    }

    protected function avatarUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->avatar?->url,
        );
    }

    public function getAvatarUrl(?int $width = null, ?int $height = null): ?string
    {
        return $this->avatar?->getUrl($width, $height);
    }
    public function borrows()
    {
        return $this->hasMany(Borrows::class, 'borrower_id');
    }

    /**
     * Get the student profile for a student user.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the teacher profile for a teacher user.
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is a teacher
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is a borrower (student or teacher)
     */
    public function isBorrower(): bool
    {
        return in_array($this->role, ['student', 'teacher']);
    }

    /**
     * Get the user code (student_code or teacher_code)
     */
    public function getUserCode(): ?string
    {
        if ($this->isStudent()) {
            return $this->student?->student_code;
        }

        if ($this->isTeacher()) {
            return $this->teacher?->teacher_code;
        }

        return null;
    }

    /**
     * Override toArray to exclude null student/teacher relationships
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Remove student field if user is not a student or if it's null
        if ($this->role !== 'student' || is_null($this->student)) {
            unset($array['student']);
        }

        // Remove teacher field if user is not a teacher or if it's null
        if ($this->role !== 'teacher' || is_null($this->teacher)) {
            unset($array['teacher']);
        }

        return $array;
    }
}
