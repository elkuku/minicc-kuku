<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ContractFilterPayload
{
    public function __construct(
        #[SerializedName('store_id')]
        public int $storeId = 0,
        public int $year = 0,
    ) {
    }
}
