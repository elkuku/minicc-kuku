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
    public function __construct(private string $rootDir)
    {
    }

    public function getRoot(): string
    {
        return $this->rootDir;
    }
}
