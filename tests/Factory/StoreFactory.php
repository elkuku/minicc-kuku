<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Store;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Store>
 */
final class StoreFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Store::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'destination' => self::faker()->word(),
            'valAlq' => 0.0,
            'cntLanfort' => 0,
            'cntNeon' => 0,
            'cntSwitch' => 0,
            'cntToma' => 0,
            'cntVentana' => 0,
            'cntLlaves' => 0,
            'cntMedAgua' => 0,
            'medAgua' => '',
            'cntMedElec' => 0,
            'medElectrico' => '',
        ];
    }
}
