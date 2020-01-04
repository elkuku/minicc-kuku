<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Helper;

/**
 * Class IntlConverter
 */
class IntlConverter
{
	/**
	 * @param string|\DateTime $date
	 * @param string           $format
	 * @param string           $lang
	 *
	 * @return string
	 */
	public static function formatDate($date, string $format = "d 'de' MMMM YYYY", string $lang = 'es_ES'): string
	{
		$formatter = new \IntlDateFormatter('ES_es', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);

		$dateTime = \is_object($date) ? $date : new \DateTime($date);

		return $formatter->formatObject($dateTime, $format, $lang);
	}
}
