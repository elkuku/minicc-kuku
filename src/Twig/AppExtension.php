<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Twig;

use App\Entity\User;
use App\Service\ShaFinder;
use App\Service\TaxService;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;

/**
 * Class AppExtension
 */
class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * AppExtension constructor.
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilters(): array
	{
		return [
			new \Twig_SimpleFilter('price', [$this, 'priceFilter']),
			new \Twig_SimpleFilter('conIva', [$this, 'conIvaFilter']),
			new \Twig_SimpleFilter('taxFromTotal', [$this, 'taxFromTotalFilter']),
			new \Twig_SimpleFilter('invert', [$this, 'invertFilter']),
			new \Twig_SimpleFilter('cast_to_array', [$this, 'objectFilter']),
			new \Twig_SimpleFilter('short_name', [$this, 'shortName']),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFunctions(): array
	{
		return [
			new \Twig_SimpleFunction('intlDate', [$this, 'intlDate']),
			new \Twig_SimpleFunction('formatRUC', [$this, 'formatRUC']),
			new \Twig_SimpleFunction('getSHA', [$this, 'getSHA']),
		];
	}

	/**
	 * @param float   $number
	 * @param integer $decimals
	 * @param string  $decPoint
	 * @param string  $thousandsSep
	 *
	 * @return string
	 */
	public function priceFilter($number, $decimals = 2, $decPoint = '.', $thousandsSep = ','): string
	{
		$price = number_format($number, $decimals, $decPoint, $thousandsSep);
		$price = sprintf(
			'<span class="%s">%s</span>',
			($price < 0 ? 'amount amount-red' : 'amount'),
			$price
		);

		return $price;
	}

	/**
	 * Invert a value
	 *
	 * @param integer $value
	 *
	 * @return integer
	 */
	public function invertFilter($value): int
	{
		return -$value;
	}

	/**
	 * @param mixed  $date String or DateTime object
	 * @param string $format
	 * @param string $lang
	 *
	 * @return string
	 */
	public function intlDate($date, $format = "d 'de' MMMM YYYY", $lang = 'es_ES'): string
	{
		/** @noinspection PhpUndefinedClassInspection */
		$formatter = new \IntlDateFormatter('ES_es', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);

		$dateTime = \is_object($date) ? $date : new \DateTime($date);

		return $formatter->formatObject($dateTime, $format, $lang);
	}

	/**
	 * Convert object to array for Twig usage..
	 *
	 * @param object $classObject
	 *
	 * @return array
	 */
	public function objectFilter($classObject): array
	{
		$array    = (array) $classObject;
		$response = [];

		$className = \get_class($classObject);

		foreach ($array as $k => $v)
		{
			$response[trim(str_replace($className, '', $k))] = $v;
		}

		return $response;
	}

	/**
	 * Shorten a (latin) name
	 *
	 * @param string $longName
	 *
	 * @return string
	 */
	public function shortName(string $longName): string
	{
		// E.g. Juan José Perez Pillo
		$parts = explode(' ', $longName);

		if (2 === \count($parts))
		{
			// Juan Perez => Juan Perez
			return $longName;
		}

		if (3 === \count($parts))
		{
			// Juan José Perez => Juan Perez
			return $parts[0] . ' ' . $parts[2];
		}

		if (4 === \count($parts))
		{
			// Juan José Perez Pillo => Juan Perez
			return $parts[0] . ' ' . $parts[2];
		}

		return $longName;
	}

	/**
	 * Add the tax value to a given amount.
	 */
	public function conIvaFilter(float $value): float
	{
		return $this->container->get(TaxService::class)->getValueConTax($value);
	}

	/**
	 * Add the tax value to a given amount.
	 */
	public function taxFromTotalFilter(float $value): float
	{
		return $this->container->get(TaxService::class)->getTaxFromTotal($value);
	}

	public function getSHA()
	{
		return $this->container->get(ShaFinder::class)->getSha();
	}

	/**
	 * Cleanup a long number and make it readable.
	 */
	public function formatRUC(User $user): string
	{
		$ruc = '?';

		if ($user->getInqRuc())
		{
			$ruc = $user->getInqRuc();

			if (13 === \strlen($ruc))
			{
				$rucs = str_split($ruc, 10);

				$ruc = chunk_split($rucs[0], 3, ' ') . ' ' . $rucs[1];
			}
			else
			{
				$ruc = chunk_split($ruc, 3, ' ');
			}
		}
		elseif ($user->getInqCi())
		{
			$ruc = str_replace('-', '', $user->getInqCi());
			$ruc = chunk_split($ruc, 3, ' ');
		}

		return $ruc;
	}

	public static function getSubscribedServices():array
	{
		return [
			ShaFinder::class,
			TaxService::class
		];
	}
}
