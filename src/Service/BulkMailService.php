<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Store;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class BulkMailService
{
    public function __construct(private readonly MailerInterface $mailer) {}

    /**
     * Send one email per store, filtered to those present in $recipients.
     *
     * @param Store[]          $stores
     * @param array<int, mixed> $recipients  Keys are store IDs
     * @param callable(Store): Email $emailFactory
     */
    public function sendToFilteredStores(array $stores, array $recipients, callable $emailFactory): MailBatchResult
    {
        $result = new MailBatchResult();

        foreach ($stores as $store) {
            if (!array_key_exists((int) $store->getId(), $recipients) || !$store->getUser()) {
                continue;
            }

            try {
                $this->mailer->send($emailFactory($store));
                $result->addSuccess($store->getId());
            } catch (TransportExceptionInterface $exception) {
                $result->addFailure($exception->getMessage());
            }
        }

        return $result;
    }
}
