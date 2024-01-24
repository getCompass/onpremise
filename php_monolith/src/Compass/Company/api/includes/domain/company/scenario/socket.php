<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии компании для Socket API
 */
class Domain_Company_Scenario_Socket {

	/**
	 * Действия при создании компании
	 *
	 * @param int    $creator_user_id
	 * @param string $company_name
	 * @param int    $created_at
	 * @param array  $default_file_key_list
	 * @param array  $bot_list
	 * @param int    $hibernation_immunity_till
	 * @param int    $is_enabled_employee_card
	 * @param string $creator_locale
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function actionsOnCreateCompany(int $creator_user_id, string $company_name, int $created_at, array $default_file_key_list, array $bot_list,
								    int $hibernation_immunity_till, int $is_enabled_employee_card, string $creator_locale):void {

		Domain_Company_Entity_Dynamic::set(Domain_Company_Entity_Dynamic::HIBERNATION_IMMUNITY_TILL, $hibernation_immunity_till);

		// создаем дефолтные группы для компании
		$default_file_key_list = new Struct_File_Default(
			$default_file_key_list["hiring_conversation_avatar_file_key"],
			$default_file_key_list["notes_conversation_avatar_file_key"],
			$default_file_key_list["support_conversation_avatar_file_key"],
			$default_file_key_list["respect_conversation_avatar_file_key"]
		);
		Gateway_Socket_Conversation::createDefaultGroups($creator_user_id, $default_file_key_list, $creator_locale);

		// устанавливаем имя компании в конфиге компании
		$value = ["value" => $company_name];
		Domain_Company_Action_Config_Set::do($value, Domain_Company_Entity_Config::COMPANY_NAME);

		// устанавливаем время, когда создана компания
		$value = ["value" => $created_at];
		Domain_Company_Action_Config_Set::do($value, Domain_Company_Entity_Config::COMPANY_CREATED_AT);

		// устанавливаем все дефолтные значения конфига
		foreach (Domain_Company_Entity_Config::CONFIG_ON_CREATE_VALUE_LIST as $key => $default_value) {

			$value = ["value" => $default_value];
			Domain_Company_Action_Config_Set::do($value, $key);
		}

		if ($is_enabled_employee_card) {

			Domain_Company_Entity_Config::set(Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY, $is_enabled_employee_card);
			Domain_Company_Action_SendExtendedCardEvent::do($creator_user_id, $is_enabled_employee_card);
		}

		// добавляем ботов
		foreach ($bot_list as $bot_info) {

			Domain_User_Action_AddBot::do(
				$bot_info["user_id"],
				$bot_info["npc_type"],
				"",
				$bot_info["full_name"],
				$bot_info["avatar_file_key"],
				""
			);
		}
	}

	/**
	 * Возвращаем список активных сотрудников за день
	 *
	 * @return int[]
	 */
	public static function getListOfActiveMembersByDay(int $year, int $day_num):array {

		return Domain_Company_Action_GetActiveMembers::do($year, $day_num);
	}

	/**
	 * Проверяем существование текущей компании
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function checkIfExistCurrent():bool {

		return Gateway_Socket_Pivot::checkCompanyExists();
	}

	/**
	 * Сценарий настройки карточки (базовая/расширенная)
	 *
	 * @param int $user_id
	 * @param int $is_enabled
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setExtendedEmployeeCard(int $user_id, int $is_enabled):void {

		Domain_Company_Entity_Config::set(Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY, $is_enabled);
		Domain_Company_Action_SendExtendedCardEvent::do($user_id, $is_enabled);
	}

	/**
	 * Выполним задачу в зависимости от типа
	 *
	 * @param int $task_id
	 * @param int $type
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ParamException
	 * @throws \cs_RowIsEmpty
	 */
	public static function doTask(int $task_id, int $type):bool {

		return Domain_Company_Action_Task::do($task_id, $type);
	}

	/**
	 * Сценарий удаления компании
	 *
	 * @param int $user_id
	 * @param int $deleted_at
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function delete(int $user_id, int $deleted_at):void {

		$user = Gateway_Bus_CompanyCache::getMember($user_id);

		// проверяем, что пользователь собственник компании
		\CompassApp\Domain\Member\Entity\Permission::assertCanDeleteSpace($user->role, $user->permissions);

		// помечаем компанию как удаленную и разлогиниваем всех
		Domain_Company_Action_Delete::do($deleted_at);

		// публикуем анонс, что компания удалена
		$raw_data = \Service\AnnouncementTemplate\TemplateService::createOfType(
			\Service\AnnouncementTemplate\AnnouncementType::COMPANY_WAS_DELETED, ["deleted_at" => $deleted_at]);

		Domain_Announcement_Action_Publish::run($raw_data, COMPANY_ID, [], [$user_id]);
	}

	/**
	 * Получить информацию по статистике в пространстве
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function getEventCountInfo():array {

		$space_created_at = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::COMPANY_CREATED_AT)["value"];

		$first_year   = (int) date("Y", $space_created_at);
		$current_year = (int) date("Y");

		$total_event_count         = 0;
		$previous_week_event_count = 0;
		$current_week_event_count  = 0;
		$current_week_num          = (int) (new \DateTime())->format("W");
		$previous_week_num         = $current_week_num === 1 ? self::_getLastWeekOfYear($current_year - 1) : $current_week_num - 1;

		for ($year = $first_year; $year <= $current_year; $year++) {

			// получаем железобетонно первую и последнюю неделю месяца
			$start_week = 1;
			$end_week   = self::_getLastWeekOfYear($year);

			$event_count_list =
				Domain_Rating_Action_GetEventCountByInterval::do($year, $start_week, $end_week, Domain_Rating_Entity_Rating::GENERAL);

			// считаем количество всех действий в команде
			$total_event_count += array_reduce($event_count_list,
				static fn(int $total_event_count, Struct_Bus_Rating_EventCount $event_count) => $total_event_count + $event_count->count,
				0);

			// для текущего года считаем текущую и прошедшую недели
			if ($year === $current_year) {

				$current_year_event_count_list = array_column($event_count_list, "count", "week");
				$previous_week_event_count     = $current_year_event_count_list[$previous_week_num] ?? 0;
				$current_week_event_count      = $current_year_event_count_list[$current_week_num] ?? 0;
			}
		}

		return [$total_event_count, $previous_week_event_count, $current_week_event_count];
	}

	/**
	 * Получить последнюю неделю года
	 *
	 * @param int $year
	 *
	 * @return int
	 */
	protected static function _getLastWeekOfYear(int $year):int {

		$date = new \DateTime();
		$date->setISODate($year, 53);
		return ($date->format("W") === "53" ? 53 : 52);
	}
}