<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 16/05/17
 * Time: 2:45
 */

namespace App\Service;

class ShaFinder
{
	/**
	 * @var string
	 */
	private $sha = 'n/a';

	public function __construct(string $root)
	{
		if (file_exists($root . '/sha.txt'))
		{
			$this->sha = file_get_contents($root . '/sha.txt') ?: 'n/a';
		}
		elseif (file_exists($root . '/.git/refs/heads/master'))
		{
			$this->sha = file_get_contents($root . '/.git/refs/heads/master') ?: 'n/a';
		}
	}

	public function getSha(): string
	{
		return $this->sha;
	}
}
