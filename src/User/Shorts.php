<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


/**
 * 🩳 Shorts (inspired by https://github.com/dakujem/shorts)
 *
 * Tool to shorten or limit personal names to desired length, or to use initials instead of a full name.
 * Supports Unicode / UTF-8 names.
 *
 * | John Roland Reuel Tolkien |
 * | --> John R. Reuel Tolkien |   # last name priority
 * | -----> John R. R. Tolkien |
 * | -------> J. R. R. Tolkien |
 * | ---------> J.R.R. Tolkien |
 * | -----------> John Tolkien |
 * | ---------------> J.R.R.T. |
 * | -------------------> JRRT |
 * | ---------------------> JT |
 * |                           |
 * | John Roland Reuel Tolkien |
 * | John R. R. Tolkien <----- |   # first name priority
 * | John R. R. T. <---------- |
 * | John R.R.T. <------------ |
 * | J.R.R.T. <--------------- |
 * | JRRT <------------------- |
 * | JT <--------------------- |
 */
final class Shorts
{
	public const STRATEGY_FIRST_NAME = 'fn';

	public const STRATEGY_LAST_NAME = 'ln';


	/** Static shorthand for the `limit` method. */
	public static function process(string $fullName, int $limit, string $priority = self::STRATEGY_LAST_NAME): string
	{
		return (new self)->limit($fullName, $limit, $priority);
	}


	/**
	 * Limit a given full name in length,
	 * shorten by reducing name parts to initials if needed,
	 * so that the length of the result does not exceed given limit.
	 * Depending on $priority, last name (default) or first name will be kept intact, when possible.
	 *
	 * input "John Ronald Reuel Tolkien"   ($priority=LAST_NAME, gradually reducing $limit)
	 *    -> "John R. Reuel Tolkien"
	 *    -> "John R. R. Tolkien"
	 *    -> "J. R. R. Tolkien"
	 *    -> "J.R.R. Tolkien"
	 *    -> "J. Tolkien"
	 *    -> "J.R.R.T."
	 *    -> "JRRT"
	 *    -> "JT"
	 *    -> "T"
	 *
	 * input "John Ronald Reuel Tolkien"  ($priority=FIRST_NAME, gradually reducing $limit)
	 *    -> "John R. Reuel Tolkien"
	 *    -> "John R. R. Tolkien"
	 *    -> "John R. R. T."
	 *    -> "John R.R.T."
	 *    -> "J.R.R.T."
	 *    -> "John T."
	 *    -> "JRRT"
	 *    -> "JT"
	 *    -> "T"
	 */
	public function limit(string $fullName, int $limit, string $strategy = self::STRATEGY_LAST_NAME): string
	{
		if ($strategy === self::STRATEGY_FIRST_NAME) {
			return $this->reduceLast($fullName, $limit);
		}
		if ($strategy === self::STRATEGY_LAST_NAME) {
			return $this->reduceFirst($fullName, $limit);
		}

		throw new \InvalidArgumentException('Strategy "' . $strategy . '" is not supported.');
	}


	/**
	 * Reduce a name to initials
	 * keeping either the last name (default) or the first name intact.
	 *
	 * input "John Ronald Reuel Tolkien" -> "J. R. R. Tolkien" | "John R. R. T."
	 */
	public function reduce(string $fullName, string $priority = self::STRATEGY_LAST_NAME): string
	{
		if ($priority === self::STRATEGY_FIRST_NAME) {
			return $this->keepFirst($fullName);
		}
		if ($priority === self::STRATEGY_LAST_NAME) {
			return $this->keepLast($fullName);
		}

		throw new \InvalidArgumentException('Priority "' . $priority . '" is not supported.');
	}


	/**
	 * @param string[] $significantNames
	 * @param string[] $middleNames
	 */
	public function significantLastName(
		int $limit,
		int $minimalReduction,
		array $significantNames,
		array $middleNames
	): string {
		$lastName = $significantNames[0];
		$firstName = $significantNames[1];

		// move the first name to the end, so that it is reduced last
		[$reduced, $reduction] = $this->reduceParts(array_merge($middleNames, [$firstName]), $minimalReduction);
		/** @var string[] $reduced */
		/** @var int $reduction */
		$tmp = array_pop($reduced);
		array_unshift($reduced, $tmp);
		if ($reduction >= $minimalReduction) {
			// success, the actual length reduction is greater or equal to the targeted reduction
			return $this->implode(array_merge($reduced, [$lastName]));
		}

		$attempt1 = $this->implode($reduced, '.', '') . ' ' . $lastName; // J.R.R. Tolkien
		if (strlen($attempt1) <= $limit) {
			return $attempt1;
		}

		// at this point we need to try these two and see which one to return
		$attempt2 = $reduced[0] . '. ' . $lastName; // J. Tolkien (middle names omitted)
		$attempt3 = $this->implode(array_merge($reduced, [$this->_initials([$lastName])]), '.', ''); // J.R.R.T. (initials only)
		$l2 = strlen($attempt2);
		$l3 = strlen($attempt3);

		if ($l2 <= $limit && $l3 > $limit) {
			return $attempt2;
		}
		if ($l3 <= $limit && $l2 > $limit) {
			return $attempt3;
		}
		if ($l2 <= $limit && $l3 <= $limit) {
			return $l2 >= $l3 ? $attempt2 : $attempt3;
		}
		throw new \LogicException('wtf'); // this should never happen
	}


	/**
	 * @param string[] $significantNames
	 * @param string[] $middleNames
	 */
	public function significantFirstName(
		int $limit,
		int $minimalReduction,
		array $significantNames,
		array $middleNames
	): string {
		$firstName = $significantNames[0];
		$lastName = $significantNames[1];

		// move the first name to the end, so that it is reduced last
		[$reduced, $reduction] = $this->reduceParts(array_merge($middleNames, [$lastName]), $minimalReduction);
		/** @var string[] $reduced */
		/** @var int $reduction */
		if ($reduction >= $minimalReduction) {
			// success, the actual length reduction is greater or equal to the targeted reduction
			return $this->implode(array_merge([$firstName], $reduced));
		}

		$attempt1 = $firstName . ' ' . $this->implode($reduced, '.', ''); // John R.R.T.
		if (strlen($attempt1) <= $limit) {
			return $attempt1;
		}

		// at this point we need to try these two and see which one to return
		$attempt2 = $firstName . ' ' . $reduced[count($reduced) - 1] . '.'; // John T. (middle names omitted)
		$attempt3 = $this->implode(array_merge([$this->_initials([$firstName])], $reduced), '.', ''); // J.R.R.T. (initials only)
		$l2 = strlen($attempt2);
		$l3 = strlen($attempt3);

		if ($l2 <= $limit && $l3 > $limit) {
			return $attempt2;
		}
		if ($l3 <= $limit && $l2 > $limit) {
			return $attempt3;
		}
		if ($l2 <= $limit && $l3 <= $limit) {
			return $l2 >= $l3 ? $attempt2 : $attempt3;
		}
		throw new \LogicException('wtf'); // this should never happen
	}


	private function reduceFirst(string $full, int $limit): string
	{
		return $this->limitNameTo($full, $limit, fn(array $parts): array => [
			$parts[count($parts) - 1], // last name
			$parts[0], // fist name
		], [$this, 'significantLastName']);
	}


	private function reduceLast(string $full, int $limit): string
	{
		return $this->limitNameTo($full, $limit, fn(array $parts): array => [
			$parts[0], // fist name
			$parts[count($parts) - 1], // last name
		], [$this, 'significantFirstName']);
	}


	private function limitNameTo(string $full, int $limit, callable $significant, callable $subroutine): string
	{
		if ($limit < 1) {
			throw new \InvalidArgumentException('You may have slipped...');
		}
		$name = trim($full);
		$len = strlen($name);
		if ($len <= $limit) {
			// no need to do anything since the required max length is shorter than the original string
			return $name;
		}

		$parts = $this->explode($full);
		$num = count($parts);

		$significantNames = call_user_func($significant, $parts);
		$mostSignificantNameLength = strlen($significantNames[0]);

		if (
			$num > 1 && // Note: if there was only one word ($num===1), it would have been returned above
			$mostSignificantNameLength < $limit && // if the most significant name is not shorter than the limit initials will have to be used anyway
			(2 * $num <= $limit || $mostSignificantNameLength + 3 <= $limit) // the initials in the shortest form, have a dot between them; omission of middle names
		) {
			// first try to reduce the first and middle names
			$middleNames = array_slice($parts, 1, -1);
			$target = $len - $limit; // this is the targeted minimum reduction needed for the shortener to be successful

			$candidate = call_user_func($subroutine, $limit, $target, $significantNames, $middleNames);
			if ($candidate !== null) {
				return $candidate;
			}
		}

		// fall back to using initials
		return $this->limitInitials($parts, $limit);
	}


	/**
	 * @param string[] $parts
	 * @return string[][]|int[]
	 */
	private function reduceParts(array $parts, int $minimalReduction): array
	{
		$result = [];
		$reduction = 0;
		foreach ($parts as $part) {
			if ($reduction < $minimalReduction) {
				$reduction += strlen($part) - 2;
				$result[] = $part[0];
			} else {
				$result[] = $part;
			}
		}

		return [$result, $reduction];
	}


	/**
	 * Reduce a full name to to initials keeping the first name intact.
	 *
	 * "Hugo Ventil" -> "Hugo V."
	 * "John Ronald Reuel Tolkien" -> "John R. R. T."
	 */
	private function keepFirst(string $full, string $suffix = '.', string $glue = ' '): string
	{
		if ($full === '') {
			return '';
		}
		$parts = $this->explode($full);
		$first = array_shift($parts);

		return $this->implode(array_merge([$first], array_map(fn(string $p): string => $p[0], $parts)), $suffix, $glue);
	}


	/**
	 * Reduce a full name to to initials keeping the last name intact.
	 *
	 * "Hugo Ventil" -> "H. Ventil"
	 * "John Ronald Reuel Tolkien" -> "J. R. R. Tolkien"
	 */
	private function keepLast(string $full, string $suffix = '.', string $glue = ' '): string
	{
		if ($full === '') {
			return '';
		}
		$parts = $this->explode($full);

		return $this->implode(array_merge(array_map(fn(string $p): string => $p[0], $parts), [array_pop($parts)]), $suffix, $glue);
	}


	/**
	 * @param string[] $parts
	 */
	private function _initials(array $parts, string $suffix = '', string $glue = ''): string
	{
		return $this->implode(array_map(fn(string $p): string => $p[0], $parts), $suffix, $glue);
	}


	/**
	 * @param string[] $parts
	 */
	private function limitInitials(array $parts, int $limit): string
	{
		$num = count($parts);
		if ($limit < $num) { // special case first
			if ($limit === 1) {
				return $this->_initials([ // return the first letter of the last name only
					$parts[$num - 1],
				]);
			}

			return $this->_initials([ // omit middle names
				$parts[0],
				$parts[$num - 1],
			]);
		}

		// then the usual initials
		return $this->_initials($parts);
	}


	/**
	 * Explode the name into parts.
	 *
	 * @return string[]
	 */
	private function explode(string $input): array
	{
		/** @phpstan-ignore-next-line */
		return array_values(array_filter((array) preg_split('/\W+/u', $input), fn(string $s): bool => $s !== ''));
	}


	/**
	 * Put the parts together.
	 * To each initial add a dot ($suffix), and glue all parts together with a space ($glue).
	 *
	 * @param string[]|null[] $parts
	 * @param string $suffix suffix added to each produced initial, usually this would be empty to produce "AB" or a dot to produce "A. Bee"
	 * @param string $glue glue to put the parts together, usually an empty string to produce "AB" or a space to produce "A. Bee"
	 */
	private function implode(array $parts, string $suffix = '.', string $glue = ' '): string
	{
		return implode($glue, array_map(static fn(?string $p): string => $p . (strlen((string) $p) === 1 ? $suffix : ''), $parts));
	}
}
