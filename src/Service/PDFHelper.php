<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 13.01.19
 * Time: 13:18
 */

namespace App\Service;

class PDFHelper
{
    private string $root;

    public function __construct(string $rootDir)
    {
        $this->root = $rootDir;
    }

    public function getRoot(): string
    {
        return $this->root;
    }
}
