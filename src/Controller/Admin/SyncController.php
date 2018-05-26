<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SyncController
 */
class SyncController extends Controller
{
	/**
	 * @Route("/old-export-table/{name}", name="old-export-table")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param string $name
	 *
	 * @return Response
	 */
	public function OLDexportAction(string $name): Response
	{
		try
		{
			$items = $this->getDoctrine()
				->getRepository('App:' . $name)
				->findAll();
		}
		catch (\Exception $exception)
		{
			$this->addFlash('danger', 'There was an error...');

			return $this->redirectToRoute('admin-tasks');
		}

		$content  = json_encode($items);
		$filename = sprintf('export-%s-%s.json', $name, date('Y-m-d'));

		return new Response(
			$content,
			200,
			[
				'Content-Type'        => 'application/txt',
				'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
			]
		);
	}

	/**
	 * @Route("/export-table/{name}", name="export-table")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param string $name
	 *
	 * @return Response
	 */
	public function exportAction(string $name): Response
	{
		$content  = json_encode($this->getTableData($name));
		$filename = sprintf('export-%s-%s.json', $name, date('Y-m-d'));

		return new Response(
			$content,
			200,
			[
				'Content-Type'        => 'application/txt',
				'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
			]
		);
	}

	/**
	 * @Route("/import-table", name="import-table")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function importAction(Request $request): Response
	{
		$file = $request->files->get('file');

		if (!$file)
		{
			$this->addFlash('danger', 'No file received.');

			return $this->redirectToRoute('admin-tasks');
		}

		$path = $file->getRealPath();

		if (!$path)
		{
			$this->addFlash('danger', 'Invalid file.');

			return $this->redirectToRoute('admin-tasks');
		}

		$parts = explode('-', $file->getClientOriginalName());

		if (count($parts) < 2)
		{
			$this->addFlash('danger', 'Invalid filename should be "export-{TABLE_NAME}-{DATE}.json".');

			return $this->redirectToRoute('admin-tasks');
		}

		$tableName = $parts[1];

		$newData = json_decode(file_get_contents($path));

		$oldData = $this->getTableData($tableName);

		foreach ($newData as $i => $newItem)
		{
			foreach ($oldData as $io => $oldItem)
			{
				if ($oldItem['id'] == $newItem->id)
				{
					foreach ($newItem as $prop => $value)
					{
						if ($oldItem[$prop] != $value)
						{
							throw new \UnexpectedValueException('Data inconsistency.');
						}
					}

					unset($newData[$i]);
					continue 2;
				}
			}
		}

		if (!count($newData))
		{
			$this->addFlash('success', 'Everything is in Sync :)');

			return $this->redirectToRoute('admin-tasks');
		}

		$queryLines   = [];
		$queryLines[] = "INSERT INTO $tableName\n";

		$keys = [];

		foreach (reset($newData) as $prop => $value)
		{
			$keys[] = $prop;
		}

		$queryLines[] = '(' . implode(', ', $keys) . ")\n";

		$queryLines[] = "VALUES\n";

		$values = [];

		foreach ($newData as $item)
		{
			$valueLine = '';

			foreach ($item as $prop => $value)
			{
				if (is_null($value))
				{
					$valueLine .= 'null, ';
				}
				elseif (strpos($value, '-') || strpos($value, '.'))
				{
					$valueLine .= "'$value', ";
				}
				else
				{
					$valueLine .= $value . ', ';
				}
			}

			$values[] = sprintf('(%s)', trim($valueLine, ', '));
		}

		$queryLines[] = implode(",\n", $values) . ';';

		$query = implode('', $queryLines);

		$em = $this->getDoctrine()->getManager();

		/** @type \Doctrine\DBAL\Statement $statement */
		$statement = $em->getConnection()->prepare($query);
		$statement->execute();

		$this->addFlash('success', count($newData) . ' lines inserted');

		return $this->redirectToRoute('admin-tasks');
	}

	/**
	 * @Route("/backup", name="backup")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function backupAction(): Response
	{
		$pattern = '#mysql://(.+)\:(.+)@127.0.0.1:3306/(.+)#';

		preg_match($pattern, getenv('DATABASE_URL'), $matches);

		if (4 != count($matches))
		{
			throw new \UnexpectedValueException('Error parsing the database URL.');
		}

		$dbUser = $matches[1];
		$dbPass = $matches[2];
		$dbName = $matches[3];

		$cmd = sprintf('mysqldump -u%s -p%s %s|gzip 2>&1', $dbUser, $dbPass, $dbName);

		ob_start();
		passthru($cmd, $retVal);
		$gzip = ob_get_clean();

		if ($retVal)
		{
			throw new \RuntimeException('Error creating DB backup: ' . $gzip);
		}

		$fileName = date('Y-m-d') . '_backup.gz';
		$mime     = 'application/x-gzip';

		$message = (new \Swift_Message('Backup', '<h3>Backup</h3>Date: ' . date('Y-m-d'), 'text/html'))
			->attach(new \Swift_Attachment($gzip, $fileName, $mime))
			->setFrom('minicckuku@gmail.com')
			->setTo('minicckuku@gmail.com');

		$count = $this->get('mailer')->send($message);

		if (!$count)
		{
			$this->addFlash('danger', 'There was an error sending the message...');
		}
		else
		{
			$this->addFlash('success', 'Backup has been sent to your inbox.');
		}

		return $this->redirectToRoute('admin-tasks');
	}

	/**
	 * @param string $tableName
	 *
	 * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
	 */
	private function getTableData($tableName)
	{
		try
		{
			$em = $this->getDoctrine()->getManager();

			$query = 'SELECT * FROM ' . $tableName . ';';

			/** @type \Doctrine\DBAL\Statement $statement */
			$statement = $em->getConnection()->prepare($query);
			$statement->execute();

			$result = $statement->fetchAll();
		}
		catch (\Exception $exception)
		{
			$this->addFlash('danger', 'There was an error...');

			return $this->redirectToRoute('admin-tasks');
		}

		return $result;
	}
}
