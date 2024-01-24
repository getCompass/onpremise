<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Событие — необходимо сохранять сущности в поисковый индекс
 *
 * @event_category search
 * @event_name     process_queue
 */
class Type_Event_Search_IndexFillingQueue {

	/** @var string тип события */
	public const EVENT_TYPE = "search.process_index_filling_queue";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Search_IndexFillingQueue::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function parse(array $event):Struct_Event_Search_IndexFillingQueue {

		return Struct_Event_Search_IndexFillingQueue::build();
	}
}
