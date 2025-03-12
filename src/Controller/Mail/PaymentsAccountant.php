<?php

namespace App\Controller\Mail;

use App\Controller\BaseController;
use App\Repository\TransactionRepository;
use App\Service\EmailHelper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/mail/payments-accountant', name: 'mail_payments_accountant', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class PaymentsAccountant extends BaseController
{
    public function __invoke(
        Request                                       $request,
        TransactionRepository                         $repository,
        EmailHelper                                   $emailHelper,
        MailerInterface                               $mailer,
        #[Autowire('%env(EMAIL_ACCOUNTANT)%')] string $emailAccountant,
    ): Response
    {
        $year = $request->request->getInt('year', (int)date('Y'));
        $month = $request->request->getInt('month', (int)date('m'));
        $ii = $request->get('ids');
        $ids = is_array($ii) ? array_filter($ii, 'is_numeric') : [];

        if ($ids) {
            $email = $emailHelper->createTemplatedEmail(
                to: Address::create($emailAccountant),
                subject: sprintf('Pagos del MiniCC KuKu - %d / %d', $month, $year)
            )
                ->htmlTemplate('email/cobros-contador.twig')
                ->context([
                    'year' => $year,
                    'month' => $month,
                    'payments' => $repository->findByIds($ids),
                    'fileName' => '@todo$fileName',//@todo ->attach($document, $fileName)
                ]);

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Payments have been mailed.');
            } catch (TransportExceptionInterface $exception) {
                $this->addFlash('danger', 'Payments have NOT been mailed! - ' . $exception->getMessage());
            }

            //@todo redirect elsewhere
            //return $this->redirectToRoute('mail_cobros_contador');
        }

        return $this->render('mail/cobros-contador.html.twig',
            [
                'month' => $month,
                'year' => $year,
                'payments' => $repository->findByDate($year, $month),
            ]
        );
    }
}
