<?php

namespace Compass\Company;

/**
 * Класс для работы с наблюдателем пользователя (User_Observer)
 */
class Type_User_Observer {

	// типы выполняемых задач
	public const JOB_TYPE_AUTO_COMMIT_WORKED_HOURS          = 1; // автофиксация рабочих часов
	public const JOB_TYPE_NOTIFY_ABOUT_WORKED_HOURS         = 2; // системное уведомление об автофиксации рабочих часов
	public const JOB_TYPE_SET_NEXT_MONTH_PLAN               = 3; // автоустановка плана на следующий месяц
	public const JOB_TYPE_NOTIFY_ABOUT_EMPLOYEE_ANNIVERSARY = 4; // системное уведомление о годовщине сотрудника в компании

	/** @var string[] список классов задач, сгруппированных по типу выполняемых задач */
	public const JOB_CLASS_LIST_BY_TYPE = [
		self::JOB_TYPE_AUTO_COMMIT_WORKED_HOURS          => Type_User_Observer_AutoCommitWorkedHours::class,
		self::JOB_TYPE_NOTIFY_ABOUT_WORKED_HOURS         => Type_User_Observer_Notify_AutoCommit::class,
		self::JOB_TYPE_SET_NEXT_MONTH_PLAN               => Type_User_Observer_SetMonthPlan::class,
		self::JOB_TYPE_NOTIFY_ABOUT_EMPLOYEE_ANNIVERSARY => Type_User_Observer_Notify_EmployeeAnniversary::class,
	];

	// -------------------------------------------------------
	// WORK OBSERVER
	// -------------------------------------------------------

	/**
	 * Главный метод обсервера для выполнения задач
	 *
	 * @param array $user_job_list_batch
	 *
	 * @return int
	 */
	public static function doWork(array $user_job_list_batch):int {

		$closest_job_at_list = [];

		foreach ($user_job_list_batch as $user_job_list) {

			$closest_job_at_list[] = self::_processUserJobList($user_job_list["user_id"], $user_job_list["data"]);
		}

		return min($closest_job_at_list);
	}

	/**
	 * Выполнить всю работу по одному пользователю
	 *
	 * @param int   $user_id
	 * @param array $observer_data
	 *
	 * @return int
	 */
	protected static function _processUserJobList(int $user_id, array $observer_data):int {

		// данные для следующего исполнения задач, сгруппированные по типу задач
		$next_jobs_data_by_type = [];

		// получаем extra, чтобы сформировать задачи для исполнения
		$job_provide_extra_data_list = self::_getJobProvideList($user_id);

		// выполняем над пользователем последовательно каждое действие из списка
		foreach ($job_provide_extra_data_list as $job_type => $extra) {

			/** @var Type_User_Observer_Default $provider - класс выполняемой задачи */
			$provider = self::JOB_CLASS_LIST_BY_TYPE[$job_type];

			// если у пользователя еще не было такой задачи
			if (!isset($observer_data[$job_type])) {

				// собираем задачи и данные для следующего исполнения и пропускаем
				$next_jobs_data_by_type[$job_type] = $provider::getNextWorkTime($extra);
				continue;
			}

			// генерируем задачи для исполнения
			$job_list = $provider::provideJobList($observer_data, $extra);

			// выполняем задачи и собираем данные для следующего исполнения этих задач
			$next_time_data_list = self::_processJobList($provider, $job_list);

			// мерджим и собираем по типу задач время следующего выполнения
			$next_jobs_data_by_type = $next_jobs_data_by_type + $next_time_data_list;
		}

		// устанавливаем для задач время следующего выполнения
		self::_setNextJobTime($user_id, $next_jobs_data_by_type);

		// возвращаем время для ближайшей задачи
		return count($next_jobs_data_by_type) > 0
			? min(array_values($next_jobs_data_by_type))
			: time() + 60 * 30;
	}

	/**
	 * Получаем extra-данные для генерации задач
	 */
	protected static function _getJobProvideList(int $user_id):array {

		// собираем extra данные для задач, сгруппированные по типу задачи
		$job_extra_list_by_type = [];

		/** @var Type_User_Observer_Default $provider - класс для выполняемой задачи */
		foreach (self::JOB_CLASS_LIST_BY_TYPE as $job_type => $provider) {

			// для каждого класса получаем extra для выполнения задачи
			$job_extra_list_by_type[$job_type] = $provider::provideJobExtra($user_id, $job_type);
		}

		return $job_extra_list_by_type;
	}

	/**
	 * Вызывает исполнение задач
	 */
	protected static function _processJobList(string $provider, array $job_list):array {

		$next_job_data_list_by_type = [];

		// для каждой полученной задачи
		foreach ($job_list as $job_type => $job) {

			// выполняем задачу
			/** @var Type_User_Observer_Default $provider - класс для выполняемой задачи */
			$next_job_data[$job_type] = $provider::doJob($job);

			// собираем данные для следующего выполнения задач
			$next_job_data_list_by_type = $next_job_data_list_by_type + $next_job_data;
		}

		return $next_job_data_list_by_type;
	}

	/**
	 * Устанавливаем следующее выполнение задачам
	 */
	protected static function _setNextJobTime(int $user_id, array $next_jobs_data):void {

		if (count($next_jobs_data) < 1) {
			return;
		}

		// получаем актуальную запись обсервера для пользователя
		$observer_row = self::getUserFromObserver($user_id);
		if (!isset($observer_row["user_id"])) {

			self::addUserForObserver($user_id);
			return;
		}

		// добавляем в нее данные следующего выполнения для собранных задач
		foreach ($next_jobs_data as $job_type => $next_work_time) {

			$observer_row["data"][$job_type] = [
				"need_work" => $next_work_time,
			];
		}

		// обновляем запись пользователя
		$set = ["data" => $observer_row["data"]];
		self::updateUserFromObserver($user_id, $set);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// метод для добавления пользователя в наблюдение к Observer'у
	public static function addUserForObserver(int $user_id):void {

		$insert = [
			"user_id"    => $user_id,
			"need_work"  => 0,
			"created_at" => time(),
			"updated_at" => 0,
			"data"       => self::getAllJobList($user_id),
		];
		Gateway_Db_CompanySystem_ObserverMember::insertOrUpdate($insert);
	}

	// получаем все задачи для исполнения обсервером
	public static function getAllJobList(int $user_id):array {

		$observer_data = [];

		/** @var Type_User_Observer_Default $provider - класс для выполняемой задачи */
		foreach (self::JOB_CLASS_LIST_BY_TYPE as $job_type => $provider) {

			// получаем extra для нужного типа задачи
			$job_extra = $provider::provideJobExtra($user_id, $job_type);

			// устанавливаем время следующего исполнения для задачи
			$observer_data[$job_type]["need_work"] = $provider::getNextWorkTime($job_extra);
		}

		return $observer_data;
	}

	// метод для удаления пользователя из наблюдения Observer'а
	public static function removeUserFromObserver(int $user_id):void {

		Gateway_Db_CompanySystem_ObserverMember::delete($user_id);
	}

	// метод для получения записи пользователя
	public static function getUserFromObserver(int $user_id):array {

		return Gateway_Db_CompanySystem_ObserverMember::get($user_id);
	}

	// метод для обновления записи пользователя
	public static function updateUserFromObserver(int $user_id, array $set):void {

		if (!isset($set["updated_at"])) {
			$set["updated_at"] = time();
		}

		Gateway_Db_CompanySystem_ObserverMember::set($user_id, $set);
	}
}