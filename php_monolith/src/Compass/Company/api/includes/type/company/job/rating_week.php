<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Дефолтный класс для задач крона отправки рейтинга по компании
 */
class Type_Company_Job_RatingWeek extends Type_Company_Job_Default {

	/** @var string ключ для хранения параметров задачи */
	protected const _RATING_SENDER_KEY = "rating_week_job";

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	public static function provideJobExtra():array {

		return [
			"action_time" => time(),                            // время совершения действия
			"next_time"   => strtotime("next Monday 10 hours"), // когда нужно исполнить действие в следующий раз
		];
	}

	/**
	 * Генерирует задачи, которые нужно взять на исполнение.
	 *
	 * @param array $extra экстра для генерации таска
	 *
	 * @throws \parseException
	 */
	public static function provideJobList(array $extra):array {

		$datastore_row = Domain_Company_Action_Config_Get::do(self::_RATING_SENDER_KEY);

		// если еще не было понедельников то ставим в работу
		if (!isset($datastore_row["need_work"])) {

			Domain_Company_Action_Config_Set::do(["need_work" => $extra["next_time"]], self::_RATING_SENDER_KEY);
			return [];
		}

		// если время не пришло просто выходим
		if ($datastore_row["need_work"] > $extra["action_time"]) {
			return [];
		}

		// генерируем данные задачи
		$last_monday = strtotime("last Monday");

		$task = [
			"year"      => intval(date("o", $last_monday)),
			"week"      => intval(date("W", $last_monday)),
			"next_time" => intval($extra["next_time"]),
		];

		// возвращаем массив с одной задачей
		return [
			self::_RATING_SENDER_KEY => $task,
		];
	}

	/**
	 * Исполняет задачу.
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public static function doWork(string $task_key, array $job):void {

		// если включены уведомления в главный чат
		if (Domain_Company_Action_GetGeneralChatNotificationSettings::do() === 1) {

			// отправляем статистику
			Domain_Rating_Action_SendRatingForLastWeek::do($job["year"], $job["week"]);
		}

		Domain_Company_Action_Config_Set::do(["need_work" => $job["next_time"]], $task_key);
	}
}
