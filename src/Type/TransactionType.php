<?php

declare(strict_types=1);

namespace App\Type;

use Symfony\Component\Translation\TranslatableMessage;

enum TransactionType: string
{
    case rent = '1';
    case payment = '2';
    case initial = '3';
    case adjustment = '4';

    public function translationKey(): string
    {
        return 'TRANSACTION_TYPE_' . strtoupper($this->name);
    }

    public function translatedName(): TranslatableMessage
    {
        return new TranslatableMessage($this->translationKey());
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::rent => 'table-success',
            self::payment => '',
            self::initial => 'table-info',
            self::adjustment => 'table-warning',
        };
    }

    public function cssClassPdf(): string
    {
        return match ($this) {
            self::rent => 'rent',
            self::payment => '',
            self::initial => 'initial',
            self::adjustment => 'adjustment',
        };
    }
}
