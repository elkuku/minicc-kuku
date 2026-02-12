<?php

declare(strict_types=1);

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
    public function __construct(private readonly TransactionRepository $repository, private readonly EmailHelper $emailHelper, private readonly MailerInterface $mailer) {}

    public function __invoke(
        Request $request,
        #[Autowire('%env(EMAIL_ACCOUNTANT)%')] string $emailAccountant,
    ): Response
    {
        $year = $request->request->getInt('year', (int)date('Y'));
        $month = $request->request->getInt('month', (int)date('m'));
        $ids = array_map(intval(...), array_filter($request->request->all('ids'), is_numeric(...)));

        if ($ids) {
            $email = $this->emailHelper->createTemplatedEmail(
                to: Address::create($emailAccountant),
                subject: sprintf('Pagos del MiniCC KuKu - %d / %d', $month, $year)
            )
                ->htmlTemplate('email/cobros-contador.twig')
                ->context([
                    'year' => $year,
                    'month' => $month,
                    'payments' => $this->repository->findByIds($ids),
                    'fileName' => '@todo$fileName',//@todo ->attach($document, $fileName)
                ]);

            try {
                $this->mailer->send($email);
                $this->addFlash('success', 'Payments have been mailed.');
            } catch (TransportExceptionInterface $exception) {
                $this->addFlash('danger', 'Payments have NOT been mailed! - '.$exception->getMessage());
            }

            //@todo redirect elsewhere
            //return $this->redirectToRoute('mail_cobros_contador');
        }

        return $this->render('mail/cobros-contador.html.twig',
            [
                'month' => $month,
                'year' => $year,
                'payments' => $this->repository->findByDate($year, $month),
            ]
        );
    }
}
