<?php declare(strict_types = 1);

namespace Compass\Jitsi;

/**
 * Атрибут для методов-подписчиков на системные события.
 */
#[\Attribute]
class Type_Attribute_EventListener {

	public const EVENT       = 3;
	public const EVENT_QUEUE = 4;
	public const TASK_QUEUE  = 5;

	/** @var string тип события, которое ожидает метод */
	public string $event_type;

	/** @var int тип триггера */
	public int $trigger_type;

	/**
	 * EventListener constructor.
	 *
	 * @param string $event_type
	 * @param int    $trigger_type
	 */
	public function __construct(string $event_type, int $trigger_type = self::EVENT_QUEUE) {

		$this->event_type   = $event_type;
		$this->trigger_type = $trigger_type;
	}

	/**
	 * Формирует предмет подписки.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	public function makeSubscriptionItem():Struct_Event_System_SubscriptionItem {

		return match ($this->trigger_type) {
			static::EVENT       => $this->_makeSubscriptionItemEvent(),
			static::EVENT_QUEUE => $this->_makeSubscriptionItemEventQueue(),
			static::TASK_QUEUE  => $this->_makeSubscriptionItemEventTask(),
		};
	}

	/**
	 * Подписка на одиночное событие.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	protected function _makeSubscriptionItemEvent():Struct_Event_System_SubscriptionItem {

		return Struct_Event_System_SubscriptionItem::build($this->trigger_type, $this->event_type, [
			"module" => "php_" . CURRENT_MODULE,
			"method" => "event.processEvent",
			"group"  => "default",
		]);
	}

	/**
	 * Подписка на события пачками.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	protected function _makeSubscriptionItemEventQueue():Struct_Event_System_SubscriptionItem {

		return Struct_Event_System_SubscriptionItem::build($this->trigger_type, $this->event_type, [
			"module" => "php_" . CURRENT_MODULE,
			"method" => "event.processEventList",
			"group"  => "default",
		]);
	}

	/**
	 * Подписка на задачи по событиям.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	protected function _makeSubscriptionItemEventTask():Struct_Event_System_SubscriptionItem {

		return Struct_Event_System_SubscriptionItem::build($this->trigger_type, $this->event_type, [
			"is_unique"   => 0,
			"module"      => "php_" . CURRENT_MODULE, // модуль получатель события
			"group"       => "default",               // метод обработчик события
			"error_limit" => 5                        // до победного
		]);
	}
}