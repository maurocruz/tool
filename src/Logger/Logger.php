<?php
declare(strict_types=1);
namespace Plinct\Tool\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as Monolog;

class Logger {
	private string $channel = 'general';
	private string $pathfile = 'logs.log';
	private Level $level = Level::Debug;

	public function __construct(?string $channel, ?string $pathfile)
	{
		if ($channel) $this->channel = $channel;
		if ($pathfile) $this->pathfile = $pathfile;
		return $this;
	}

	/**
	 * @param string $channel
	 */
	public function setChannel(string $channel): void
	{
		$this->channel = $channel;
	}

	/**
	 * @param string $pathfile
	 */
	public function setPathfile(string $pathfile): void
	{
		$this->pathfile = $pathfile;
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function debug(string $message, array $context = [])
	{
		$this->level = Level::Debug;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function info(string $message, array $context = [])
	{
		$this->level = Level::Info;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function notice(string $message, array $context = [])
	{
		$this->level = Level::Notice;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function warning(string $message, array $context = [])
	{
		$this->level = Level::Warning;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function error(string $message, array $context = [])
	{
		$this->level = Level::Error;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function critical(string $message, array $context = [])
	{
		$this->level = Level::Critical;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function alert(string $message, array $context = [])
	{
		$this->level = Level::Alert;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function emergency(string $message, array $context = [])
	{
		$this->level = Level::Emergency;
		$this->write($message, $context);
	}
	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	private function write(string $message, array $context = [])
	{
		$monolog = new Monolog($this->channel);
		$monolog->pushHandler(new StreamHandler($this->pathfile, $this->level));
		switch ($this->level) {
			case Level::Debug: $monolog->debug($message, $context); break;
			case Level::Info: $monolog->info($message, $context); break;
			case Level::Notice: $monolog->notice($message, $context); break;
			case Level::Warning: $monolog->warning($message, $context); break;
			case Level::Error: $monolog->error($message, $context); break;
			case Level::Critical: $monolog->critical($message, $context); break;
			case Level::Alert: $monolog->alert($message, $context); break;
			case Level::Emergency: $monolog->emergency($message, $context); break;
		}
	}
}
