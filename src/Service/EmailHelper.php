<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailHelper
{
    private readonly Address $emailFrom;

    public function __construct(
        #[Autowire('%env(EMAIL_FROM_ADDR)%')]
        string $emailFromAddress,
        #[Autowire('%env(EMAIL_FROM_NAME)%')]
        string $emailFromName,
    )
    {
        $this->emailFrom = new Address($emailFromAddress, $emailFromName);
    }

    public function create(string $toAddress, string $subject): Email
    {
        return (new Email())
            ->from($this->emailFrom)
            ->to($toAddress)
            ->subject($subject);
    }
}
