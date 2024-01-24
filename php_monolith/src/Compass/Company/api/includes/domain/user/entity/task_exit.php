<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс для взаимодействия с задачей на увольнение
 */
class Domain_User_Entity_TaskExit {

	public const STATUS_IN_PROGRESS = 0; // статус задачи - в процессе
	public const STATUS_DONE        = 1; // статус задачи - выполнена

	/**
	 * Выполним задачу по удалении пользователя
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function run(Struct_Db_CompanyData_ExitList $task):bool {

		// получим шаги очистки
		$step_list = Domain_User_Entity_TaskExitStep::FULL_EXIT_STEP_LIST;

		// если 0 шаг, то проставим что задача взята в работу
		if ($task->step == 0) {
			self::setStep($task, Domain_User_Entity_TaskExitStep::FIRST_CLEAR_STEP);
		}

		// пройдемся по всем шагам и получим обновленный таск
		foreach ($step_list as $module_need_accept => $steps) {
			$task = self::_doStepClear($module_need_accept, $steps, $task);
		}

		// если шаги пришли к проверке то получим шаги проверки
		if ($task->step >= Domain_User_Entity_TaskExitStep::FIRST_CHECK_STEP) {

			$step_list_check = Domain_User_Entity_TaskExitStep::STEPS_CHECKER;

			// пройдеся по всем шагам и получим обновленный таск
			foreach ($step_list_check as $module_need_accept_check => $step_check) {
				$task = self::_doStepCheck($module_need_accept_check, $step_check, $task);
			}
		}

		// если задача выполнена, то вернем положительный ответ
		if ($task->status == self::STATUS_DONE) {
			return true;
		}

		return false;
	}

	/**
	 * Добавляем задачу
	 *
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $exit_task_id):Struct_Db_CompanyData_ExitList {

		return Gateway_Db_CompanyData_ExitList::insert($exit_task_id,
			self::STATUS_IN_PROGRESS,
			$user_id,
			self::initExtra()
		);
	}

	/**
	 * Ставим статус задача в процессе
	 *
	 * @throws \parseException
	 */
	public static function setStatusInProgress(Struct_Db_CompanyData_ExitList $exit_task):void {

		try {

			Gateway_Db_CompanyData_ExitList::set($exit_task->exit_id, [
				"status"     => self::STATUS_IN_PROGRESS,
				"updated_at" => $exit_task->updated_at,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Dismissal task row not updated");
		}
	}

	/**
	 * Ставим статус задача выполнена
	 *
	 * @throws \parseException
	 */
	public static function setStatusDone(Struct_Db_CompanyData_ExitList $exit_task):void {

		try {

			Gateway_Db_CompanyData_ExitList::set($exit_task->exit_id, [
				"status"     => self::STATUS_DONE,
				"updated_at" => $exit_task->updated_at,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Dismissal task row not updated");
		}
	}

	/**
	 * Ставим шаг
	 *
	 * @throws \parseException
	 */
	public static function setStep(Struct_Db_CompanyData_ExitList $exit_task, int $step):void {

		try {

			Gateway_Db_CompanyData_ExitList::set($exit_task->exit_id, [
				"step"       => $step,
				"updated_at" => time(),
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Dismissal task row not updated");
		}
	}

	/**
	 * Обновим extra
	 *
	 * @throws \parseException
	 */
	public static function setExtra(Struct_Db_CompanyData_ExitList $exit_task, array $extra):void {

		try {

			Gateway_Db_CompanyData_ExitList::set($exit_task->exit_id, [
				"extra"      => $extra,
				"updated_at" => time(),
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Dismissal task row not updated");
		}
	}

	/**
	 * Получаем заявку по ее id
	 *
	 * @throws cs_DismissalTaskNotExist
	 */
	public static function get(int $exit_task_id):Struct_Db_CompanyData_ExitList {

		try {
			$exit_task = Gateway_Db_CompanyData_ExitList::getOne($exit_task_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_DismissalTaskNotExist();
		}

		return $exit_task;
	}

	/**
	 * Получаем заявку с блокировкой
	 *
	 * @throws cs_HireRequestNotExist
	 */
	public static function getForUpdate(int $exit_request_id):Struct_Db_CompanyData_ExitList {

		try {
			$exit_request = Gateway_Db_CompanyData_ExitList::getForUpdate($exit_request_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_HireRequestNotExist();
		}

		return $exit_request;
	}

	/**
	 * получаем все записи по статусу
	 *
	 * @return Struct_Db_CompanyData_ExitList[]
	 */
	public static function getAllByStatus(int $status):array {

		return Gateway_Db_CompanyData_ExitList::getAllByStatus($status);
	}

	/**
	 * получаем флаг, находится ли пользователь на этапе увольнения
	 */
	public static function isExitStatusInProgress(int $user_id):bool {

		// пробуем получить действующие таски на увольнение
		$exit_task_list = self::getAllByStatus(self::STATUS_IN_PROGRESS);

		// если тасков увольнения нет, значит пользователь не увольняется из компании на текущий момент
		if (count($exit_task_list) < 1) {
			return false;
		}

		// получаем пользователей, которых увольняем прямо сейчас
		$user_id_list = array_column($exit_task_list, "user_id");

		// если имеется задача для нашего пользователя
		return in_array($user_id, $user_id_list);
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 2; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"check_done_list" => [],
		],
		2 => [
			"check_done_list" => [],
			"param_query"     => [],
		],
	];

	/**
	 * Создать новую структуру для extra
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * устанавливаем check_done_list
	 */
	public static function setStepDone(array $extra, int $step):array {

		$extra                                    = self::_getExtra($extra);
		$extra["extra"]["check_done_list"][$step] = 1;

		return $extra;
	}

	/**
	 * устанавливаем параметры запросов
	 */
	public static function setParamQuery(array $extra, array $data):array {

		$extra                         = self::_getExtra($extra);
		$extra["extra"]["param_query"] = $data;

		return $extra;
	}

	/**
	 * получаем check_done_list
	 */
	public static function getStepDone(array $extra):array {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["check_done_list"];
	}

	/**
	 * получаем param_query
	 */
	public static function getParamQuery(array $extra):array {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["param_query"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}

	/**
	 * Выполним шаги по очистке данных
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	protected static function _doStepClear(string $module_need_accept, array $steps, Struct_Db_CompanyData_ExitList $task):Struct_Db_CompanyData_ExitList {

		foreach ($steps as $step => $method) {

			// если нынешний шаг соответствует то выполним его
			if ($task->step == $step) {

				foreach ($method as $method_need_accept => $data) {

					$data                          = array_merge($data, self::getParamQuery($task->extra));
					$data["iteration_is_possible"] += 1;

					// закончено ли выполнение шага
					$is_complete = self::_isCompleteClearAfterUser($method_need_accept, $module_need_accept, $data, $task);

					// если закончено и он последний в списке проверок - ставим следующим шаг проверки, или просто увеличим на 1
					if ($is_complete && $task->step == Domain_User_Entity_TaskExitStep::LAST_CLEAR_STEP) {

						$extra = self::setParamQuery($task->extra, [
							"offset"                => 0,
							"limit"                 => 0,
							"iteration_is_possible" => $data["iteration_is_possible"],
						]);

						self::setExtra($task, $extra);

						self::setStep($task, Domain_User_Entity_TaskExitStep::FIRST_CHECK_STEP);
						break;
					}

					if ($is_complete) {

						$extra = self::setParamQuery($task->extra, [
							"offset"                => 0,
							"limit"                 => 0,
							"iteration_is_possible" => $data["iteration_is_possible"],
						]);
						self::setExtra($task, $extra);

						self::setStep($task, $task->step + 1);
					}

					self::_checkDoneStep($task, $data, $is_complete);
				}
			}
		}

		return $task;
	}

	/**
	 * Вызовем нужный метод очистки данных
	 *
	 * @throws paramException
	 * @throws \returnException
	 */
	protected static function _isCompleteClearAfterUser(string $method_need_accept, string $module_need_accept, array $data, Struct_Db_CompanyData_ExitList $task):bool {

		$data["user_id"] = $task->user_id;

		return match ($module_need_accept) {

			"conversation" => Gateway_Socket_Conversation::clearAfterExitUser($method_need_accept, $data, $task->user_id),
			"thread"       => Gateway_Socket_Thread::clearAfterExitUser($method_need_accept, $data, $task->user_id),
			"company"      => self::_clearAfterExitUser($method_need_accept, $task->user_id),
			default        => throw new ParamException("Incorrect method"),
		};
	}

	/**
	 * чистим после ухода пользователя
	 */
	protected static function _clearAfterExitUser(string $method_need_accept, int $user_id):bool {

		return match ($method_need_accept) {
			"clearMember" => Domain_Member_Action_ClearAfterExit::do($user_id),
			default       => true,
		};
	}

	/**
	 * Выполним шаги по проверке очистки данных
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _doStepCheck(string $module_need_accept, array $steps, Struct_Db_CompanyData_ExitList $task):Struct_Db_CompanyData_ExitList {

		foreach ($steps as $method) {

			foreach ($method as $method_need_accept => $data) {

				$data                          = array_merge($data, self::getParamQuery($task->extra));
				$data["iteration_is_possible"] -= 1;

				$is_cleared = self::_checkClearAfterUser($method_need_accept, $module_need_accept, $data, $task);

				// если количество итераций которым удаляли сделано - а диалоги не закончились, вернемся на первый шаг
				if ($data["iteration_is_possible"] <= 0 && $is_cleared === false) {

					$extra = self::setParamQuery($task->extra, []);
					self::setExtra($task, $extra);
					self::setStep($task, Domain_User_Entity_TaskExitStep::FIRST_CLEAR_STEP);
					return $task;
				}

				if ($is_cleared) {
					self::setStep($task, $task->step + 1);
				}

				// если закончено и он последний в списке проверок - ставим следующим шаг проверки, или просто увеличим на 1
				if ($is_cleared && $task->step == Domain_User_Entity_TaskExitStep::LAST_CHECK_STEP) {

					self::setStep($task, Domain_User_Entity_TaskExitStep::EXIT_FINISH_STEP);
					self::setStatusDone($task);
					$task->status = self::STATUS_DONE;
					return $task;
				}

				self::_checkDoneStep($task, $data, $is_cleared);
			}
		}

		return $task;
	}

	/**
	 * Вызовем нужный метод проверки очистки данных
	 *
	 * @throws paramException
	 * @throws \returnException
	 */
	protected static function _checkClearAfterUser(string $method_need_accept, string $module_need_accept, array $data, Struct_Db_CompanyData_ExitList $task):bool {

		$data["user_id"] = $task->user_id;

		return match ($module_need_accept) {

			"conversation" => Gateway_Socket_Conversation::checkAfterExitUser($method_need_accept, $data, $task->user_id),
			"thread"       => Gateway_Socket_Thread::clearAfterExitUser($method_need_accept, $data, $task->user_id),
			"company"      => self::_checkAfterExitUser($method_need_accept, $task->user_id),
			default        => throw new ParamException("Incorrect method"),
		};
	}

	/**
	 * проверяем данные после ухода пользователя
	 */
	protected static function _checkAfterExitUser(string $method_need_accept, int $user_id):bool {

		return match ($method_need_accept) {
			"clearMember" => Domain_Member_Action_ClearAfterExit::do($user_id),
			default       => true,
		};
	}

	/**
	 * Проверим закончился ли шаг задачи
	 *
	 * @param Struct_Db_CompanyData_ExitList $task
	 * @param array                          $data
	 * @param bool                           $is_done
	 *
	 * @return void
	 * @throws \parseException
	 */
	protected static function _checkDoneStep(Struct_Db_CompanyData_ExitList $task, array $data, bool $is_done):void {

		// если шаг не завершен - увеличим оффсет
		if ($is_done === false && isset($data["offset"])) {

			$data["offset"] += $data["limit"];
			$extra          = self::setParamQuery($task->extra, $data);

			self::setExtra($task, $extra);
		}
	}
}
