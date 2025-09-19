<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Class Type_Event_Handler
 * Класс обработчика системных событий.
 *
 * Сюда попадают события из шины, то есть тут оно распределяется уже по методам.
 */
class Type_Event_Handler {

	// статусы доставки для событий, пришедших пачками
	protected const _DELIVERY_STATUS_DELIVERED = 1;
	protected const _DELIVERY_STATUS_REQUEUED  = 3;
	protected const _DELIVERY_STATUS_REJECTED  = 4;

	/** @var int максимально время для обработки в мс */
	protected const _TIME_LIMIT_FOR_BATCH = 10 * 1000;

	/** @var Type_Event_Handler|null для синглтона */
	protected static Type_Event_Handler|null $_instance = null;

	/** @var string[] классы, в которых объявлены подписчики */
	protected array $_listener_class_list = [
		Domain_Premise_Scenario_Event::class,
	];

	/** @var \ReflectionMethod[][] список слушателей событий string */
	protected array $_listener_list = [

	];

	/**
	 * Type_Event_Handler constructor.
	 *
	 * @throws \ReflectionException
	 */
	protected function __construct() {

		// обновляем всех подписчиков
		$this->_listener_list = $this->_updateListener();
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
	 * Обработать событие.
	 * Рассылает событие по конечным обработчикам.
	 *
	 * @param array $event
	 */
	public function handle(array $event):void {

		// проверяем наличие подписчиков на событие
		// если их нет, то дальше делать уже нечего
		if (!isset($this->_listener_list[$event["event_type"]])) {
			return;
		}

		// получаем обработчик события, который может распарсить данные
		[$category, $name] = explode(".", $event["event_type"]);

		$category = str_replace("_", "", ucwords($category, "_"));
		$name     = str_replace("_", "", ucwords($name, "_"));

		// обработчик для полученного события
		$event_handler = __NAMESPACE__ . "\Type_Event_{$category}_{$name}";

		// если такого обработчика нет, то событие обработать не получится
		if (!class_exists($event_handler)) {
			return;
		}

		if (\BaseFrame\Server\ServerProvider::isReserveServer()) {
			return;
		}

		// парсим данные из события
		$event_data = $event_handler::parse($event);

		// передаем данные в каждый обработчик
		foreach ($this->_listener_list[$event["event_type"]] as $handler) {
			$handler["method_reflection"]->invoke(null, $event_data);
		}
	}

	/**
	 * Обработать событие.
	 * Рассылает событие по конечным обработчикам.
	 *
	 * @param array $event_list
	 *
	 * @return array
	 */
	public function handleList(array $event_list):array {

		$output = [];

		$start_at = timeMs();

		foreach ($event_list as $event) {

			$key            = $event["uuid"];
			$start_event_at = timeMs();

			if ($start_event_at > $start_at + static::_TIME_LIMIT_FOR_BATCH) {

				// не успели, сообщаем, что нужно снова запушить событие
				$output[$key] = $this::_createDeliveryInfo(static::_DELIVERY_STATUS_REQUEUED, 0, "execution time limit exceeded");
				continue;
			}

			try {

				// передаем в работу
				$this->handle($event);
				$output[$key] = $this::_createDeliveryInfo(static::_DELIVERY_STATUS_DELIVERED, $start_event_at);
			} catch (\Exception|\Error $e) {

				// если поймали исключение, то фиксируем это
				$output[$key] = $this::_createDeliveryInfo(static::_DELIVERY_STATUS_REJECTED, $start_event_at, $e->getMessage());

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
	 * @param string $message
	 *
	 * @return array
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["status" => "int", "delivered_at" => "int", "processed_in" => "int", "requeue_delay" => "int", "message" => "string"])]
	protected static function _createDeliveryInfo(int $status, int $delivered_at, string $message = ""):array {

		return [
			"status"        => $status,
			"delivered_at"  => $delivered_at,
			"processed_in"  => $delivered_at === 0 ? 0 : timeMs() - $delivered_at,
			"requeue_delay" => 0,
			"message"       => $message,
		];
	}

	/**
	 * Получить список слушателей для всех событий
	 *
	 */
	public function getListenerList():array {

		return $this->_listener_list;
	}

	# region protected

	/**
	 * Выполняет обновление списка слушателей событий.
	 *
	 * @throws \ReflectionException
	 */
	protected function _updateListener():array {

		$output = [];

		// перебираем всех объявленных слушателей
		foreach ($this->_listener_class_list as $listener_class) {

			// для обращения к атрибутам нам нужны отражения
			$reflection_class = new \ReflectionClass($listener_class);

			// перебираем все методы класса
			foreach ($reflection_class->getMethods() as $method) {

				// получаем все атрибуты EventListener для этого метода
				// их может быть несколько, ведь один метод может быть обработчиком для большого числа событий
				$attributes = $method->getAttributes(Type_Attribute_EventListener::class);

				foreach ($attributes as $attribute) {

					// получаем класс атрибута
					$listener = $attribute->newInstance();

					// получаем из класса атрибута ожидаемое событие и заносим его как подписку на событие
					$output[$listener->event_type][] = [
						"method_reflection" => $method,
						"attribute"         => $listener,
					];
				}
			}
		}

		return $output;
	}

	# endregion protected
}
