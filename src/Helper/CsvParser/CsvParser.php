<?php

namespace App\Helper\CsvParser;

use RuntimeException;
use stdClass;
use UnexpectedValueException;

class CsvParser
{
    public function parseCSV(array $contents): CsvObject
    {
        if (!$contents) {
            throw new UnexpectedValueException('CSV file is empty');
        }

        $csvObject = new CsvObject;

        $headVars = explode(',', trim(trim($contents[0]), '"'));

        $csvObject->headVars = $headVars;

        $lines = [];

        // Strip header
        unset($contents[0]);

        foreach ($contents as $line) {
            $fields = explode(',', trim(trim($line), '"'));

            $o = new stdClass;

            foreach ($fields as $i => $field) {
                if (!isset($headVars[$i])) {
                    throw new RuntimeException('Malformed CSV file.');
                }

                $o->{strtolower($headVars[$i])} = trim($field);
            }

            $lines[] = $o;
        }

        $csvObject->lines = $lines;

        return $csvObject;
    }
}
