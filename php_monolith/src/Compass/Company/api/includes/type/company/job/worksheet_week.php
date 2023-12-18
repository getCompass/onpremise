<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс, описывающий задачу по рассылке рейтинга рабочих часов за неделю.
 */
class Type_Company_Job_WorksheetWeek extends Type_Company_Job_Default {

	/** @var string ключ для хранения параметров задачи */
	protected const _SYSTEM_DATASTORE_KEY = "worksheet_week_job";

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	public static function provideJobExtra():array {

		// конец периода для выборки — начало текущей недели
		$period_end_date = strtotime("monday this week");

		return [
			"action_time"       => time(),                            // время совершения действия
			"next_time"         => strtotime("next Monday 10 hours"), // когда нужно исполнить действие в следующий раз
			"period_start_date" => $period_end_date - DAY7,           // дата начала периода
			"period_end_date"   => $period_end_date - HOUR12,         // дата окончания периода
		];
	}

	/**
	 * Генерирует задачи, которые нужно взять на исполнение.
	 * Для наала выбирает все доступные компании и по ним генерирует задачи на отправку рейтинг рабочих часов.
	 *
	 * @param array $extra экстра для генерации таска
	 *
	 * @throws \parseException
	 */
	public static function provideJobList(array $extra):array {

		// список задач на исполнение
		$job_list = [];

		// получаем информацию по задаче
		$job = self::_getJobData($extra);

		// если задача получена, то добавляем ее в список на исполнение
		if (count($job) > 0) {
			$job_list[self::_SYSTEM_DATASTORE_KEY] = $job;
		}

		return $job_list;
	}

	/**
	 * Исполняет задачу.
	 *
	 * @param string $task_key
	 * @param array  $job
	 *
	 * @throws \busException
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

			// триггерим рейтинг
			Domain_EmployeeCard_Action_SendWorksheetRating::do($job["period_start_date"], $job["period_end_date"]);
		}

		// устанавливаем время следующей итерации
		self::_setTaskData($task_key, $job["next_time"]);
	}

	# region protected

	/**
	 * Возвращает данные для задачи для указанной компании.
	 * Возвращает пустой массив, если задача для компании в данный момент не должна исполняться.
	 *
	 * @throws \parseException
	 */
	protected static function _getJobData(array $extra):array {

		// получаем информацию по задаче
		$task_data = Domain_Company_Action_Config_Get::do(self::_SYSTEM_DATASTORE_KEY);

		// задачи не существует
		if (!isset($task_data["need_work"])) {

			// добавляем новую задачу, для отдельно взятой компании это произойдет один раз
			self::_setTaskData(self::_SYSTEM_DATASTORE_KEY, $extra["next_time"]);
			return [];
		}

		// если время исполнения еще не наступило, то дальше ничего не делаем
		if ($task_data["need_work"] > $extra["action_time"]) {
			return [];
		}

		// формируем данные для задачи
		return [
			"period_start_date" => $extra["period_start_date"],
			"period_end_date"   => $extra["period_end_date"],
			"next_time"         => $extra["next_time"],
		];
	}

	/**
	 * Сохраняет данные о следующей итерации работы.
	 *
	 * @throws \parseException
	 */
	protected static function _setTaskData(string $key, int $need_work):array {

		// создаем данные для новой задачи
		$data = [
			"need_work" => $need_work,
		];

		// сохраняем задачу
		Domain_Company_Action_Config_Set::do($data, $key);

		return $data;
	}

	# endregion protected
}
