<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

readonly class EmailHelper
{
    private Address $addressFrom;

    public function __construct(
        #[Autowire('%env(EMAIL_FROM_ADDR)%')]
        string $addressFrom,
        #[Autowire('%env(EMAIL_FROM_NAME)%')]
        string $nameFrom,
    )
    {
        $this->addressFrom = new Address($addressFrom, $nameFrom);
    }

    public function createEmail(string $toAddress, string $subject): Email
    {
        return (new Email())
            ->from($this->addressFrom)
            ->to($toAddress)
            ->subject($subject);
    }

    public function createTemplatedEmail(Address $addressTo, string $subject): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from($this->addressFrom)
            ->to($addressTo)
            ->subject($subject);
    }
}
