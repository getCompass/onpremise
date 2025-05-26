<?php

namespace BaseFrame\System;

/**
 * Класс для работы с лог файлами
 */
class Log {

	public const LOG_INFO    = 1;
	public const LOG_ERROR   = 2;
	public const LOG_SUCCESS = 3;

	public string $text;
	public int    $started_at;
	public int    $finished_at;
	public int    $type;

	/**
	 * Конструктор лога
	 */
	public function __construct() {

		$this->text = "";
		$date_time        = new \DateTime();
		$this->started_at = $date_time->getTimestamp();
		$this->addText("---------------------------------" . PHP_EOL .
			"LOG START" . PHP_EOL .
			"STARTED AT: " . $date_time->format("Y-m-d H:i:s.u") . PHP_EOL);
	}

	/**
	 * Подготовить текст для добавления в лог
	 *
	 * @param string $log
	 * @param int    $log_type
	 *
	 * @return Log
	 */
	public function addText(string $log, int $log_type = self::LOG_INFO):self {

		$this->text .= $this->_makeLogType($log_type);
		$this->text .= $this->_makeTimestamp() . " | " . $log . PHP_EOL;

		return $this;
	}

	/**
	 * Закрыть лог
	 *
	 * @return Log
	 */
	public function close():self {

		$date_time         = new \DateTime();
		$this->finished_at = $date_time->getTimestamp();

		$this->addText("---------------------------------" . PHP_EOL .
			"LOG END" . PHP_EOL .
			"ENDED AT: " . $date_time->format("Y-m-d H:i:s.u") . PHP_EOL .
			"LOG WRITE TIME: " . ($this->finished_at - $this->started_at) . "s");

		return $this;
	}

	/**
	 * Вернуть временную метку для лога
	 *
	 * @return string
	 */
	protected function _makeTimestamp():string {

		$date_time = (new \DateTime())->format("Y-m-d H:i:s.u");
		return "[" . $date_time . "]";
	}

	/**
	 * Сформировать строку с типом лога
	 *
	 * @param int $log_type
	 *
	 * @return string
	 */
	protected function _makeLogType(int $log_type):string {

		return match ($log_type) {
			self::LOG_ERROR => "------------------!!!ERROR!!!------------------" . PHP_EOL,
			self::LOG_SUCCESS => "------------------***SUCCESS***------------------" . PHP_EOL,
			default => "",
		};
	}

}