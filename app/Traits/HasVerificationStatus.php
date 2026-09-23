<?php

namespace App\Traits;

use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasVerificationStatus
{
    public function initializeHasVerificationStatus(): void
    {
        // Ensure casts includes verification_status enum if not already defined
        if (! isset($this->casts['verification_status'])) {
            $this->casts['verification_status'] = VerificationStatus::class;
        }
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('verification_status', VerificationStatus::Draft);
    }

    public function scopeMenungguVerifikasi($query)
    {
        return $query->where('verification_status', VerificationStatus::MenungguVerifikasi);
    }

    public function scopeTerverifikasi($query)
    {
        return $query->where('verification_status', VerificationStatus::Terverifikasi);
    }

    public function scopePerluPerbaikan($query)
    {
        return $query->where('verification_status', VerificationStatus::PerluPerbaikan);
    }

    public function scopeVerificationStatus($query, VerificationStatus|string $status)
    {
        $value = $status instanceof VerificationStatus ? $status->value : $status;

        return $query->where('verification_status', $value);
    }

    public function isDraft(): bool
    {
        return $this->verification_status === VerificationStatus::Draft;
    }

    public function isMenungguVerifikasi(): bool
    {
        return $this->verification_status === VerificationStatus::MenungguVerifikasi;
    }

    public function isTerverifikasi(): bool
    {
        return $this->verification_status === VerificationStatus::Terverifikasi;
    }

    public function isPerluPerbaikan(): bool
    {
        return $this->verification_status === VerificationStatus::PerluPerbaikan;
    }

    public function isDitolak(): bool
    {
        return $this->verification_status === VerificationStatus::Ditolak;
    }

    public function markMenungguVerifikasi(): bool
    {
        return $this->update(['verification_status' => VerificationStatus::MenungguVerifikasi]);
    }

    public function markTerverifikasi(?int $verifierId = null, ?string $catatan = null): bool
    {
        return $this->update([
            'verification_status' => VerificationStatus::Terverifikasi,
            'verified_by' => $verifierId ?? auth()->id(),
            'verified_at' => now(),
            'catatan_verifikator' => $catatan ?? $this->catatan_verifikator,
        ]);
    }

    public function markPerluPerbaikan(string $catatan, ?int $verifierId = null): bool
    {
        return $this->update([
            'verification_status' => VerificationStatus::PerluPerbaikan,
            'catatan_verifikator' => $catatan,
            'verified_by' => $verifierId ?? auth()->id(),
            'verified_at' => now(),
        ]);
    }

    public function markDitolak(string $catatan, ?int $verifierId = null): bool
    {
        return $this->update([
            'verification_status' => VerificationStatus::Ditolak,
            'catatan_verifikator' => $catatan,
            'verified_by' => $verifierId ?? auth()->id(),
            'verified_at' => now(),
        ]);
    }
}
