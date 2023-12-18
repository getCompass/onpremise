<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии карточки пользователя в компании
 *
 * Class Domain_Member_Scenario_Api
 */
class Domain_EmployeeCard_Scenario_Api {

	// получаем профиль пользователя
	// @long
	public static function getProfile(int $executor_user_id, int $need_user_id):array {

		// проверяем user_id на корректность
		Domain_User_Entity_Validator::assertValidUserId($need_user_id);

		$member_info = Gateway_Bus_CompanyCache::getMember($need_user_id);

		// если это бот
		if (Type_User_Main::isBot($member_info->npc_type)) {
			throw new ParamException("you can't do this action on bot-user");
		}

		// получаем запись dynamic-данных карточки пользователя
		$dynamic_obj = Type_User_Card_DynamicData::get($need_user_id);

		// получаем запись редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($need_user_id);

		// получаем необходимые записи чатов для отображения карточки
		[$single_conversation, $heroes_conversation] = Gateway_Socket_Conversation::getConversationCardList($executor_user_id, $need_user_id);

		// получаем значения плана по требовательности/респекту на текущий месяц
		$month_plan_list = Type_User_Card_MonthPlan::getAllType($need_user_id, monthStart());

		// разбиваем каждое из значений по отдельности для удобства форматирования
		$plan_respect_data      = Type_User_Card_MonthPlan::extractPlanByType($month_plan_list, Type_User_Card_MonthPlan::MONTH_PLAN_RESPECT_TYPE);
		$plan_exactingness_data = Type_User_Card_MonthPlan::extractPlanByType($month_plan_list, Type_User_Card_MonthPlan::MONTH_PLAN_EXACTINGNESS_TYPE);
		$month_plan_list        = [
			"respect"      => $plan_respect_data,
			"exactingness" => $plan_exactingness_data,
		];

		// получаем среднее время за последние N недель
		$avg_worked_hours = self::_getAvgForLastWeeks($need_user_id, $member_info->created_at);

		// получаем рейтинг пользователя за текущую неделю
		$user_rating_list = Domain_Rating_Action_GetByUserId::do($need_user_id, (int) date("o"), (int) date("W"), (int) date("W"), $member_info);
		$user_rating      = end($user_rating_list);

		// загружаем количество участников компании, требуется для определения последней позиции в рейтинге
		$member_count = Domain_User_Action_Config_GetMemberCount::do();

		// собираем и подготавливаем данные для карточки
		$prepared_employee_card = self::_preparedEmployeeCard(
			$executor_user_id,
			$member_info,
			$editors_obj,
			$dynamic_obj,
			$heroes_conversation["conversation_key"],
			$month_plan_list,
			$avg_worked_hours,
			$single_conversation
		);

		return [$prepared_employee_card, $user_rating, $member_info, $member_count];
	}

	/**
	 * получаем среднее время за последние N недель
	 *
	 * @long - много вычислений добавлено, участвует много переменных и много условий для расчета
	 */
	protected static function _getAvgForLastWeeks(int $user_id, int $join_company_at):float {

		$current_time = time();

		// получаем итемы фиксации рабочих часов за последние N недель
		$day_start_at          = dayStart($current_time) - DAY7 * Type_User_Card_WorkedHours::LAST_WEEKS_COUNT_FOR_AVG_HOURS;
		$worked_hours_obj_list = Type_User_Card_WorkedHours::getListByDayStartAt(
			$user_id, $day_start_at, 7 * Type_User_Card_WorkedHours::LAST_WEEKS_COUNT_FOR_AVG_HOURS
		);

		$earliest_day_value = 0;
		$is_exist_today     = false;

		// получаем среднее время за последние N недель
		$value_for_last_weeks   = 0;
		$worked_hours_obj_count = 0;
		foreach ($worked_hours_obj_list as $v) {

			// если фиксация появилась до того момента как мы вступили в компанию, то ее не нужно учитывать
			if ($v->day_start_at < dayStart($join_company_at)) {
				continue;
			}

			// если фиксация в первый же день N недель назад
			if ($v->day_start_at == $day_start_at) {
				$earliest_day_value = $v->float_value;
			}

			// отмечаем что сегодня была фиксация
			if ($v->day_start_at == dayStart($current_time)) {
				$is_exist_today = true;
			}

			// собираем общее значения за последние N недель
			$value_for_last_weeks += $v->float_value;
			$worked_hours_obj_count++;
		}

		// если сегодня была фиксация, то не учитываем первый же день N недель назад
		if ($is_exist_today) {
			$value_for_last_weeks -= $earliest_day_value;
		}

		// получаем переменную для формулы высчитывания среднего времени рабочих часов
		$variable = Type_User_Card_WorkedHours::getVariableForAvgWorkedHours($worked_hours_obj_count, $current_time, $join_company_at);

		return $value_for_last_weeks / $variable;
	}

	/**
	 * Собираем и подготавливаем данные для карточки
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_DatesWrongOrder
	 * @throws \queryException
	 * @long - много данных используется
	 */
	protected static function _preparedEmployeeCard(int                                  $this_user_id,
									\CompassApp\Domain\Member\Struct\Main $user_info,
									Struct_Domain_Usercard_EditorList    $editors_obj,
									Struct_Domain_Usercard_Dynamic       $dynamic_obj,
									string                               $heroes_conversation_key,
									array                                $month_plan_list,
									float                                $avg_worked_hours,
									array                                $single_conversation):array {

		// достаем информацию с какого времени сотрудник работает в компании
		$join_company_at = $user_info->company_joined_at;

		// получаем из конфига компании время ее создания
		$company_created_at_config = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::COMPANY_CREATED_AT);
		$company_created_at        = $company_created_at_config["value"];

		// получаем количество редакторов для выбранного пользователя и флаг, можем ли мы менять карточку пользователя
		$admin_list = Domain_User_Action_Member_GetByPermissions::do(
			[\CompassApp\Domain\Member\Entity\Permission::MEMBER_PROFILE_EDIT]);

		$admin_id_list         = array_map(function(\CompassApp\Domain\Member\Struct\Main $member) {

			return $member->user_id;
		}, $admin_list);
		$editor_id_list        = Type_User_Card_EditorList::getAllUserEditorIdListFromEditorListObj($editors_obj);
		$all_editors_id_list   = array_unique(array_merge($editor_id_list, $admin_id_list));
		$editors_count         = count($all_editors_id_list);
		$is_me_can_change_card = in_array($this_user_id, $all_editors_id_list);

		// получаем время когда деактивировали его аккаунт, либо 0
		$disable_company_at = $user_info->left_at;
		$is_account_deleted = \CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra) ? 1 : 0;

		// определяем был ли уволен сотрудник, если да, то целевая дата - дата увольнения, иначе текущая
		$target_timestamp = $disable_company_at > 0 ? $disable_company_at : time();

		// получаем итоговое отношение времени работы в компании к годам.
		$total_worked_time = Domain_Member_Entity_EmployeeCard::calculateTotalWorkedTime($join_company_at, $target_timestamp);

		// получаем общую вовлеченность пользователя
		$loyalty_count        = Type_User_Card_DynamicData::getLoyaltyCount($dynamic_obj->data);
		$loyalty_value_data   = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($dynamic_obj->data);
		$loyalty_dynamic_data = Type_User_Card_Loyalty::getDynamicData($loyalty_value_data, $loyalty_count);
		$loyalty              = $loyalty_dynamic_data["value"];

		// получаем процент успешных спринтов пользователя
		$sprint_success_percent = 0;
		$total_sprint_count     = Type_User_Card_DynamicData::getSprintTotalCount($dynamic_obj->data);
		$success_sprint_count   = Type_User_Card_DynamicData::getSprintSuccessCount($dynamic_obj->data);
		if ($total_sprint_count > 0) {
			$sprint_success_percent = floor(100 / $total_sprint_count * $success_sprint_count);
		}

		[$avg_screen_time, $total_action_count, $avg_message_answer_time] = self::_getUserActivityData($user_info);

		return [
			"avg_worked_hours"    => round($avg_worked_hours, 1),
			"user_data"           => [
				"join_company_at"         => $join_company_at,
				"disable_company_at"      => $disable_company_at,
				"account_deleted_at"      => \CompassApp\Domain\Member\Entity\Extra::getAliasDisabledAt($user_info->extra),
				"is_me_can_change_card"   => $is_me_can_change_card,
				"is_account_deleted"      => $is_account_deleted,
				"avg_screen_time"         => $avg_screen_time,
				"total_action_count"      => $total_action_count,
				"avg_message_answer_time" => $avg_message_answer_time,
			],
			"company_created_at"  => $company_created_at,
			"total_worked_time"   => $total_worked_time,
			"respect_count"       => Type_User_Card_DynamicData::getRespectCount($dynamic_obj->data),
			"achievement_count"   => Type_User_Card_DynamicData::getAchievementCount($dynamic_obj->data),
			"sprint_info"         => [
				"total"           => $total_sprint_count,
				"success"         => $success_sprint_count,
				"success_percent" => $sprint_success_percent,
			],
			"loyalty"             => $loyalty,
			"editor_count"        => $editors_count,
			"conversation_key"    => $heroes_conversation_key,
			"plan_gave_data"      => [
				"respect"      => [
					"plan"    => $month_plan_list["respect"]["plan_value"] ?? 0,
					"current" => $month_plan_list["respect"]["user_value"] ?? 0,
				],
				"exactingness" => [
					"plan"    => $month_plan_list["exactingness"]["plan_value"] ?? 0,
					"current" => $month_plan_list["exactingness"]["user_value"] ?? 0,
				],
			],
			"role"                => $user_info->role,
			"permission_list"     => \CompassApp\Domain\Member\Entity\Permission::getPermissionList($user_info->permissions),
			"single_conversation" => [
				"conversation_key" => $single_conversation["conversation_key"],
				"is_muted"         => $single_conversation["is_muted"],
			],
		];
	}

	/**
	 * Получаем данные по активности пользователя
	 */
	protected static function _getUserActivityData(\CompassApp\Domain\Member\Struct\Main $user_info):array {

		$avg_screen_time         = \CompassApp\Domain\Member\Entity\Extra::getAliasAvgScreenTime($user_info->extra);
		$total_action_count      = \CompassApp\Domain\Member\Entity\Extra::getAliasTotalActionCount($user_info->extra);
		$avg_message_answer_time = \CompassApp\Domain\Member\Entity\Extra::getAliasAvgMessageAnswerTime($user_info->extra);
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {

			$avg_screen_time         = 0;
			$total_action_count      = 0;
			$avg_message_answer_time = 0;
		}

		// права админа приоритетнее
		if (\CompassApp\Domain\Member\Entity\Permission::isAdministratorStatisticInfinite($user_info->role, $user_info->permissions)) {

			$avg_screen_time         = -1;
			$total_action_count      = -1;
			$avg_message_answer_time = -1;
		}

		return [$avg_screen_time, $total_action_count, $avg_message_answer_time];
	}

	/**
	 * Подготавливаем и возвращаем планы на месяц
	 * Если получаем за текущий год - проверит и создаст планы если их нет
	 *
	 * @param int $user_id
	 * @param int $year
	 * @param int $type
	 *
	 * @return array
	 */
	public static function getGaveMonthList(int $user_id, int $year, int $type):array {

		// получаем планы за переданный год
		[$month_plan_list, $months_count] = Type_User_Card_MonthPlan::getByYear($user_id, $year, $type);

		// если это текущий год проверим что есть план за текущий месяц
		if ($year == (int) date("Y")) {

			// создадим план на текущий месяц если его еще нет.
			$is_created = Type_User_Card_MonthPlan::createCurrentMonthPlanIfNotExist($type, $month_plan_list, $user_id);

			// если были созданы планы, получаем все планы за год с новыми планами
			if ($is_created) {
				[$month_plan_list, $months_count] = Type_User_Card_MonthPlan::getByYear($user_id, $year, $type);
			}
		}

		return [$month_plan_list, $months_count];
	}
}