<?php
declare(strict_types=1);
namespace Plinct\Tool\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as Monolog;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\ProcessorInterface;

class Logger {
	private string $channel = 'general';
	private string $pathfile = 'logs.log';
	private Level $level = Level::Debug;
	private ?ProcessIdProcessor $processor = null;

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
	 * @param ProcessorInterface|null $processor
	 * @return void
	 */
	public function info(string $message, array $context = [], ProcessorInterface $processor = null)
	{
		$this->level = Level::Info;
		$this->processor = $processor;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param ProcessorInterface|null $processor
	 * @return void
	 */
	public function notice(string $message, array $context = [], ProcessorInterface $processor = null)
	{
		$this->level = Level::Notice;
		$this->processor = $processor;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param ProcessorInterface|null $processor
	 * @return void
	 */
	public function warning(string $message, array $context = [], ProcessorInterface $processor = null)
	{
		$this->level = Level::Warning;
		$this->processor = $processor;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param ProcessorInterface|null $processor
	 * @return void
	 */
	public function error(string $message, array $context = [], ProcessorInterface $processor = null)
	{
		$this->level = Level::Error;
		$this->processor = $processor;
		$this->write($message, $context);
	}

	/**
	 * @param string $message
	 * @param array $context
	 * @param ProcessorInterface|null $processor
	 * @return void
	 */
	public function critical(string $message, array $context = [], ProcessorInterface $processor = null)
	{
		$this->level = Level::Critical;
		$this->processor = $processor;
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
		if ($this->processor) {
			$monolog->pushProcessor($this->processor);
		}
		switch ($this->level) {
			case Level::Info: $monolog->info($message, $context); break;
			case Level::Notice: $monolog->notice($message, $context); break;
			case Level::Warning: $monolog->warning($message, $context); break;
			case Level::Error: $monolog->error($message, $context); break;
			case Level::Critical: $monolog->critical($message, $context); break;
		}
	}
}
