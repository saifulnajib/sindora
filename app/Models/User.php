<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'klub_id',
        'organisasi_id',
        'cabor_id',
    ];

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

    public function klub(): BelongsTo
    {
        return $this->belongsTo(Klub::class);
    }

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class);
    }

    public function cabor(): BelongsTo
    {
        return $this->belongsTo(Cabor::class);
    }

    public function cabors(): BelongsToMany
    {
        return $this->belongsToMany(Cabor::class, 'cabor_user')->withTimestamps();
    }

    /**
     * Check if user is Pengkot (Pengurus Cabor).
     */
    public function isPengkot(): bool
    {
        return $this->hasRole(['pengurus_cabor', 'pengkot', 'operator_organisasi', 'operator_klub']);
    }

    /**
     * Get all Cabor IDs assigned to this Pengkot user.
     *
     * @return array<int>
     */
    public function getAssignedCaborIds(): array
    {
        $ids = $this->cabors()->pluck('cabors.id')->all();
        if ($this->cabor_id && ! in_array((int) $this->cabor_id, $ids, true)) {
            $ids[] = (int) $this->cabor_id;
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /**
     * Check if user has permission to manage a specific cabor.
     */
    public function managesCabor(int $caborId): bool
    {
        if ($this->hasRole(['super_admin', 'verifikator'])) {
            return true;
        }

        return in_array($caborId, $this->getAssignedCaborIds(), true);
    }
}
