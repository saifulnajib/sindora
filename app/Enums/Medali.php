<?php

namespace App\Enums;

enum Medali: string
{
    case Emas = 'emas';
    case Perak = 'perak';
    case Perunggu = 'perunggu';
    case JuaraHarapan = 'juara_harapan';

    public function label(): string
    {
        return match ($this) {
            self::Emas => 'Emas',
            self::Perak => 'Perak',
            self::Perunggu => 'Perunggu',
            self::JuaraHarapan => 'Juara Harapan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Emas => 'yellow',
            self::Perak => 'gray',
            self::Perunggu => 'orange',
            self::JuaraHarapan => 'blue',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Emas => 'bg-yellow-100 text-yellow-800',
            self::Perak => 'bg-gray-100 text-gray-800',
            self::Perunggu => 'bg-orange-100 text-orange-800',
            self::JuaraHarapan => 'bg-blue-100 text-blue-800',
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
