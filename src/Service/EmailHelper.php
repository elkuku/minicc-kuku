<?php

namespace App\Service;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailHelper
{
    private readonly Address $emailFrom;

    public function __construct(string $emailFromAddress, string $emailFromName)
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
