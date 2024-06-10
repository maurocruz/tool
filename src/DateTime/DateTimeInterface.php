<?php

declare(strict_types=1);

namespace Plinct\Tool\DateTime;

interface DateTimeInterface
{
	/**
	 * @param string $expression
	 * @return string
	 */
	public function format(string $expression): string;

	/**
	 * @return string
	 */
	public function getYear(): string;

	/**
	 * @return string
	 */
	public function getDay(): string;

	/**
	 * @return string
	 */
	public function getMonth(): string;

	/**
	 * @return string
	 */
	public function getWeekday(): string;

	/**
	 * @param $month
	 * @return string|null
	 */
	public function translateMonth($month): ?string;

	/**
	 * @param $weekday
	 * @param $abrev
	 * @return string|null
	 */
	public function translateWeekday($weekday, $abrev = NULL): ?string;

	/**
	 * @return string
	 */
	public function readyDateTimeWithLiteral(): string;
}
