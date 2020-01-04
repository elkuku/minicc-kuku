<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 08/07/18
 * Time: 18:00
 */

namespace App\Helper;

class GitLoader
{
	private $projectDir;

	public function __construct($rootDir)
	{
		$this->projectDir = $rootDir;
	}

	public function getBranchName(): string
	{
		$gitHeadFile = $this->projectDir . '/.git/HEAD';
		$branchname  = 'no branch name';

		$stringFromFile = file_exists($gitHeadFile) ? file($gitHeadFile, FILE_USE_INCLUDE_PATH) : '';

		if ($stringFromFile !== 0 && \is_array($stringFromFile))
		{
			// Get the string from the array
			$firstLine = $stringFromFile[0];

			// Seperate out by the "/" in the string
			$explodedString = explode('/', $firstLine, 3);

			$branchname = trim($explodedString[2]);
		}

		return $branchname;
	}

	public function getLastCommitMessage(): string
	{
		$gitCommitMessageFile = $this->projectDir . '/.git/COMMIT_EDITMSG';
		$commitMessage        = file_exists($gitCommitMessageFile) ? file($gitCommitMessageFile, FILE_USE_INCLUDE_PATH) : '';

		return \is_array($commitMessage) ? trim($commitMessage[0]) : '';
	}

	public function getLastCommitDetail(): array
	{
		$gitLogFile = $this->projectDir . '/.git/logs/HEAD';
		$gitLogs    = file_exists($gitLogFile) ? file($gitLogFile, FILE_USE_INCLUDE_PATH) : '';
		$sha = trim($this->execCommand('cd ' . $this->projectDir . ' && git rev-parse --short HEAD'));

		preg_match("/([\w]+) ([\w]+) ([\w\s]+) (<[\w\.@]+>) ([\d]+) ([\d-]+)\tcommit: ([\w\s]+)/", end($gitLogs), $matches);

		$logs = [];

		$logs['author'] = $matches[3] ?? 'not defined';
		$logs['date']   = isset($matches[5]) ? date('Y/m/d H:i', $matches[5]) : 'not defined';
		$logs['sha'] = $sha;

		return $logs;
	}

	/**
	 * Execute a command on the server.
	 *
	 * @param   string  $command  The command to execute.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	protected function execCommand($command): string
	{
		ob_start();
		$lastLine = system($command, $status);

		if ($status)
		{
			// Command exited with a status != 0
			if ($lastLine)
			{
				throw new \RuntimeException($lastLine);
			}

			throw new \RuntimeException('An unknown error occurred');
		}

		ob_end_clean();

		return $lastLine;
	}
}
