<?php

namespace BaseFrame\System;

use BaseFrame\Module\ModuleProvider;
use BaseFrame\Path\PathProvider;

/**
 * Класс для записи аналитики
 */
class Analytic {

	protected const _FILE_NAME  = "analytic_row";
	protected const _COMPANY_ID = 0;                   // т.е шлем с пивота

	protected string $_namespace; // неймспейс статистики

	protected function __construct(string $namespace) {

		$this->_namespace = $namespace;
	}

	// инициализируем и кладем класс в $GLOBALS
	public static function init(string|false $namespace):self {

		if (!$namespace) {
			$namespace = ModuleProvider::current();
		}

		if (isset($GLOBALS[__CLASS__][$namespace])) {
			return $GLOBALS[__CLASS__][$namespace];
		}

		$GLOBALS[__CLASS__][$namespace] = new self($namespace);

		return $GLOBALS[__CLASS__][$namespace];
	}

	// -------------------------------------------------------
	// ACTIONS
	// -------------------------------------------------------

	/**
	 * Инкрементим статистику для одного эвента
	 *
	 */
	public function inc(string $row):void {

		try {

			self::_save(toJson([
				"type"       => "TYPE_STAT_INC",
				"namespace"  => $this->_namespace,
				"company_id" => self::_COMPANY_ID,
				"key"        => $row,
				"value"      => 1,
				"event_time" => time(),
			]));
		} catch (\Exception) {
			// чтобы ничего не попадало если не сможем записать лог
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * сохраняем
	 */
	protected static function _save(...$arr):void {

		$text = "";
		foreach ($arr as $value) {

			$text .= formatArgs($value);
			$text .= "\n";
		}

		@writeToFile(PathProvider::logs() . "info/" . mb_strtolower(self::_FILE_NAME) . ".log", $text);
		@writeToFile(PathProvider::logs() . "debug.log", $text);
	}
}