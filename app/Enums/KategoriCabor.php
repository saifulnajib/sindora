<?php

namespace App\Enums;

enum KategoriCabor: string
{
    case Koni = 'koni';
    case Kormi = 'kormi';
    case Disabilitas = 'disabilitas';

    public function label(): string
    {
        return match ($this) {
            self::Koni => 'KONI (Olahraga Prestasi)',
            self::Kormi => 'KORMI (Olahraga Rekreasi / Masyarakat)',
            self::Disabilitas => 'DISABILITAS (Olahraga Disabilitas / NPCI)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Koni => 'KONI',
            self::Kormi => 'KORMI',
            self::Disabilitas => 'DISABILITAS',
        };
    }

    public function sublabel(): string
    {
        return match ($this) {
            self::Koni => 'Olahraga Prestasi',
            self::Kormi => 'Olahraga Rekreasi / Masyarakat',
            self::Disabilitas => 'Olahraga Disabilitas (NPCI)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Koni => 'red',
            self::Kormi => 'amber',
            self::Disabilitas => 'blue',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Koni => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200 border border-red-200 dark:border-red-800',
            self::Kormi => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200 border border-amber-200 dark:border-amber-800',
            self::Disabilitas => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200 border border-blue-200 dark:border-blue-800',
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
