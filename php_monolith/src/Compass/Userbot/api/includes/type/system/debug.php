<?php

namespace Compass\Userbot;

/**
 * класс для отладки приложения
 * фиксируем время ответа API методов и тупящие моменты, логируем какие-то моменты
 */
class Type_System_Debug {

	protected array $_history = []; // сюда сохраняем логи

	// конструктор
	protected function __construct() {

		$this->_history = [];
	}

	// инициализация singleton
	public static function init():self {

		if (!isset($GLOBALS[__CLASS__])) {
			$GLOBALS[__CLASS__] = new self();
		}

		return $GLOBALS[__CLASS__];
	}

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// добавить временную метку для записи в лог
	public function addLabel(string $comment, array $extra = []):void {

		// получаем имя функции, из которой был вызыван debug
		$caller_function_str = self::_getCallerFunctionStr();

		// время после от начала запроса
		if (isset($_SERVER["REQUEST_TIME_FLOAT"])) {

			// добавляем время выполнения метода в лог
			$extra_default = [
				"time_ms" => round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000),
			];

			$extra = array_merge($extra_default, $extra);
		}

		// формируем текст лога
		$txt = "{$comment} [{$caller_function_str}]";

		// добавляем параметры из extra
		foreach ($extra as $k => $v) {

			$txt .= " {$k}: {$v};";
		}

		$this->_history[] = $txt;
	}

	// получить функцию, из которой был вызван debug
	protected static function _getCallerFunctionStr():string {

		$backtrace = debug_backtrace();

		// нас интересует 2 элемент массива
		array_shift($backtrace);
		$caller = array_shift($backtrace);

		// если имя метода пустое
		if (is_null($caller)) {
			return "undefined";
		}

		$path = str_replace(PATH_ROOT, "", $caller["file"]);
		$line = $caller["line"];

		return "{$path}:{$line}";
	}

	// сохранить запись в лог
	public function save(int $user_id = 0, bool $is_force = true):void {

		// получаем имя метода
		$api_method = get("api_method", "log_default");

		// заменяем "." в методе на "_" (например: "covnversations.get" на "conversations_get")
		$log_name = str_replace(".", "_", $api_method);

		// если передали is_force
		if (!$is_force) {

			if (!self::_isNeedSave($api_method)) {
				return;
			}

			$log_name = "slow_" . $log_name;
		}

		// формируем текст лога
		$txt = "method: {$api_method} user_id: {$user_id}; \n" . implode("\n", $this->_history);
		$txt .= "\n\n-----\n";

		Type_System_Admin::log($log_name, $txt, $is_force);
	}

	// проверяем, нужно ли сохранять запись в лог
	protected static function _isNeedSave(string $api_method):bool {

		// если константа не задана - выходим
		if (!defined("DEBUG_SLOW_METHODS")) {
			return false;
		}

		// если нет необходимости отслеживать медленные методы - выходим
		if (DEBUG_SLOW_METHODS !== true) {
			return false;
		}

		// если метод не критичен - выходим
		if (in_array($api_method, getConfig("METHODS_WHITELIST"))) {
			return false;
		}

		// если дебаг происходит из консоли - выходим
		if (isCLi()) {
			return false;
		}

		return true;
	}
}