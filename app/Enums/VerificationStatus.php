<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Draft = 'draft';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Terverifikasi = 'terverifikasi';
    case PerluPerbaikan = 'perlu_perbaikan';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Terverifikasi => 'Terverifikasi',
            self::PerluPerbaikan => 'Perlu Perbaikan',
            self::Ditolak => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::MenungguVerifikasi => 'yellow',
            self::Terverifikasi => 'green',
            self::PerluPerbaikan => 'orange',
            self::Ditolak => 'red',
        };
    }

    /**
     * Tailwind badge classes helper.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-800',
            self::MenungguVerifikasi => 'bg-yellow-100 text-yellow-800',
            self::Terverifikasi => 'bg-green-100 text-green-800',
            self::PerluPerbaikan => 'bg-orange-100 text-orange-800',
            self::Ditolak => 'bg-red-100 text-red-800',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
