<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие — «Рабочее время было зафиксировано автоматически».
 *
 * @event_category member
 * @event_name     employee_card_metric_delta
 */
class Type_Event_Member_WorkTimeAutoLogged {

	/** @var string тип события */
	public const EVENT_TYPE = "member.work_time_auto_logged";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $employee_user_id, int $work_time):Struct_Event_Base {

		$event_data = Struct_Event_Member_WorkTimeAutoLogged::build($employee_user_id, $work_time);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Member_WorkTimeAutoLogged {

		return Struct_Event_Member_WorkTimeAutoLogged::build(...$event["event_data"]);
	}
}
