<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use ReflectionMethod;

/**
 * Class Type_Task_Handler
 * Класс обработчика системных задач.
 *
 * Сюда попадают события из шины, то есть тут оно распределяется уже по методам.
 */
class Type_Task_Handler {

	// статусы доставки для событий, пришедших пачками
	public const DELIVERY_STATUS_DONE                    = 1;
	public const DELIVERY_STATUS_NEXT_ITERATION_REQUIRED = 2;
	public const DELIVERY_STATUS_NOT_PROCESSED           = 3;
	public const DELIVERY_STATUS_ERROR                   = 4;

	/** @var int максимально время для обработки в мс */
	protected const _TIME_LIMIT_FOR_BATCH = 10 * 1000;

	/** @var Type_Task_Handler|null для синглтона */
	protected static Type_Task_Handler|null $_instance = null;

	/** @var string[] классы, в которых объявлены подписчики */
	protected array $_executor_class_list = [
		Domain_System_Scenario_Event::class,
		Domain_User_Scenario_Event::class,
		Domain_Company_Scenario_Event::class,
		Domain_Remind_Scenario_Event::class,
		Domain_Member_Scenario_Event::class,
	];

	/** @var ReflectionMethod[][] список слушателей событий string */
	protected array $_executor_list = [

	];

	/**
	 * Type_Task_Handler constructor.
	 *
	 * @throws \ReflectionException
	 * @throws \parseException
	 */
	protected function __construct() {

		// обновляем всех подписчиков
		$this->_executor_list = $this->_updateExecutors();
	}

	/**
	 * Подписчик работает через синглтон.
	 * Все обращения должны быть пропущены через этот вызов.
	 *
	 * @return $this
	 */
	public static function instance():self {

		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Обработать задачу.
	 * Рассылает событие по конечным обработчикам.
	 *
	 * @param array $task
	 *
	 * @return Type_Task_Struct_Response
	 *
	 * @throws \ReflectionException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function handle(array $task):Type_Task_Struct_Response {

		// проверяем, что в данных вообще есть задача
		if (!isset($task["name"])) {
			throw new ParamException("task type not set");
		}

		// проверяем, что дата указана
		if (!isset($task["data"])) {
			throw new ParamException("task data not set");
		}

		// проверяем, что есть исполнитель задачи
		if (!isset($this->_executor_list[$task["name"]])) {
			return Type_Task_Struct_Response::build(static::DELIVERY_STATUS_DONE);
		}

		// передаем данные в обработчик
		$method_meta = $this->_executor_list[$task["name"]];

		/** @var Type_Task_Attribute_Executor $attribute */
		$attribute = $method_meta["attribute"];

		/** @var ReflectionMethod $method_reflection */
		$method_reflection = $method_meta["method_reflection"];

		// вызываем метод с данными таска
		return $method_reflection->invoke(null, $attribute->convertDataToStruct($task["data"]));
	}

	/**
	 * Обработать событие.
	 * Рассылает событие по конечным обработчикам.
	 *
	 * @param array $task_list
	 *
	 * @return array
	 */
	public function handleList(array $task_list):array {

		$output = [];

		$start_at = timeMs();

		foreach ($task_list as $task) {

			$key           = $task["unique_key"];
			$start_task_at = timeMs();

			if ($start_task_at > $start_at + static::_TIME_LIMIT_FOR_BATCH) {

				// не успели, сообщаем, что нужно снова запушить событие
				$output[$key] = $this::_createTaskProcessResult(static::DELIVERY_STATUS_NOT_PROCESSED, 0, time(), "execution time limit exceeded");
				continue;
			}

			try {

				// передаем в работу
				$result       = $this->handle($task);
				$output[$key] = $this::_createTaskProcessResult($result->status, $start_task_at, $result->need_work_at, $result->message);
			} catch (\Exception|\Error $e) {

				// если поймали исключение, то фиксируем это
				$output[$key] = $this::_createTaskProcessResult(static::DELIVERY_STATUS_ERROR, $start_task_at, time(), $e->getMessage());

				// логируем ошибку
				$exception_message = \BaseFrame\Exception\ExceptionUtils::makeMessage($e, HTTP_CODE_500);
				\BaseFrame\Exception\ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			}
		}

		return $output;
	}

	/**
	 * Создает информацию об обработке события.
	 *
	 * @param int    $status
	 * @param int    $delivered_at
	 * @param int    $need_work_at
	 * @param string $message
	 *
	 * @return array
	 */
	protected static function _createTaskProcessResult(int $status, int $delivered_at, int $need_work_at, string $message = ""):array {

		return [
			"result_state" => $status,
			"processed_in" => $delivered_at === 0 ? 0 : timeMs() - $delivered_at,
			"need_work_at" => $need_work_at,
			"message"      => $message,
		];
	}

	/**
	 * Получить список слушателей для всех событий
	 *
	 * @return array
	 */
	public function getExecutorList():array {

		return $this->_executor_list;
	}

	# region protected

	/**
	 * Выполняет обновление списка слушателей событий.
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \ReflectionException
	 */
	protected function _updateExecutors():array {

		$output = [];

		// перебираем всех объявленных слушателей
		foreach ($this->_executor_class_list as $listener_class) {

			// для обращения к атрибутам нам нужны отражения
			$reflection_class = new \ReflectionClass($listener_class);

			// перебираем все методы класса
			foreach ($reflection_class->getMethods() as $method_reflection) {

				// получаем все атрибуты EventListener для этого метода
				// их может быть несколько, ведь один метод может быть обработчиком для большого числа событий
				$attribute_reflection_list = $method_reflection->getAttributes(Type_Task_Attribute_Executor::class);

				// если метод не реализует атрибут, то нет смысла дальше проверять
				if (count($attribute_reflection_list) === 0) {
					continue;
				}

				// если метод не реализует атрибут, то нет смысла дальше проверять
				if (count($attribute_reflection_list) > 1) {
					throw new ParseFatalException("task has more than one executor");
				}

				// проверяем, что метод возвращает правильный тип данных
				if ($method_reflection->getReturnType()?->getName() !== Type_Task_Struct_Response::class) {
					throw new ParseFatalException("passed task executor returns incorrect type, " . Type_Task_Struct_Response::class . " expected");
				}

				foreach ($attribute_reflection_list as $attribute_reflection) {

					// получаем класс атрибута
					$attribute = $attribute_reflection->newInstance();

					// получаем из класса атрибута ожидаемое событие и заносим его как подписку на событие
					$output[$attribute->task_type] = [
						"method_reflection" => $method_reflection,
						"attribute"         => $attribute,
					];
				}
			}
		}

		return $output;
	}

	# endregion protected
}
