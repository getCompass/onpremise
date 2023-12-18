<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Атрибут для методов-подписчиков на системные события.
 */
#[\Attribute]
class Type_Attribute_EventListener {

	public const EVENT       = 3;
	public const EVENT_QUEUE = 4;
	public const TASK_QUEUE  = 5;

	public const DEFAULT_GROUP = "default"; // дефолтная группа обработчика события (для простых и быстрых событий)
	public const SLOW_GROUP    = "slow";    // группа обработчика для более тяжеловесных событий

	/** @var string тип события, которое ожидает метод */
	public string $event_type;

	/** @var int тип триггера */
	public int $trigger_type;

	/** @var array экстра-данные для триггера */
	public array $trigger_extra;

	/**
	 * EventListener constructor.
	 *
	 * @param string $event_type
	 * @param int    $trigger_type
	 */
	public function __construct(string $event_type, int $trigger_type = self::EVENT_QUEUE, array $trigger_extra = []) {

		// добавляем дефолтные поля, если таковые отсутствуют
		if (!isset($trigger_extra["module"]) || mb_strlen($trigger_extra["module"]) < 1) {
			$trigger_extra["module"] = "php_" . CURRENT_MODULE;
		}
		if (!isset($trigger_extra["group"]) || mb_strlen($trigger_extra["group"]) < 1) {
			$trigger_extra["group"] = self::DEFAULT_GROUP;
		}

		$this->event_type    = $event_type;
		$this->trigger_type  = $trigger_type;
		$this->trigger_extra = $trigger_extra;
	}

	/**
	 * Формирует предмет подписки.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	public function makeSubscriptionItem():Struct_Event_System_SubscriptionItem {

		return match ($this->trigger_type) {
			static::EVENT => static::_makeSubscriptionItemEvent(),
			static::EVENT_QUEUE => static::_makeSubscriptionItemEventQueue(),
			static::TASK_QUEUE => static::_makeSubscriptionItemEventTask(),
		};
	}

	/**
	 * Подписка на одиночное событие.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	protected function _makeSubscriptionItemEvent():Struct_Event_System_SubscriptionItem {

		$trigger_extra = array_merge($this->trigger_extra, [
			"method" => "event.processEvent",
		]);

		return Struct_Event_System_SubscriptionItem::build($this->trigger_type, $this->event_type, $trigger_extra);
	}

	/**
	 * Подписка на события пачками.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	protected function _makeSubscriptionItemEventQueue():Struct_Event_System_SubscriptionItem {

		$trigger_extra = array_merge($this->trigger_extra, [
			"method" => "event.processEventList",
		]);

		return Struct_Event_System_SubscriptionItem::build($this->trigger_type, $this->event_type, $trigger_extra);
	}

	/**
	 * Подписка на задачи по событиям.
	 *
	 * @return Struct_Event_System_SubscriptionItem
	 * @throws \parseException
	 */
	protected function _makeSubscriptionItemEventTask():Struct_Event_System_SubscriptionItem {

		$trigger_extra = array_merge($this->trigger_extra, [
			"is_unique"   => 0,
			"error_limit" => 5, // до победного
		]);

		return Struct_Event_System_SubscriptionItem::build($this->trigger_type, $this->event_type, $trigger_extra);
	}
}