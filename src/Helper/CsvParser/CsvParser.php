<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

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
    public function parseCSV(array $contents): CsvObject
    {
        if (!$contents) {
            throw new \UnexpectedValueException('CSV file is empty');
        }

        $csvObject = new CsvObject;

        $headVars2 = explode('","', trim(trim($contents[0]), '"'));
        $headVars = explode(',', trim(trim($contents[0]), '"'));

        $csvObject->headVars = $headVars;

        $lines = [];

        // Strip header
        unset($contents[0]);

        foreach ($contents as $line) {
            $fields2 = explode('","', trim(trim($line), '"'));
            $fields = explode(',', trim(trim($line), '"'));

            $o = new \stdClass;

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
