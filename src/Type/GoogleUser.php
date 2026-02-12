<?php

declare(strict_types=1);

namespace App\Type;

final class GoogleUser
{
    /**
     * @param array<string> $response
     */
    public function __construct(
        private array $response
    ) {}

    public function getId(): string
    {
        return $this->response['sub'];
    }

    public function getName(): string
    {
        return $this->response['name'];
    }

    public function getFirstName(): ?string
    {
        return $this->getResponseValue('given_name');
    }

    private function getResponseValue(string $key): string|null
    {
        return $this->response[$key] ?? null;
    }

    public function getLastName(): ?string
    {
        return $this->getResponseValue('family_name');
    }

    public function getLocale(): ?string
    {
        return $this->getResponseValue('locale');
    }

    public function getEmail(): ?string
    {
        return $this->getResponseValue('email');
    }

    public function getHostedDomain(): ?string
    {
        return $this->getResponseValue('hd');
    }

    public function getAvatar(): ?string
    {
        return $this->getResponseValue('picture');
    }

    /**
     * Get user data as an array.
     *
     * @return array<string>
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
