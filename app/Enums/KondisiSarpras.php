<?php

namespace App\Enums;

enum KondisiSarpras: string
{
    case Baik = 'baik';
    case RusakRingan = 'rusak_ringan';
    case RusakBerat = 'rusak_berat';

    public function label(): string
    {
        return match ($this) {
            self::Baik => 'Baik',
            self::RusakRingan => 'Rusak Ringan',
            self::RusakBerat => 'Rusak Berat',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Baik => 'green',
            self::RusakRingan => 'yellow',
            self::RusakBerat => 'red',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Baik => 'bg-green-100 text-green-800',
            self::RusakRingan => 'bg-yellow-100 text-yellow-800',
            self::RusakBerat => 'bg-red-100 text-red-800',
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
