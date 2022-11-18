<?php

namespace App\Type;

enum Gender: string
{
    case male = '1';
    case female = '2';
    case other = '3';

    /**
     * @return array<string>
     */
    public static function getChoices(): array
    {
        $cases = [];

        foreach (self::cases() as $case) {
            $cases[$case->name] = $case->value;
        }

        return $cases;
    }

    // @TODO This should be translatable!
    public function title(): string
    {
        return match($this)
        {
            self::male => 'Sr.',
            self::female => 'Sra.',
            self::other => 'Sr@.',
        };
    }

    public function titleLong(): string
    {
        return match($this)
        {
            self::male => 'señor',
            self::female => 'señora',
            self::other => 'señor@',
        };
    }

    public function salutation(): string
    {
        return match($this)
        {
            self::male => 'Estimado',
            self::female => 'Estimada',
            self::other => 'Estimad@',
        };
    }

    public function text_1(): string
    {
        return match($this)
        {
            self::male => 'el',
            self::female => 'la',
            self::other => 'l@',
        };
    }

    public function text_2(): string
    {
        return match($this)
        {
            self::male => 'del',
            self::female => 'de la',
            self::other => 'de l@',
        };
    }
}
