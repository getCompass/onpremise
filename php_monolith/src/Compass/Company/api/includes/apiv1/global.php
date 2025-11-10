<?php

namespace Compass\Company;

/**
 * контроллер для технических методов клиента
 */
class Apiv1_Global extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"doStart",
		"onApplicationFocused",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод передает информацию о клиенте и загружает параметры, начальное состояние приложения
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_AnswerCommand
	 * @throws cs_PlatformNotFound
	 * @throws \queryException
	 */
	public function doStart():array {

		$permissions_output_version = match ($this->method_version) {
			1       => 1,
			2       => 2,
			default => \CompassApp\Domain\Member\Entity\Permission::CURRENT_PERMISSIONS_OUTPUT_SCHEMA_VERSION,
		};

		try {

			$is_push_body_display = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::PUSH_BODY_DISPLAY_KEY)["value"];
			$this->action->member([
				"is_display_push_body"       => $is_push_body_display,
				"permissions_output_version" => $permissions_output_version,
			]);

			[$config, $ws_connection_info, $notification_preferences, $member_permission_list, $access, $has_confirmed_join_request] =
				Domain_User_Scenario_Api::doStart($this->user_id, $this->role, $this->permissions);
		} catch (\cs_RowIsEmpty) {
			return $this->error(654, "User is not a member of the company");
		} catch (cs_UserNotLoggedIn) {
			return $this->ok();
		}

		$output = [
			"access"                                   => (object) Apiv1_Format::access($access),
			"is_push_body_display"                     => (int) $config["is_push_body_display"],
			"is_extended_employee_card_enabled"        => (int) $config["is_extended_employee_card_enabled"],
			"is_general_chat_notification_enabled"     => (int) $config["is_general_chat_notification_enabled"],
			"is_unlimited_messages_editing_enabled"    => (int) $config["is_unlimited_messages_editing_enabled"],
			"is_unlimited_messages_deleting_enabled"   => (int) $config["is_unlimited_messages_deleting_enabled"],
			"is_add_to_general_chat_on_hiring_enabled" => (int) $config["is_add_to_general_chat_on_hiring"],
			"is_local_links_enabled"                   => (int) $config["is_local_links_enabled"],
			"show_message_read_status"                 => (int) $config["show_message_read_status"],
			"ws_connection_info"                       => (object) Apiv1_Format::wsConnectionInfo($ws_connection_info),
			"notification_preferences"                 => (object) $notification_preferences,
			"member_permission_list"                   => (object) $member_permission_list,
			"space_uniq"                               => (string) random_int(1000000, 9999999) . "-" . random_int(100000, 999999),
			"has_confirmed_join_request"               => (int) $has_confirmed_join_request,
			"max_file_size"                            => (int) MAX_FILE_SIZE_MB,
		];

		return $this->ok($output);
	}

	/**
	 * Метод вызывается, когда пользователь выводит приложение из бекграунда
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public function onApplicationFocused():array {

		if ($this->user_id > 0) {

			[$local_date, $local_time, $_] = getLocalClientTime();
			Domain_User_Action_AddScreenTime::do($this->user_id, $local_date, $local_time);
		}

		return $this->ok();
	}
}