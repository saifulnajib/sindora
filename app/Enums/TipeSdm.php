<?php

namespace App\Enums;

enum TipeSdm: string
{
    case Pelatih = 'pelatih';
    case Wasit = 'wasit';
    case Tenaga = 'tenaga';

    public function label(): string
    {
        return match ($this) {
            self::Pelatih => 'Pelatih',
            self::Wasit => 'Wasit',
            self::Tenaga => 'Tenaga Pendukung',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pelatih => 'green',
            self::Wasit => 'yellow',
            self::Tenaga => 'blue',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pelatih => 'bg-green-100 text-green-800',
            self::Wasit => 'bg-yellow-100 text-yellow-800',
            self::Tenaga => 'bg-blue-100 text-blue-800',
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
