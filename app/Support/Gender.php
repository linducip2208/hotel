<?php

declare(strict_types=1);

namespace App\Support;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public static function fromLocal(string $local): self
    {
        return match (mb_strtolower(trim($local))) {
            'laki-laki', 'laki laki', 'lakilaki', 'pria', 'male', 'm', 'cowo', 'cowok' => self::Male,
            'perempuan', 'wanita', 'female', 'f', 'cewe', 'cewek' => self::Female,
            default => self::Other,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Laki-laki',
            self::Female => 'Perempuan',
            self::Other => 'Lainnya',
        };
    }
}
