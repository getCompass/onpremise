<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\ParamException;

/**
 * Class Type_Script_Handler
 * Обработчик для выполнения скриптов.
 */
class Type_Script_Handler {

	/** @var int маска для исполнения скрипта — вызов без изменений */
	public const _DRY_MASK = 1 << 0;
	/** @var int маска для исполнения скрипта — асинхронный вызов */
	public const _ASYNC_MASK = 1 << 1;

	/** @var string уровень логирования — ошибка */
	protected const _LOG_LEVEL_ERROR = "error";

	/**
	 * Выполняет скрипт.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param int    $flag_mask
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \ReflectionException
	 */
	public static function exec(string $name, array $data, int $flag_mask):array {

		static::_assertScriptClass($name);

		// для асинхронного запроса оборачиваем в событие и возвращаем информацию об этом
		if (static::isAsync($flag_mask)) {
			return static::_dispatchAsync($name, $data, $flag_mask);
		}

		return static::_exec($name, $data, $flag_mask);
	}

	/**
	 * Диспатчит асинхронное исполнение скрипта.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param int    $flag_mask
	 *
	 * @return string[]
	 * @throws \parseException
	 */
	protected static function _dispatchAsync(string $name, array $data, int $flag_mask):array {

		// запрещаем работать асинхронные вызовы в dry mode
		if (static::isDry($flag_mask)) {
			return ["", "script can not be executed in dry mode if async flag passed"];
		}

		// диспатчим событие на асинхронное исполнение
		Gateway_Event_Dispatcher::dispatch(Type_Event_System_AsyncModuleUpdateInitialized::create(CURRENT_MODULE, $name, $data));
		return ["script executed in async mode", ""];
	}

	/**
	 * Вызывает исполнение скрипта в асинхронном режиме.
	 *
	 * Асинхронный вызов не возвращает логи (ему некуда их возвращать)
	 * и не поддерживает dry-run, поскольку информацию так же некуда выводить.
	 *
	 * @param Struct_Event_System_AsyncModuleUpdateInitialized $event
	 *
	 * @throws \paramException
	 * @throws \ReflectionException
	 */
	#[Type_Attribute_EventListener(Type_Event_System_AsyncModuleUpdateInitialized::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function execAsync(Struct_Event_System_AsyncModuleUpdateInitialized $event):void {

		static::_assertScriptClass($event->script_name);

		$data = $event->data;
		static::_exec($event->script_name, $data, 0);
	}

	/**
	 * Проверяет, является ли вызов скрипта асинхронным.
	 *
	 * @param int $flag_mask
	 *
	 * @return bool
	 */
	public static function isAsync(int $flag_mask):bool {

		return $flag_mask & static::_ASYNC_MASK;
	}

	/**
	 * Проверяет, является ли вызов скрипта асинхронным.
	 *
	 * @param int $flag_mask
	 *
	 * @return bool
	 */
	public static function isDry(int $flag_mask):bool {

		return $flag_mask & static::_DRY_MASK;
	}

	# region protected

	/**
	 * Проверяет, что на вход поступил подходящий класс.
	 *
	 * @param string $script_name
	 *
	 * @throws \ReflectionException
	 * @throws \paramException
	 */
	protected static function _assertScriptClass(string $script_name):void {

		$script_class_name = static::_makeClassName($script_name);

		// проверяем существование класса
		if (!class_exists($script_class_name)) {

			throw new ParamException("passed incorrect class: {$script_name} was not found");
		}

		// проверяем, что тип подходит
		$reflection_instance = (new \ReflectionClass($script_class_name))->newInstanceWithoutConstructor();

		if (!$reflection_instance instanceof Type_Script_CompanyUpdateTemplate) {

			throw new ParamException("passed incorrect class: {$script_name} has wrong type");
		}
	}

	/**
	 * Вызывает исполнение логики.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param int    $flag_mask
	 *
	 * @return string[]
	 *
	 * @throws \paramException
	 * @throws \ReflectionException
	 */
	protected static function _exec(string $name, array $data, int $flag_mask):array {

		// конвертим имя скрипта
		$script_class_name   = static::_makeClassName($name);
		$reflection_instance = (new \ReflectionClass($script_class_name))->newInstanceWithoutConstructor();

		if ($reflection_instance instanceof Type_Script_CompanyUpdateTemplate) {
			return static::_call($name, $data, $flag_mask);
		} else {

			throw new ParamException("script {$name} doesn't exist");
		}
	}

	/**
	 * Вызывает и обрабатывает логику скрипта.
	 *
	 * @param string $script_name
	 * @param array  $data
	 * @param int    $flag_mask
	 *
	 * @return array
	 * @throws
	 */
	protected static function _call(string $script_name, array $data, int $flag_mask):array {

		try {

			$script_class_name = static::_makeClassName($script_name);
			static::_log($script_name, "script {$script_name} execution started");

			/** @var Type_Script_CompanyUpdateTemplate $instance */
			$instance = new $script_class_name($flag_mask);
			$instance->exec($data);

			static::_log($script_name, "script {$script_name} execution done");
		} catch (\Exception $e) {

			static::_log($script_name, "script {$script_name} execution failed {$e->getMessage()}", static::_LOG_LEVEL_ERROR);
			throw $e;
		}

		return static::_getResult($instance, $script_name, $flag_mask);
	}

	/**
	 * Возвращает название класс для скрипта.
	 *
	 * @param string $script_name
	 *
	 * @return string
	 */
	protected static function _makeClassName(string $script_name):string {

		$script_name = str_replace("_", "", ucwords($script_name, "_"));
		return __NAMESPACE__ . "\Type_Script_Source_{$script_name}";
	}

	/**
	 * Возвращает результат работы скрипта.
	 *
	 * @param Type_Script_CompanyUpdateTemplate $instance
	 * @param string                            $script_name
	 * @param int                               $script_mask
	 *
	 * @return array
	 */
	protected static function _getResult(Type_Script_CompanyUpdateTemplate $instance, string $script_name, int $script_mask):array {

		$log       = $instance->getLog();
		$error_log = $instance->getError();

		if (!self::isDry($script_mask)) {

			static::_log($script_name, $log);
			static::_log($script_name, $error_log, static::_LOG_LEVEL_ERROR);

			if (!isTestServer() && $error_log !== "") {

				try {

					// пытаемся отправить в чет
					Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_EXCEPTION, mb_substr($error_log, 0, 2000));
				} catch (\Exception) {
					static::_log($script_name, "can't send error message to bot", static::_LOG_LEVEL_ERROR);
				}
			}
		}

		return [$log, $error_log];
	}

	/**
	 * Выполняет запись лога в файл.
	 *
	 * @param string $script_name
	 * @param string $message
	 * @param string $level
	 */
	protected static function _log(string $script_name, string $message, string $level = ""):void {

		if ($message === "") {
			return;
		}

		$level     = $level !== "" ? "-{$level}" : "";
		$file_name = "script{$level}-{$script_name}";

		Type_System_Admin::log($file_name, $message);
	}

	# endregion protected
}
