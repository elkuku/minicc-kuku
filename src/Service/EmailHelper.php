<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

readonly class EmailHelper
{
    private Address $from;

    public function __construct(
        #[Autowire('%env(EMAIL_FROM)%')] string $from,
    )
    {
        $this->from = Address::create($from);
    }

    public function createEmail(Address $to, string $subject): Email
    {
        return (new Email())
            ->from($this->from)
            ->to($to)
            ->subject($subject);
    }

    public function createTemplatedEmail(Address $to, string $subject): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from($this->from)
            ->to($to)
            ->subject($subject);
    }

    public function createAdminEmail(string $subject): Email
    {
        return (new Email())
            ->from($this->from)
            ->to($this->from)
            ->subject($subject);
    }

    public function getFrom(): Address
    {
        return $this->from;
    }
}
