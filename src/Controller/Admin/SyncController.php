<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use function count;

class SyncController extends AbstractController
{
    /**
     * @Route("/export-table/{name}", name="export-table")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function export(string $name): Response
    {
        $content = json_encode($this->getTableData($name));
        $filename = sprintf('export-%s-%s.json', $name, date('Y-m-d'));

        return new Response(
            $content,
            200,
            [
                'Content-Type'        => 'application/txt',
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s"',
                    $filename
                ),
            ]
        );
    }

    /**
     * @Route("/import-table", name="import-table")
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws DBALException
     */
    public function import(Request $request): Response
    {
        $file = $request->files->get('file');

        if (!$file) {
            $this->addFlash('danger', 'No file received.');

            return $this->redirectToRoute('admin-tasks');
        }

        $path = $file->getRealPath();

        if (!$path) {
            $this->addFlash('danger', 'Invalid file.');

            return $this->redirectToRoute('admin-tasks');
        }

        $parts = explode('-', $file->getClientOriginalName());

        if (count($parts) < 2) {
            $this->addFlash(
                'danger',
                'Invalid filename should be "export-{TABLE_NAME}-{DATE}.json".'
            );

            return $this->redirectToRoute('admin-tasks');
        }

        $tableName = $parts[1];

        $newData = json_decode(file_get_contents($path));

        $oldData = $this->getTableData($tableName);

        foreach ($newData as $i => $newItem) {
            foreach ($oldData as $io => $oldItem) {
                if ($oldItem['id'] === $newItem->id) {
                    foreach ($newItem as $prop => $value) {
                        if ($oldItem[$prop] !== $value) {
                            throw new UnexpectedValueException(
                                'Data inconsistency.'
                            );
                        }
                    }

                    unset($newData[$i]);
                    continue 2;
                }
            }
        }

        if (!count($newData)) {
            $this->addFlash('success', 'Everything is in Sync :)');

            return $this->redirectToRoute('admin-tasks');
        }

        $queryLines = [];
        $queryLines[] = "INSERT INTO $tableName\n";

        $keys = [];

        foreach (reset($newData) as $prop => $value) {
            $keys[] = $prop;
        }

        $queryLines[] = '('.implode(', ', $keys).")\n";

        $queryLines[] = "VALUES\n";

        $values = [];

        foreach ($newData as $item) {
            $valueLine = '';

            foreach ($item as $prop => $value) {
                if (null === $value) {
                    $valueLine .= 'null, ';
                } elseif (strpos($value, '-') || strpos($value, '.')) {
                    $valueLine .= "'$value', ";
                } else {
                    $valueLine .= $value.', ';
                }
            }

            $values[] = sprintf('(%s)', trim($valueLine, ', '));
        }

        $queryLines[] = implode(",\n", $values).';';

        $query = implode('', $queryLines);

        /** @type EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @type Statement $statement */
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();

        $this->addFlash('success', count($newData).' lines inserted');

        return $this->redirectToRoute('admin-tasks');
    }

    /**
     * @Route("/backup", name="backup")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function backup(MailerInterface $mailer): Response
    {
        try {
            $parts = parse_url($_ENV['DATABASE_URL']);

            $hostname = $parts['host'];
            $username = $parts['user'];
            $password = $parts['pass'];
            $database = ltrim($parts['path'], '/');

            // $cmd = sprintf('mysqldump -h %s -u %s -p%s %s|gzip 2>&1', $hostname, $username, $password, $database);
            $cmd = sprintf(
                'docker exec minicc-kuku_database_1 /usr/bin/mysqldump -h %s -u %s -p%s %s|gzip 2>&1',
                $hostname,
                $username,
                $password,
                $database
            );

            ob_start();
            passthru($cmd, $retVal);
            $gzip = ob_get_clean();

            if ($retVal) {
                throw new RuntimeException('Error creating DB backup: '.$gzip);
            }

            $fileName = date('Y-m-d').'_backup.gz';
            $mime = 'application/x-gzip';

            $attachment = new DataPart($gzip, $fileName, $mime);

            $email = (new Email())
                ->from('minicckuku@gmail.com')
                ->to('minicckuku@gmail.com')
                ->subject('Backup')
                ->text('Backup - Date: '.date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: '.date('Y-m-d'))
                ->attachPart($attachment);

            $mailer->send($email);
            $this->addFlash('success', 'Backup has been sent to your inbox.');
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin-tasks');
    }

    /**
     * @return array|RedirectResponse
     */
    private function getTableData($tableName)
    {
        try {
            /** @type EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            $query = "SELECT * FROM $tableName;";

            /** @type Statement $statement */
            $statement = $em->getConnection()->prepare($query);
            $statement->execute();

            $result = $statement->fetchAll();
        } catch (Exception $exception) {
            $this->addFlash('danger', 'There was an error...');

            return $this->redirectToRoute('admin-tasks');
        }

        return $result;
    }
}
