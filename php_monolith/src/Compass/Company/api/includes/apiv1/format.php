<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Класс для форматирование сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv1_Format {

	// массив для преобразования внутреннего типа во внешний
	protected const _USER_CARD_LOYALTY_VALUE_TYPE_NAME_ALIAS = [
		Type_User_Card_Loyalty::SPORT_VALUE_TYPE      => "sport",
		Type_User_Card_Loyalty::REACTION_VALUE_TYPE   => "reaction",
		Type_User_Card_Loyalty::DEPARTMENT_VALUE_TYPE => "department_life",
	];

	// массив для преобразования внутреннего типа пользователя во внешний
	public const USER_TYPE_SCHEMA = [
		Type_User_Main::HUMAN       => "user",
		Type_User_Main::SYSTEM_BOT  => "system_bot",
		Type_User_Main::SUPPORT_BOT => "support_bot",
		Type_User_Main::OUTER_BOT   => "bot",
		Type_User_Main::USER_BOT    => "userbot",
	];

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * общий рейтинг
	 */
	public static function rating(Struct_Bus_Rating_General $rating):array {

		$formatted_top_list = [];
		foreach ($rating->top_list as $v) {

			$formatted_top_list[] = (object) [
				"user_id"     => (int) $v->user_id,
				"position"    => (int) $v->position,
				"count"       => (int) $v->count,
				"is_disabled" => (int) $v->is_disabled,
			];
		}

		return [
			"year"       => (int) $rating->year,
			"week"       => (int) $rating->week,
			"month"      => (int) $rating->month,
			"count"      => (int) $rating->count,
			"updated_at" => (int) $rating->updated_at,
			"top_list"   => (array) $formatted_top_list,
			"has_next"   => (int) $rating->has_next,
		];
	}

	/**
	 * количество ивентов
	 */
	public static function eventCount(Struct_Bus_Rating_EventCount $event_count):array {

		return [
			"year"  => (int) $event_count->year,
			"week"  => (int) $event_count->week,
			"count" => (int) $event_count->count,
		];
	}

	/**
	 * Пользовательский рейтинг
	 */
	public static function userRating(Struct_Bus_Rating_User $user_rating, int $join_company_at, int $user_list_count):array {

		$event_count_list = [];
		foreach ($user_rating->event_count_list as $event_name => $count) {
			$event_count_list[(string) $event_name] = (int) $count;
		}

		$output = [
			"user_id"          => (int) $user_rating->user_id,
			"general_position" => (int) $user_rating->general_position,
			"year"             => (int) $user_rating->year,
			"week"             => (int) $user_rating->week,
			"general_count"    => (int) $user_rating->general_count,
			"updated_at"       => (int) $user_rating->updated_at,
			"join_company_at"  => $join_company_at,
			"event_count_list" => (object) $event_count_list,
		];

		// если у пользователя пустая неделя, ставим егу последнюю позицию в рейтинге
		if ($output["general_position"] == 0) {
			$output["general_position"] = $user_list_count;
		}

		return $output;
	}

	/**
	 * Форматируем данные о списке пользователе
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main[] $member_list
	 */
	public static function memberList(int $user_id, int $user_role, array $member_list):array {

		$output = [];
		foreach ($member_list as $member) {

			$can_get_permissions = $user_role === Member::ROLE_ADMINISTRATOR || $user_id === $member->user_id;
			$output[]            = Member::formatMember($member, $can_get_permissions);
		}

		return $output;
	}

	/**
	 * Форматируем данные о списке пользователей, сгрупированном по ролям
	 */
	public static function memberRoleList(array $member_list, bool $owner_pretenders = false):array {

		$output = [];
		if ($owner_pretenders) {
			$output["owner_pretender_list"] = [];
		}

		foreach (\CompassApp\Domain\Member\Entity\Member::USER_ROLE_LIST_NAMES_V1 as $role_name) {
			$output[$role_name] = [];
		}

		foreach ($member_list as $member) {
			$output[\CompassApp\Domain\Member\Entity\Member::USER_ROLE_LIST_NAMES_V1[$member->role]][] = (int) $member->user_id;
		}

		return $output;
	}

	/**
	 * Форматирует достижение пользователя для передачи клиенту
	 */
	public static function achievement(Struct_Domain_Usercard_Achievement $achievement):array {

		return [
			"achievement_id"  => (int) $achievement->achievement_id,
			"creator_user_id" => (int) $achievement->creator_user_id,
			"created_at"      => (int) $achievement->created_at,
			"updated_at"      => (int) $achievement->updated_at,
			"header"          => (string) $achievement->header_text,
			"text"            => (string) $achievement->description_text,
			"link_list"       => (array) Type_User_Card_Achievement::getLinkList($achievement->data),
		];
	}

	/**
	 * Форматирует данные плана на месяц пользователя для передачи клиенту
	 */
	public static function monthPlanDataItem(Struct_Domain_Usercard_MonthPlan $plan_obj):array {

		return [
			"row_id"        => (int) $plan_obj->row_id,
			"month"         => (int) date("m", $plan_obj->created_at), // отдаем номер месяца
			"plan_value"    => (int) $plan_obj->plan_value,
			"current_value" => (int) $plan_obj->user_value,
		];
	}

	/**
	 * Форматирует вовлеченность пользователя для передачи клиенту
	 */
	public static function loyalty(Struct_Domain_Usercard_Loyalty $loyalty_obj, int $avg_value, array $value_by_category_type, array $link_list = []):array {

		return [
			"loyalty_id"      => (int) $loyalty_obj->loyalty_id,
			"creator_user_id" => (int) $loyalty_obj->creator_user_id,
			"created_at"      => (int) $loyalty_obj->created_at,
			"updated_at"      => (int) $loyalty_obj->updated_at,
			"comment_text"    => (string) $loyalty_obj->comment_text,
			"link_list"       => (array) $link_list,
			"value"           => (int) $avg_value,
			"category_list"   => (array) self::_getLoyaltyCategoryList($value_by_category_type),
		];
	}

	/**
	 * получаем список категория для оценки вовлеченности
	 */
	protected static function _getLoyaltyCategoryList(array $value_by_category_type):array {

		$output = [];
		foreach ($value_by_category_type as $key => $value) {

			$output[] = [
				"name"  => (string) self::_USER_CARD_LOYALTY_VALUE_TYPE_NAME_ALIAS[$key],
				"value" => (int) $value,
			];
		}
		return $output;
	}

	/**
	 * Форматирует респект пользователя для передачи клиенту
	 */
	public static function respect(Struct_Domain_Usercard_Respect $respect):array {

		return [
			"respect_id"        => (int) $respect->respect_id,
			"user_id"           => (int) $respect->user_id,
			"creator_user_id"   => (int) $respect->creator_user_id,
			"allow_edit_till"   => (int) $respect->created_at + Type_User_Card_Respect::ALLOW_TO_EDIT_TIME,
			"allow_delete_till" => (int) $respect->created_at + Type_User_Card_Respect::ALLOW_TO_DELETE_TIME,
			"created_at"        => (int) $respect->created_at,
			"updated_at"        => (int) $respect->updated_at,
			"text"              => (string) $respect->respect_text,
			"link_list"         => (array) Type_User_Card_Respect::getLinkList($respect->data),
		];
	}

	/**
	 * Форматирует спринт пользователя для передачи клиенту
	 */
	public static function sprint(Struct_Domain_Usercard_Sprint $sprint):array {

		// переводим timestamp в iso8601 string
		$date = new \DateTime();
		$date->setTimestamp($sprint->end_at);
		$end_at_string = $date->format("Y-m-d");

		return [
			"sprint_id"       => (int) $sprint->sprint_id,
			"user_id"         => (int) $sprint->user_id,
			"creator_user_id" => (int) $sprint->creator_user_id,
			"is_success"      => (int) $sprint->is_success,
			"end_at_string"   => (string) $end_at_string,
			"created_at"      => (int) $sprint->created_at,
			"updated_at"      => (int) $sprint->updated_at,
			"header"          => (string) $sprint->header_text,
			"description"     => (string) $sprint->description_text,
			"link_list"       => (array) Type_User_Card_Sprint::getLinkList($sprint->data),
		];
	}

	/**
	 * Форматирует итем фиксации рабочего времени
	 */
	public static function workedHoursItem(Struct_Domain_Usercard_WorkedHours $worked_hours_obj):array {

		// получаем статус в зависимости от того сколько отработал сотрудника
		$condition = "normal";
		if ($worked_hours_obj->float_value > Type_User_Card_WorkedHours::WORKED_HOURS_FOR_DAY_STANDARD) {
			$condition = "extra";
		}
		if ($worked_hours_obj->float_value < Type_User_Card_WorkedHours::WORKED_HOURS_FOR_DAY_STANDARD) {
			$condition = "lack";
		}

		return [
			"worked_hours_id" => (int) $worked_hours_obj->worked_hour_id,
			"value"           => (float) round($worked_hours_obj->float_value, 1),
			"created_at"      => (int) $worked_hours_obj->created_at,
			"user_id"         => (int) $worked_hours_obj->user_id,
			"condition"       => (string) $condition,
		];
	}

	/**
	 * Форматируем рабочую неделю для календаря
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $week_worked_hours_object_list
	 */
	public static function workedWeek(int $week_number, int $year, array $week_worked_hours_object_list):array {

		// получаем даты начала и конца недели
		$week_date = Type_User_Card_WorkedHours::getStartedAtAndEndAt($week_number, $year);

		$day_list         = [];
		$total_hours_week = 0;
		foreach ($week_worked_hours_object_list as $week_worked_hours_object) {

			$day_list[] = [
				"day_in_week" => (int) date("N", $week_worked_hours_object->day_start_at),
				"value"       => (float) round($week_worked_hours_object->float_value, 1),
			];

			// считаем общее время за неделю
			$total_hours_week += $week_worked_hours_object->float_value;
		}

		return [
			"week_number"       => (int) $week_number,
			"total_week"        => (float) round($total_hours_week, 1),
			"started_at_string" => (string) $week_date["week_started_at_string"],
			"end_at_string"     => (string) $week_date["week_end_at_string"],
			"day_list"          => (array) $day_list,
		];
	}

	/**
	 * Форматирует карточку сотрудника для передачи клиенту
	 *
	 * @long большая структура возвращаемых данных
	 */
	public static function employeeCard(array $employee_card, Struct_Bus_Rating_User $user_rating, int $member_created_at, int $member_count, int $version = 1):array {

		$prepared_employee_card = [
			"avg_worked_hours"    => (float) $employee_card["avg_worked_hours"],
			"user_data"           => (object) [
				"join_company_at"         => (int) $employee_card["user_data"]["join_company_at"],
				"disable_company_at"      => (int) $employee_card["user_data"]["disable_company_at"],
				"account_deleted_at"      => (int) $employee_card["user_data"]["account_deleted_at"],
				"is_me_can_change_card"   => (int) $employee_card["user_data"]["is_me_can_change_card"],
				"is_account_deleted"      => (int) $employee_card["user_data"]["is_account_deleted"],
				"avg_screen_time"         => (int) $employee_card["user_data"]["avg_screen_time"],
				"total_action_count"      => (int) $employee_card["user_data"]["total_action_count"],
				"avg_message_answer_time" => (int) $employee_card["user_data"]["avg_message_answer_time"],

			],
			"company_created_at"  => (int) $employee_card["company_created_at"],
			"total_worked_time"   => (float) $employee_card["total_worked_time"],
			"respect_count"       => (int) $employee_card["respect_count"],
			"achievement_count"   => (int) $employee_card["achievement_count"],
			"sprint_info"         => (object) [
				"total"           => (int) $employee_card["sprint_info"]["total"],
				"success"         => (int) $employee_card["sprint_info"]["success"],
				"success_percent" => (int) $employee_card["sprint_info"]["success_percent"],
			],
			"loyalty"             => (int) $employee_card["loyalty"],
			"editor_count"        => (int) $employee_card["editor_count"],
			"conversation_key"    => (string) $employee_card["conversation_key"],
			"plan_gave_data"      => (object) [
				"respect"      => (object) [
					"plan"    => (int) $employee_card["plan_gave_data"]["respect"]["plan"],
					"current" => (int) $employee_card["plan_gave_data"]["respect"]["current"],
				],
				"exactingness" => (object) [
					"plan"    => (int) $employee_card["plan_gave_data"]["exactingness"]["plan"],
					"current" => (int) $employee_card["plan_gave_data"]["exactingness"]["current"],
				],
			],
			"role"                => (int) $employee_card["role"],
			"role_name"           => (string) \CompassApp\Domain\Member\Entity\Member::getRoleOutputType($employee_card["role"]),
			"permission_list"     => (array) self::_getFormattedPermissionList($employee_card["permission_list"]),
			"user_rating"         => (object) Apiv1_Format::userRating($user_rating, $member_created_at, $member_count),
			"single_conversation" => (object) [
				"conversation_key" => (string) $employee_card["single_conversation"]["conversation_key"],
				"is_muted"         => (int) $employee_card["single_conversation"]["is_muted"],
			],
		];

		// если вторая версия - вырезаем ненужные права с карточки и оставляем только нужные
		if ($version >= 2) {

			unset($prepared_employee_card["permission_list"], $prepared_employee_card["role"], $prepared_employee_card["role_name"]);
			$prepared_employee_card["profile_card_permissions"] = self::_getFormattedCardPermissions($employee_card["permission_list"]);
		}

		if (isset($employee_card["note"])) {

			$prepared_employee_card["note"] = (object) [
				"text"       => (string) $employee_card["note"]["text"],
				"created_at" => (int) $employee_card["note"]["created_at"],
			];
		}

		return $prepared_employee_card;
	}

	/**
	 * Форматирует информацию о подключении к вебсокету для передачи клиенту
	 */
	public static function wsConnectionInfo(array $ws_connection_info):array {

		return [
			"token" => (string) $ws_connection_info["token"],
			"url"   => (string) $ws_connection_info["url"],
		];
	}

	/**
	 * Массив с conversation_map в массив объектов
	 */
	protected static function _conversationMapListToListOfObjects(array $conversation_list):array {

		$output = [];
		foreach ($conversation_list as $conversation) {

			$output[] = [
				"conversation_map" => $conversation["conversation_map"],
			];
		}
		return $output;
	}

	/**
	 * Форматирует массив реквестов на найм под клиентов
	 */
	public static function hiringRequestList(array $hiring_request_list):array {

		$output = [];
		foreach ($hiring_request_list as $item) {
			$output[] = (object) self::hiringRequest($item);
		}

		return $output;
	}

	/**
	 * Форматирует реквест на найм под клиентов
	 */
	public static function hiringRequest(Struct_Domain_HiringRequest_Formatted $hiring_request):array {

		return [
			"hiring_request_id" => (int) $hiring_request->hiring_request_id,
			"hired_by_user_id"  => (int) $hiring_request->hired_by_user_id,
			"created_at"        => (int) $hiring_request->created_at,
			"updated_at"        => (int) $hiring_request->updated_at,
			"status"            => (string) Domain_HiringRequest_Entity_Request::HIRING_REQUEST_TYPE_SCHEMA[$hiring_request->status],
			"candidate_user_id" => (int) $hiring_request->candidate_user_id,
			"thread_key"        => (string) !isEmptyString($hiring_request->thread_map) ? Type_Pack_Thread::doEncrypt($hiring_request->thread_map) : "",
			"message_key"       => (string) !isEmptyString($hiring_request->message_map) ? Type_Pack_Message::doEncrypt($hiring_request->message_map) : "",
			"data"              => (object) self::_formatHiringRequestData($hiring_request->data),
		];
	}

	/**
	 * Форматируем дату для заявки
	 * @long
	 */
	protected static function _formatHiringRequestData(array $data):array {

		$group_conversation_autojoin_item_list = [];
		foreach ($data["autojoin"]["group_conversation_autojoin_item_list"] as $v) {

			$group_conversation_autojoin_item_list[] = (object) [
				"conversation_key" => (string) $v["conversation_key"],
				"status"           => (int) $v["status"],
				"order"            => (int) $v["order"],
			];
		}

		$single_conversation_autojoin_item_list = [];
		foreach ($data["autojoin"]["single_conversation_autojoin_item_list"] as $v) {

			$single_conversation_autojoin_item_list[] = (object) [
				"user_id" => (int) $v["user_id"],
				"status"  => (int) $v["status"],
				"order"   => (int) $v["order"],

			];
		}
		$formatted_data = [
			"autojoin"        => (object) [
				"group_conversation_autojoin_item_list"  => (array) $group_conversation_autojoin_item_list,
				"single_conversation_autojoin_item_list" => (array) $single_conversation_autojoin_item_list,
			],
			"invited_comment" => (string) $data["invited_comment"],
		];

		if (isset($data["candidate_user_info"])) {

			$formatted_data["candidate_user_info"] = (object) [
				"full_name"       => (string) $data["candidate_user_info"]["full_name"],
				"avatar_file_key" => (string) $data["candidate_user_info"]["avatar_file_key"],
				"avatar_color"    => (string) \BaseFrame\Domain\User\Avatar::getColorOutput($data["candidate_user_info"]["avatar_color_id"]),
			];
		}
		return $formatted_data;
	}

	/**
	 * информация о диалоге
	 */
	public static function conversationInfo(Struct_Socket_Conversation_Info $conversation_info):array {

		return [
			"conversation_key" => (string) $conversation_info->conversation_key,
			"name"             => (string) $conversation_info->name,
			"members_count"    => (int) $conversation_info->member_count,
			"avatar"           => (object) [
				"file_map" => (string) $conversation_info->avatar_file_map,
			],
		];
	}

	/**
	 * информация о диалогах
	 *
	 * @param Struct_Socket_Conversation_Info[] $conversation_info_list
	 */
	public static function conversationInfoList(array $conversation_info_list):array {

		$output = [];
		foreach ($conversation_info_list as $conversation_info) {
			$output[] = self::conversationInfo($conversation_info);
		}
		return $output;
	}

	/**
	 * Форматирует реквест на увольнение под клиентов
	 */
	public static function dismissalRequest(Struct_Db_CompanyData_DismissalRequest $dismissal_request):array {

		$thread_map  = Domain_DismissalRequest_Entity_Request::getThreadMap($dismissal_request->extra);
		$message_map = Domain_DismissalRequest_Entity_Request::getMessageMap($dismissal_request->extra);

		return [
			"dismissal_request_id" => (int) $dismissal_request->dismissal_request_id,
			"creator_user_id"      => (int) $dismissal_request->creator_user_id,
			"created_at"           => (int) $dismissal_request->created_at,
			"updated_at"           => (int) $dismissal_request->updated_at,
			"status"               => (string) Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_TYPE_SCHEMA[$dismissal_request->status],
			"dismissal_user_id"    => (int) $dismissal_request->dismissal_user_id,
			"thread_key"           => (string) !isEmptyString($thread_map) ? Type_Pack_Thread::doEncrypt($thread_map) : "",
			"message_key"          => (string) !isEmptyString($message_map) ? Type_Pack_Message::doEncrypt($message_map) : "",
		];
	}

	/**
	 * Форматирует массив реквестов на увольенение под клиентов
	 */
	public static function dismissalRequestList(array $dismissal_request_list):array {

		$output = [];
		foreach ($dismissal_request_list as $item) {
			$output[] = (object) self::dismissalRequest($item);
		}

		return $output;
	}

	/**
	 * Преобразовать массив пользователей в объекты для поиска
	 */
	public static function searchMemberList(array $user_list):array {

		$search_result = [];
		foreach ($user_list as $user) {

			$item["type"]                    = "member";
			$item["item"]["user_id"]         = $user->user_id;
			$item["item"]["role"]            = $user->role;
			$item["item"]["permission_list"] = Permission::getPermissionList($user->permissions);

			$search_result[] = (object) $item;
		}

		return $search_result;
	}

	/**
	 * Преобразовать массив пользователей в объекты для поиска
	 */
	public static function searchMemberIdList(array $user_id_list):array {

		$search_result = [];
		foreach ($user_id_list as $user_id) {

			$item["type"]            = "member";
			$item["item"]["user_id"] = $user_id;

			$search_result[] = (object) $item;
		}

		return $search_result;
	}

	/**
	 * Форматируем данные об pin
	 */
	public static function pinInfo(Struct_Db_CompanyMember_SecurityPinConfirmStory $pin):array {

		return [
			"need_confirm_pin" => (string) $pin->confirm_key,
			"expire_at"        => (int) $pin->expires_at,
		];
	}

	/**
	 * получаем отформатированный permission_list
	 */
	protected static function _getFormattedPermissionList(array $permission_list):array {

		$permissions = Permission::addPermissionListToMask(Permission::DEFAULT, $permission_list);

		return Permission::formatWithProfileCardToOutput($permissions, 1);
	}

	/**
	 * получаем отформатированный permission_list
	 */
	protected static function _getFormattedCardPermissions(array $permission_list):array {

		$permissions = Permission::addPermissionListToMask(Permission::DEFAULT, $permission_list);

		return Permission::formatProfileCardToOutput($permissions);
	}

	/**
	 * Форматируем список id
	 */
	public static function leftUserIdList(array $left_user_id_list):array {

		$output = [];
		foreach ($left_user_id_list as $user_id) {
			$output[] = (int) $user_id;
		}
		return $output;
	}

	/**
	 * Форматируем access
	 */
	public static function access(Struct_Access_Main $access):array {

		return [
			"status" => (string) $access->status,
			"reason" => (string) $access->reason,
		];
	}

}
