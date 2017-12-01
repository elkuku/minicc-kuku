<?php

namespace App\Helper\CsvParser;

/**
 * Class CsvParser
 */
class CsvParser
{
    /**
     * A simple method to parse a specific CSV file.
     *
     * @param array $contents File contents.
     *
     * @return CsvObject
     */
    public function parseCSV(array $contents)
    {
        if (!$contents) {
            throw new \UnexpectedValueException('CSV file is empty');
        }

        $csvObject = new CsvObject();

        $headVars = explode('","', trim(trim($contents[0]), '"'));

        $csvObject->headVars = $headVars;

        $lines = [];

        // Strip header
        unset($contents[0]);

        foreach ($contents as $line) {
            $fields = explode('","', trim(trim($line), '"'));

            $o = new \stdClass();

            foreach ($fields as $i => $field) {
                if (!isset($headVars[$i])) {
                    throw new \RuntimeException('Malformed CSV file.');
                }

                $o->{strtolower($headVars[$i])} = trim($field);
            }

            $lines[] = $o;
        }

        $csvObject->lines = $lines;

        return $csvObject;
    }
}
