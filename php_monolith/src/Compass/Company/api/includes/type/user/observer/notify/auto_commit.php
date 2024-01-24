<?php

namespace Compass\Company;

/**
 * Класс для работы с уведомлениями в обсервере
 */
class Type_User_Observer_Notify_AutoCommit extends Type_User_Observer_Default {

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	public static function provideJobExtra(int $user_id, int $job_type):array {

		return [
			"user_id"     => $user_id,                    // пользователь, с которым работаем
			"job_type"    => $job_type,                   // тип выполняем задачи (нужен чтобы исполнять разные задачи данного класса)
			"action_time" => time(),                      // время выполнения задачи
			"next_time"   => strtotime("next day 10:00"), // время когда в следующий раз выполняем
		];
	}

	/**
	 * Генерирует задачи, которые нужно взять на исполнение
	 *
	 * @param array $observer_data обсервер data
	 * @param array $job_extra     экстра для генерации задачи
	 */
	public static function provideJobList(array $observer_data, array $job_extra):array {

		// тип выполняемой задачи
		$job_type = $job_extra["job_type"];

		// если время не пришло, то просто выходим
		if ($observer_data[$job_type]["need_work"] > $job_extra["action_time"]) {
			return [];
		}

		// генерируем данные для выполнения задачи
		$job_data = [
			"user_id"       => $job_extra["user_id"],
			"job_type"      => $job_type,
			"observer_data" => $observer_data,
			"next_time"     => self::getNextWorkTime($job_extra),
		];

		return [
			$job_type => $job_data,
		];
	}

	/**
	 * Выполняем задачу
	 *
	 * @throws \parseException
	 */
	public static function doJob(array $job_data):int {

		$is_extended_employee_card_enabled = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY)["value"];

		// если включена базовая карточка, пропускаем задачу
		if ($is_extended_employee_card_enabled === 0) {
			return $job_data["next_time"];
		}

		// отправляем сообщение-уведомление об автофиксации рабочих часов
		self::_sendMessageAboutWorkedHours($job_data);

		return $job_data["next_time"];
	}

	/**
	 * Отправляем сообщение об автофиксации рабочих часов
	 *
	 * @throws \parseException
	 */
	protected static function _sendMessageAboutWorkedHours(array $job_data):void {

		$user_id = $job_data["user_id"];

		// проверяем, что сегодня действительно была автоматическая фиксация рабочих часов
		// (т.к. сегодняшняя автофиксация фиксирует рабочие часы за предыдущий день, то и берем предыдущий день)
		$day_start_at = dayStart() - DAY1;

		try {
			$worked_hours_obj = Type_User_Card_WorkedHours::getOneByDayStartAt($user_id, $day_start_at);
		} catch (\cs_RowIsEmpty) {
			return; // если фиксации не нашли
		}

		// если рабочие часы не принадлежат системе, то дальше не идем, так как фиксировал пользователь
		if ($worked_hours_obj->type != Type_User_Card_WorkedHours::WORKED_HOURS_SYSTEM_TYPE) {
			return;
		}

		// пушим событие о автоматической фиксации времени
		Gateway_Event_Dispatcher::dispatch(Type_Event_Member_WorkTimeAutoLogged::create($user_id, $worked_hours_obj->float_value), true);
	}

	/**
	 * Возвращает время следующего выполнения задачи
	 */
	public static function getNextWorkTime(array $job_extra):int {

		return $job_extra["next_time"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}