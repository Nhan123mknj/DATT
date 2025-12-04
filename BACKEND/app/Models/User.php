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
}
