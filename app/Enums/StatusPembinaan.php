<?php

namespace App\Enums;

enum StatusPembinaan: string
{
    case Daerah = 'daerah';
    case Provinsi = 'provinsi';
    case Nasional = 'nasional';

    public function label(): string
    {
        return match ($this) {
            self::Daerah => 'Daerah',
            self::Provinsi => 'Provinsi',
            self::Nasional => 'Nasional',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Daerah => 'blue',
            self::Provinsi => 'indigo',
            self::Nasional => 'purple',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Daerah => 'bg-blue-100 text-blue-800',
            self::Provinsi => 'bg-indigo-100 text-indigo-800',
            self::Nasional => 'bg-purple-100 text-purple-800',
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
