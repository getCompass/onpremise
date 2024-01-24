<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Server\ServerProvider;

/**
 * Дефолтный класс для задач крона отправки статистики по компании
 */
class Type_Company_Job_RatingMonth extends Type_Company_Job_Default {

	/** @var string ключ для хранения параметров задачи */
	protected const _STATISTIC_SENDER_KEY = "rating_month_job";

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	public static function provideJobExtra():array {

		return [
			"action_time" => time(),                                     // время совершения действия
			"next_time"   => strtotime("first day of next month 14:00"), // когда нужно исполнить действие в следующий раз
		];
	}

	/**
	 * Генерирует задачи, которые нужно взять на исполнение.
	 *
	 * @param array    $extra                    экстра для генерации таска
	 * @param int|null $test_month_first_data_ay значение начала месяца для тестов
	 *
	 * @throws \parseException
	 */
	public static function provideJobList(array $extra, int $test_month_first_data_ay = null):array {

		$datastore_row = Domain_Company_Action_Config_Get::do(self::_STATISTIC_SENDER_KEY);

		// если еще не было понедельников то ставим в работу
		if (!isset($datastore_row["need_work"])) {

			Domain_Company_Action_Config_Set::do(["need_work" => $extra["next_time"]], self::_STATISTIC_SENDER_KEY);
			return [];
		}

		// если время не пришло просто выходим
		if ($datastore_row["need_work"] > $extra["action_time"]) {
			return [];
		}

		// получаем время первого дня предыдущего месяца
		$month_first_day_time_at = strtotime("first day of last month 10:00");

		// если для тестов указано иное значение
		if (ServerProvider::isTest() && !is_null($test_month_first_data_ay)) {
			$month_first_day_time_at = $test_month_first_data_ay;
		}

		// генерируем данные задачи
		$task = [
			"year"      => intval(date("Y", $month_first_day_time_at)),
			"month"     => intval(date("n", $month_first_day_time_at)),
			"next_time" => intval($extra["next_time"]),
		];

		// возвращаем массив с одной задачей
		return [
			self::_STATISTIC_SENDER_KEY => $task,
		];
	}

	/**
	 * Исполняет задачу.
	 *
	 * @param string $task_key
	 * @param array  $job
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function doWork(string $task_key, array $job):void {

		$config_list = Domain_Company_Entity_Config::getList([
			Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY,
			Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS,
		]);

		// если включена расширенная карточка и включены уведомления в главный чат
		if ($config_list[Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY]["value"] === 1
			&& $config_list[Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS]["value"] === 1) {

			// отправляем статистику
			Domain_Rating_Action_SendRatingForLastMonth::do($job["year"], $job["month"]);
		}

		// устанавливаем время для следующего выполнения
		Domain_Company_Action_Config_Set::do(["need_work" => $job["next_time"]], $task_key);
	}
}
