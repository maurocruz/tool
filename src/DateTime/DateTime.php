<?php

declare(strict_types=1);

namespace Plinct\Tool\DateTime;

use Exception;

class DateTime implements DateTimeInterface
{
	/**
	 * @var \DateTime
	 */
	private \DateTime $datetime;
	/**
	 * @var string
	 */
	private string $year;
	/**
	 * @var string
	 */
	private string $month;
	/**
	 * @var string
	 */
	private string $day;
	/**
	 * @var string
	 */
	private string $hour;
	/**
	 * @var string
	 */
	private string $minute;
	/**
	 * @var string
	 */
	private string $second;
	/**
	 * @var string
	 */
	private string $weekday;

	/**
	 * @param $date
	 * @param $timezone
	 */
	public function __construct($date, $timezone = NULL)
	{
		try {
			$datetime = new \DateTime($date, $timezone);
			$this->year = $datetime->format("Y");
			$this->month = $datetime->format("n");
			$this->day = $datetime->format("d");
			$this->hour = $datetime->format("H");
			$this->minute = $datetime->format("i");
			$this->second = $datetime->format("s");
			$this->weekday = $datetime->format("N");
			$this->datetime = $datetime;

		} catch (Exception $e) {

		}
	}

	/**
	 * @param string $expression
	 * @return string
	 */
	public function format(string $expression): string
	{
		return date_format($this->datetime, $expression);
	}

	/**
	 * @return string
	 */
	public function getYear(): string
	{
		return $this->year;
	}

	/**
	 * @return string
	 */
	public function getDay(): string
	{
		return $this->day;
	}

	/**
	 * @return string
	 */
	public function getMonth(): string
	{
		return $this->month;
	}

	/**
	 * @return string
	 */
	public function getWeekday(): string
	{
		return $this->weekday;
	}

	/**
	 * @return string
	 */
	public function getHour(): string
	{
		return $this->hour;
	}

	/**
	 * @return string
	 */
	public function getMinute(): string
	{
		return $this->minute;
	}

	/**
	 * @return string
	 */
	public function getSecond(): string
	{
		return $this->second;
	}
	/**
	 * @param $month
	 * @return string|null
	 */
	public function translateMonth($month): ?string
	{
		switch ($month) {
			case "1": return "janeiro";
			case "2": return "fevereiro";
			case "3": return "março";
			case "4": return "abril";
			case "5": return "maio";
			case "6": return "junho";
			case "7": return "julho";
			case "8": return "agosto";
			case "9": return "setembro";
			case "10": return "outubro";
			case "11": return "novembro";
			case "12": return "dezembro";
		}
		return null;
	}

	/**
	 * @param $weekday
	 * @param null $abrev
	 * @return string|null
	 */
	public function translateWeekday($weekday, $abrev = NULL): ?string
	{
		if ($abrev) {
			switch ($weekday) {
				case "1": return "seg";
				case "2": return "ter";
				case "3": return "qua";
				case "4": return "qui";
				case "5": return "sex";
				case "6": return "sáb";
				case "7": return "dom";
			}
		} else {
			switch ($weekday) {
				case "1": return "segunda";
				case "2": return "terça";
				case "3": return "quarta";
				case "4": return "quinta";
				case "5": return "sexta";
				case "6": return "sábado";
				case "7": return "domingo";
			}
		}
		return null;
	}

	/**
	 * @throws Exception
	 */
	public function readyDateTimeWithLiteral(): string
	{
		$date = date_format($this->datetime, "d/m/Y");
		if ($this->getHour()) {
			$date .= " às " . (int)$this->getHour() . "h";
			$date .= (int)$this->getMinute() ?? null;
			$date .= (int)$this->getSecond() ? 'm'.(int)$this->getSecond() : null;
		}
		return $date;

	}
}
