<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\UserRole;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserFilterPayload
{
    public function __construct(
        #[SerializedName('user_active')]
        #[Assert\Choice(choices: ['', '0', '1'])]
        public string $userActive = '1',
        #[SerializedName('user_role')]
        public ?UserRole $userRole = null,
    ) {}
}
