<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\EmailHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class EmailHelperTest extends TestCase
{
    private EmailHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new EmailHelper('admin@example.com');
    }

    public function testGetFromReturnsCorrectAddress(): void
    {
        $from = $this->helper->getFrom();

        $this->assertInstanceOf(Address::class, $from);
        $this->assertSame('admin@example.com', $from->getAddress());
    }

    public function testCreateEmailReturnsEmailWithCorrectFields(): void
    {
        $to = new Address('user@example.com');
        $email = $this->helper->createEmail($to, 'Test Subject');

        $this->assertInstanceOf(Email::class, $email);
        $this->assertSame('admin@example.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('user@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Test Subject', $email->getSubject());
    }

    public function testCreateTemplatedEmailReturnsTemplatedEmailWithCorrectFields(): void
    {
        $to = new Address('user@example.com');
        $email = $this->helper->createTemplatedEmail($to, 'Templated Subject');

        $this->assertInstanceOf(TemplatedEmail::class, $email);
        $this->assertSame('admin@example.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('user@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Templated Subject', $email->getSubject());
    }

    public function testCreateAdminEmailSetsFromAndToAsAdminAddress(): void
    {
        $email = $this->helper->createAdminEmail('Admin Subject');

        $this->assertInstanceOf(Email::class, $email);
        $this->assertSame('admin@example.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('admin@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Admin Subject', $email->getSubject());
    }
}
