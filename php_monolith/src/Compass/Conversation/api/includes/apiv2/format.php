<?php

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для форматирование сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv2_Format {

	// массив для преобразования числового type диалога в строковый
	public const CONVERSATION_TYPE_SCHEMA = [
		CONVERSATION_TYPE_SINGLE_DEFAULT         => "single",
		CONVERSATION_TYPE_GROUP_DEFAULT          => "group",
		CONVERSATION_TYPE_GROUP_RESPECT          => "group", // не меняем тип для клиентов, но меняем подтип
		CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT => "single_with_system_bot",
		CONVERSATION_TYPE_PUBLIC_DEFAULT         => "public",
		CONVERSATION_TYPE_GROUP_HIRING           => "join_legacy",
		CONVERSATION_TYPE_GROUP_GENERAL          => "general",
		CONVERSATION_TYPE_SINGLE_NOTES           => "notes",
		CONVERSATION_TYPE_GROUP_SUPPORT          => "group_support",
	];

	// массив для преобразования числового type файла в строковый
	protected const _FILE_TYPE_SCHEMA = [
		FILE_TYPE_DEFAULT  => "file",
		FILE_TYPE_IMAGE    => "image",
		FILE_TYPE_VIDEO    => "video",
		FILE_TYPE_AUDIO    => "audio",
		FILE_TYPE_DOCUMENT => "document",
		FILE_TYPE_ARCHIVE  => "archive",
		FILE_TYPE_VOICE    => "voice",
	];

	// преобразование числового типа сообщения в текстовый
	protected const _CONVERSATION_ROLE_SCHEMA = [
		Type_Conversation_Meta_Users::ROLE_NOT_ATTACHED => "not_attached",
		Type_Conversation_Meta_Users::ROLE_DEFAULT      => "member",
		Type_Conversation_Meta_Users::ROLE_ADMIN        => "owner",
		Type_Conversation_Meta_Users::ROLE_OWNER        => "owner",
	];

	// массив для получения строкового подтипа диалога
	public const CONVERSATION_SUBTYPE_SCHEMA = [
		CONVERSATION_TYPE_GROUP_DEFAULT => "default",
		CONVERSATION_TYPE_GROUP_HIRING  => "hiring",
		CONVERSATION_TYPE_GROUP_GENERAL => "general",
		CONVERSATION_TYPE_GROUP_SUPPORT => "group_support",
		CONVERSATION_TYPE_GROUP_RESPECT => "respect",
	];

	// преобразование числового типа сообщения в текстовый
	protected const _CONVERSATION_MESSAGE_TYPE_SCHEMA = [
		CONVERSATION_MESSAGE_TYPE_TEXT                                   => "text",
		CONVERSATION_MESSAGE_TYPE_FILE                                   => "file",
		CONVERSATION_MESSAGE_TYPE_INVITE                                 => "invite",
		CONVERSATION_MESSAGE_TYPE_CALL                                   => "call",
		CONVERSATION_MESSAGE_TYPE_QUOTE                                  => "quote",
		CONVERSATION_MESSAGE_TYPE_MASS_QUOTE                             => "mass_quote",
		CONVERSATION_MESSAGE_TYPE_REPOST                                 => "repost",
		CONVERSATION_MESSAGE_TYPE_SYSTEM                                 => "system",
		CONVERSATION_MESSAGE_TYPE_DELETED                                => "deleted",
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST                          => "repost",
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT                => "text",
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE                => "file",
		CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE               => "mass_quote",
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_TEXT                        => "system_bot_text",
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_RATING                      => "system_bot_rating",
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE                        => "system_bot_file",
		CONVERSATION_MESSAGE_TYPE_RESPECT                                => "respect",
		CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST                         => "hiring_request",
		CONVERSATION_MESSAGE_TYPE_DISMISSAL_REQUEST                      => "dismissal_request",
		CONVERSATION_MESSAGE_TYPE_EMPLOYEE_METRIC_DELTA                  => "employee_metric_delta",
		CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_ANNIVERSARY            => "editor_employee_anniversary",
		CONVERSATION_MESSAGE_TYPE_EMPLOYEE_ANNIVERSARY                   => "employee_anniversary",
		CONVERSATION_MESSAGE_TYPE_EDITOR_FEEDBACK_REQUEST                => "editor_feedback_request",
		CONVERSATION_MESSAGE_TYPE_EDITOR_WORKSHEET_RATING                => "editor_worksheet_rating",
		CONVERSATION_MESSAGE_TYPE_COMPANY_EMPLOYEE_METRIC_STATISTIC      => "company_employee_metric_statistic",
		CONVERSATION_MESSAGE_TYPE_EDITOR_EMPLOYEE_METRIC_NOTICE          => "editor_employee_metric_notice",
		CONVERSATION_MESSAGE_TYPE_WORK_TIME_AUTO_LOG_NOTICE              => "work_time_auto_log_notice",
		CONVERSATION_MESSAGE_TYPE_INVITE_TO_COMPANY_INVITER_SINGLE       => "invite_to_company_inviter_single",
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_MESSAGES_MOVED_NOTIFICATION => "system_bot_messages_moved_notification",
		CONVERSATION_MESSAGE_TYPE_SHARED_MEMBER                          => "shared_member",
		CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_REMIND                      => "system_bot_remind",
		CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE                       => "media_conference",
	];

	// массив для преобразования типа дополнительного поля в текстовый для сообщения
	protected const _CONVERSATION_MESSAGE_ADDITIONAL_TYPE_SCHEMA = [
		Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_WORKED_HOURS => "worked_hours",
		Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_RESPECT      => "respect",
		Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_EXACTINGNESS => "exactingness",
		Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_ACHIEVEMENT  => "achievement",
	];

	// сообщение из диалога
	public static function conversationMessage(array $prepared_message):array {

		$output = self::_makeConversationMessageOutput($prepared_message);

		// прикрепляем map родительской сущности
		$output = self::_attachParentMap($output, $prepared_message);

		// прикрепляем список ссылок, если есть
		$output = self::_attachLinkListIfExist($output, $prepared_message);

		// прикрепляем превью, если есть
		$output = self::_attachPreviewIfExist($output, $prepared_message);

		// прикрепляем map треда если есть
		$output = self::_attachThreadIfExist($output, $prepared_message);

		// прикрепляем Напоминание, если есть
		$output = self::_attachRemindIfExist($output, $prepared_message);

		// формируем data сообщения в зависимости от его типа
		$output["data"] = (object) self::_makeConversationMessageData($output["type"], $prepared_message);

		// формируем additional поля сообщения
		$output["additional"] = (array) self::_makeConversationMessageAdditional($prepared_message);

		return $output;
	}

	// формируем массив conversation_message
	protected static function _makeConversationMessageOutput(array $prepared_message):array {

		return [
			"message_map"          => (string) $prepared_message["message_map"],
			"block_id"             => (int) $prepared_message["block_id"],
			"mention_user_id_list" => (array) arrayValuesInt($prepared_message["mention_user_id_list"]),
			"type"                 => (string) self::_getConversationMessageOutputType($prepared_message["type"]),
			"is_edited"            => (int) $prepared_message["is_edited"],
			"is_archived"          => (int) 0,
			"message_index"        => (int) $prepared_message["message_index"],
			"sender_id"            => (int) $prepared_message["sender_id"],
			"created_at"           => (int) $prepared_message["created_at"],
			"allow_edit_till"      => (int) $prepared_message["allow_edit_till"],
			"allow_delete_till"    => (int) $prepared_message["allow_delete_till"],
			"last_message_edited"  => (int) $prepared_message["last_message_edited"],
			"last_reaction_edited" => (int) $prepared_message["last_reaction_edited"],
			"client_message_id"    => (string) $prepared_message["client_message_id"],
			"text"                 => (string) $prepared_message["text"],
			"reaction_list"        => (array) $prepared_message["reaction_list"],
			"platform"             => (string) $prepared_message["platform"],
		];
	}

	// возвращает тип сообщения для фронта на основе его свойства type
	protected static function _getConversationMessageOutputType(int $message_type):string {

		if (!isset(self::_CONVERSATION_MESSAGE_TYPE_SCHEMA[$message_type])) {

			throw new ParseFatalException("there is no format output for message type {$message_type}");
		}

		return self::_CONVERSATION_MESSAGE_TYPE_SCHEMA[$message_type];
	}

	// прикрепляем map родительской сущности
	protected static function _attachParentMap(array $output, array $prepared_message):array {

		// если сообщение написано в диалог
		if (isset($prepared_message["conversation_map"])) {
			$output["conversation_map"] = (string) $prepared_message["conversation_map"];
		}

		// если сообщение написано в тред
		if (isset($prepared_message["thread_map"])) {
			$output["thread_map"] = (string) $prepared_message["thread_map"];
		}

		return $output;
	}

	// прикрепляем список ссылок к сообщению, если есть
	protected static function _attachLinkListIfExist(array $output, array $prepared_message):array {

		// если в сообщении есть список ссылок
		if (isset($prepared_message["link_list"])) {

			foreach ($prepared_message["link_list"] as $key => $link) {

				if (isset($link["original_link"])) {
					$prepared_message["link_list"][$key]["redirect_link"] = $link["original_link"];
				}
			}

			$output["link_list"] = (array) $prepared_message["link_list"];
		}

		return $output;
	}

	// прикрепляем превью к сообщению, если есть
	protected static function _attachPreviewIfExist(array $output, array $prepared_message):array {

		// если прикреплено "простое" превью - не прикрепляем к сообщению
		if (isset($prepared_message["preview_type"])) {

			if ($prepared_message["preview_type"] === PREVIEW_TYPE_SIMPLE) {
				return $output;
			}
			$output["preview_type"] = (string) Type_Preview_Main::PREVIEW_TYPE_SCHEMA[$prepared_message["preview_type"]];
		}

		// если в сообщении есть превью
		if (isset($prepared_message["preview_map"])) {
			$output["preview_map"] = (string) $prepared_message["preview_map"];
		}

		// если в сообщении есть preview_image
		if (isset($prepared_message["preview_image"])) {
			$output["preview_image"] = (array) $prepared_message["preview_image"];
		}

		return $output;
	}

	// прикрепляем тред к сообщению, если есть
	protected static function _attachThreadIfExist(array $output, array $prepared_message):array {

		// если к сообщению существует тред
		if (isset($prepared_message["child_thread"]["thread_map"])) {

			$output["child_thread"] = (object) [
				"thread_map" => (string) $prepared_message["child_thread"]["thread_map"],
				"is_hidden"  => (int) ($prepared_message["child_thread"]["is_hidden"] ?? 0),
			];
		}

		return $output;
	}

	// прикрепляем Напоминание к сообщению, если есть
	protected static function _attachRemindIfExist(array $output, array $prepared_message):array {

		$output["remind"] = [];

		// если в сообщении есть превью
		if (isset($prepared_message["remind"])) {
			$output["remind"] = $prepared_message["remind"];
		}

		$output["remind"] = (object) $output["remind"];

		return $output;
	}

	// формируем data сообщения в зависимости от его типа
	// @long
	protected static function _makeConversationMessageData(string $type, array $prepared_message):array {

		switch ($type) {

			case "text":
			case "system_bot_text":
			case "respect":
			case "deleted":
			case "system_bot_messages_moved_notification":

				return [];

			case "system_bot_rating":

				$data = [
					"year"            => (int) $prepared_message["data"]["year"],
					"week"            => (int) $prepared_message["data"]["week"],
					"count"           => (int) $prepared_message["data"]["count"],
					"read_at_by_list" => self::_formatReadByAtList($prepared_message["data"]["read_at_by_list"] ?? []),
					"is_read"         => (int) ($prepared_message["data"]["read_at_by_list"] ?? []),
				];

				if (isset($prepared_message["data"]["name"])) {
					$data["name"] = (string) $prepared_message["data"]["name"];
				}

				return $data;

			case "invite":

				return [
					"invite_map"  => (string) $prepared_message["data"]["invite_map"],
					"invite_type" => (string) $prepared_message["data"]["invite_type"],
				];

			case "call":

				$data = [
					"call_map" => (string) $prepared_message["data"]["call_map"],
				];

				if (isset($prepared_message["data"]["call_report_id"], $prepared_message["data"]["call_duration"])) {

					$data["call_report_id"] = (int) $prepared_message["data"]["call_report_id"];
					$data["call_duration"]  = (int) $prepared_message["data"]["call_duration"];
				}

				return $data;

			case "media_conference":

				$data = [
					"conference_id"            => (string) $prepared_message["data"]["conference_id"],
					"conference_accept_status" => (string) $prepared_message["data"]["conference_accept_status"],
					"conference_link"          => (string) $prepared_message["data"]["conference_link"],
					"conference_code"          => (string) $prepared_message["data"]["conference_code"],
				];

				return $data;
			case "file":
			case "system_bot_file":

				$data = [
					"file_map"  => (string) $prepared_message["data"]["file_map"],
					"file_type" => (string) self::_FILE_TYPE_SCHEMA[$prepared_message["data"]["file_type"]],
				];

				if (isset($prepared_message["data"]["file_uid"])) {
					$data["file_uid"] = (string) $prepared_message["data"]["file_uid"];
				}

				if (isset($prepared_message["data"]["file_name"])) {
					$data["file_name"] = (string) $prepared_message["data"]["file_name"];
				}

				if (isset($prepared_message["data"]["file_width"]) && isset($prepared_message["data"]["file_height"])) {

					$data["file_width"]  = (int) $prepared_message["data"]["file_width"];
					$data["file_height"] = (int) $prepared_message["data"]["file_height"];
				}

				return $data;

			case "quote":

				return [
					"quoted_message" => (object) self::conversationMessage($prepared_message["data"]["quoted_message"]),
				];

			case "mass_quote":
			case "system_bot_remind":

				$data = [
					"quoted_message_list" => (array) [],
				];

				// проходимся по каждому сообщению из цитаты
				foreach ($prepared_message["data"]["quoted_message_list"] as $v) {
					$data["quoted_message_list"][] = (object) self::conversationMessage($v);
				}

				// добавляем количество сообщений, который имеет в себе цитата
				$data["quoted_message_count"] = (int) $prepared_message["data"]["quoted_message_count"];

				return $data;

			case "repost":

				$data = [
					"reposted_message_list" => (array) [],
				];

				// проходимся по каждому сообщению из репоста
				foreach ($prepared_message["data"]["reposted_message_list"] as $v) {
					$data["reposted_message_list"][] = (object) self::conversationMessage($v);
				}

				// добавляем количество сообщений, который имеет в себе репост
				$data["reposted_message_count"] = (int) $prepared_message["data"]["reposted_message_count"];

				return $data;

			case "system":

				$system_message_type = $prepared_message["data"]["system_message_type"];
				return [
					"system_message_type" => (string) $system_message_type,
					"extra"               => (object) self::_formatSystemMessageExtra(
						$system_message_type,
						$prepared_message["data"]["extra"]
					),
				];

			case "employee_metric_delta":

				$output = [
					"metric_type"     => (string) $prepared_message["data"]["metric_type"],
					"metric_extra"    => [
						"editor_user_id" => (int) $prepared_message["data"]["metric_extra"]["editor_user_id"],
						"metric_id"      => (int) $prepared_message["data"]["metric_extra"]["metric_id"],
						"header_text"    => (string) $prepared_message["data"]["metric_extra"]["header_text"],
						"comment_text"   => (string) $prepared_message["data"]["metric_extra"]["comment_text"],
						"value_delta"    => (string) $prepared_message["data"]["metric_extra"]["value_delta"],
					],
					"read_at_by_list" => self::_formatReadByAtList($prepared_message["data"]["read_at_by_list"]),
					"is_read"         => (int) $prepared_message["data"]["read_at_by_list"],
				];

				// если есть исходное сообщение, то пакуем его
				if ($prepared_message["data"]["metric_extra"]["source_message_map"] !== "") {

					$output["metric_extra"]["source_message_key"] = \CompassApp\Pack\Message\Conversation::doEncrypt(
						$prepared_message["data"]["metric_extra"]["source_message_map"]
					);
				} else {
					$output["metric_extra"]["source_message_key"] = "";
				}

				return $output;

			case "editor_employee_metric_notice":

				return [
					"employee_user_id" => (int) $prepared_message["data"]["employee_user_id"],
				];

			case "editor_employee_anniversary":

				return [
					"employee_user_id" => (int) $prepared_message["data"]["employee_user_id"],
					"hired_at"         => (int) $prepared_message["data"]["hired_at"],
					"read_at_by_list"  => self::_formatReadByAtList($prepared_message["data"]["read_at_by_list"]),
					"is_read"          => (int) $prepared_message["data"]["read_at_by_list"],
				];

			case "employee_anniversary":

				return [
					"hired_at"        => (int) $prepared_message["data"]["hired_at"],
					"read_at_by_list" => self::_formatReadByAtList($prepared_message["data"]["read_at_by_list"]),
					"is_read"         => (int) $prepared_message["data"]["read_at_by_list"],
				];

			case "editor_feedback_request":

				return [
					"feedback_request_id" => (int) $prepared_message["data"]["feedback_request_id"],
					"employee_user_id"    => (int) $prepared_message["data"]["employee_user_id"],
					"period_id"           => (int) $prepared_message["data"]["period_id"],
					"period_start_date"   => (int) $prepared_message["data"]["period_start_date"],
					"period_end_date"     => (int) $prepared_message["data"]["period_end_date"],
				];

			case "editor_worksheet_rating":

				return [
					"period_id"                  => (int) $prepared_message["data"]["period_id"],
					"period_start_date"          => (int) $prepared_message["data"]["period_start_date"],
					"period_end_date"            => (int) $prepared_message["data"]["period_end_date"],
					"leader_user_work_item_list" => self::_formatUserWorkItemList($prepared_message["data"]["leader_user_work_item_list"]),
					"driven_user_work_item_list" => self::_formatUserWorkItemList($prepared_message["data"]["driven_user_work_item_list"]),
				];

			case "company_employee_metric_statistic":

				return [
					"company_name"           => (string) $prepared_message["data"]["company_name"],
					"period_id"              => (int) $prepared_message["data"]["period_id"],
					"period_start_date"      => (int) $prepared_message["data"]["period_start_date"],
					"period_end_date"        => (int) $prepared_message["data"]["period_end_date"],
					"metric_count_item_list" => self::_formatMetricCountItemList($prepared_message["data"]["metric_count_item_list"]),
					"read_at_by_list"        => self::_formatReadByAtList($prepared_message["data"]["read_at_by_list"]),
					"is_read"                => (int) $prepared_message["data"]["read_at_by_list"],
				];

			case "work_time_auto_log_notice":

				return [
					"work_time" => (int) $prepared_message["data"]["work_time"],
				];

			case "hiring_request":

				return [
					"hiring_request_id" => (int) $prepared_message["data"]["hiring_request_id"],
				];

			case "dismissal_request":

				return [
					"dismissal_request_id" => (int) $prepared_message["data"]["dismissal_request_id"],
				];

			case "invite_to_company_inviter_single":

				return [
					"company_inviter_user_id" => (int) $prepared_message["data"]["company_inviter_user_id"],
					"read_at_by_list"         => self::_formatReadByAtList($prepared_message["data"]["read_at_by_list"]),
					"is_read"                 => (int) $prepared_message["data"]["read_at_by_list"],
				];

			case "shared_member":

				return [
					"shared_user_id_list" => (array) arrayValuesInt($prepared_message["data"]["shared_user_id_list"]),
				];

			default:

				throw new ParseFatalException("message type is not available");
		}
	}

	// форматируем additional поля сообщения
	protected static function _makeConversationMessageAdditional(array $prepared_message):array {

		$output = [];

		// форматируем каждое дополнительное поле
		foreach ($prepared_message["additional"] as $item) {

			// переводим идентификатор типа в text
			$temp     = (object) [
				"type" => (string) self::_CONVERSATION_MESSAGE_ADDITIONAL_TYPE_SCHEMA[$item["type"]],
				"data" => (object) self::_getDataForAdditional($item),
			];
			$output[] = $temp;
		}

		return $output;
	}

	// достаем данные additional-полей
	// @long
	protected static function _getDataForAdditional(array $item):array {

		return match ($item["type"]) {
			Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_WORKED_HOURS => [
				"worked_hours_id"  => (int) $item["data"]["worked_hours_id"],
				"day_start_string" => (string) $item["data"]["day_start_string"],
				"sender_type"      => (string) ($item["data"]["sender_type"] ?? Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_USER_SENDER),
			],
			Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_RESPECT      => [
				"receiver_user_id" => (int) $item["data"]["receiver_user_id"],
				"receiver_name"    => (string) ($item["data"]["receiver_name"] ?? ""),
				"respect_id"       => (int) $item["data"]["respect_id"],
				"sender_name"      => (string) ($item["data"]["sender_name"] ?? ""),
			],
			Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_EXACTINGNESS => [
				"receiver_user_id" => (int) $item["data"]["receiver_user_id"],
				"receiver_name"    => (string) ($item["data"]["receiver_name"] ?? ""),
				"exactingness_id"  => (int) ($item["data"]["exactingness_id"] ?? 0),
				"sender_name"      => (string) ($item["data"]["sender_name"] ?? ""),
			],
			Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_ACHIEVEMENT  => [
				"receiver_user_id" => (int) $item["data"]["receiver_user_id"],
				"achievement_id"   => (int) $item["data"]["achievement_id"],
			],
			default                                                                 => [],
		};
	}

	// форматируем extra системного сообщения на основе его типа
	protected static function _formatSystemMessageExtra(string $system_message_type, array $extra):array {

		return match ($system_message_type) {
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_INVITED_TO_GROUP,
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_JOINED_TO_GROUP,
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_DECLINED_INVITE,
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_LEFT_GROUP,
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_LEFT_COMPANY,
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_KICKED_FROM_GROUP,
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_PROMOTED_TO_ADMIN,
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_ADMIN_DEMOTED_TO_USER
			=> [
				"user_id" => (int) $extra["user_id"],
			],
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_USER_ADD_GROUP
			=> self::_formatSystemMessageUserAddGroupExtra($extra),
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_ADMIN_RENAMED_GROUP
			=> self::_formatSystemMessageAdminRenamedGroupExtra($extra),
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_DESCRIPTION
			=> self::_formatSystemMessageAdminChangedGroupDescriptionExtra($extra),
			Type_Conversation_Message_Handler_Default::SYSTEM_MESSAGE_ADMIN_CHANGED_GROUP_AVATAR
			=> self::_formatSystemMessageAdminChangedGroupAvatarExtra($extra),
			default
			=> throw new ParseFatalException("Unsupported system message type '{$system_message_type}' in " . __METHOD__),
		};
	}

	/**
	 * форматируем extra системного сообщения типа user_add_group
	 */
	protected static function _formatSystemMessageUserAddGroupExtra(array $extra):array {

		return [
			"user_id"    => (int) $extra["user_id"],
			"group_name" => (string) $extra["group_name"],
		];
	}

	// форматируем extra системного сообщения типа admin_renamed_group
	protected static function _formatSystemMessageAdminRenamedGroupExtra(array $extra):array {

		$output = [
			"user_id"    => (int) $extra["user_id"],
			"group_name" => (string) $extra["group_name"],
		];

		if (!isset($extra["old_group_name"])) {
			return $output;
		}

		$output["old_group_name"] = (string) $extra["old_group_name"];
		return $output;
	}

	// форматируем extra системного сообщения типа admin_changed_group_description
	protected static function _formatSystemMessageAdminChangedGroupDescriptionExtra(array $extra):array {

		$output = [
			"user_id"     => (int) $extra["user_id"],
			"description" => (string) $extra["description"] ?? "",
		];

		if (!isset($extra["old_group_description"])) {
			return $output;
		}

		$output["old_group_description"] = (string) $extra["old_group_description"];
		return $output;
	}

	// форматируем extra системного сообщения типа admin_changed_group_avatar
	protected static function _formatSystemMessageAdminChangedGroupAvatarExtra(array $extra):array {

		return [
			"user_id"  => (int) $extra["user_id"],
			"file_map" => (string) $extra["file_map"],
		];
	}

	/**
	 * Подготавливает список пользователь-отработанное время.
	 *
	 * @param array user_time_list
	 *
	 */
	protected static function _formatUserWorkItemList(array $user_time_list):array {

		$output = [];

		foreach ($user_time_list as $v) {

			$output[] = [
				"user_id"   => (int) $v["user_id"],
				"work_time" => (int) $v["work_time"],
			];
		}

		return $output;
	}

	/**
	 * Подготавливает список элементов метрика-количество.
	 *
	 * @param array user_time_list
	 *
	 */
	protected static function _formatMetricCountItemList(array $user_time_list):array {

		$output = [];

		foreach ($user_time_list as $v) {

			$output[] = [
				"metric_type" => (string) $v["metric_type"],
				"count"       => (int) $v["count"],
			];
		}

		return $output;
	}

	/**
	 * Подготавливает список элементов кем прочитано-когда.
	 *
	 */
	protected static function _formatReadByAtList(array $read_at_by_list):array {

		$output = [];

		foreach ($read_at_by_list as $v) {

			$output[] = [
				"user_id" => (int) $v["user_id"],
				"read_at" => (int) $v["read_at"],
			];
		}

		return $output;
	}

	// форматируем реакции
	public static function reactionBlockList(array $reaction_block_list):array {

		$output = [];
		foreach ($reaction_block_list as $reaction_block_item) {

			$reaction_message_list = [];
			foreach ($reaction_block_item["reaction_list"] as $item) {

				$reaction_list = [];
				foreach ($item["reaction_user_list"] as $reaction_name => $user_id_list) {

					$reaction_list[] = [
						"reaction_name" => (string) $reaction_name,
						"user_id_list"  => (array) $user_id_list,
					];
				}

				$reaction_message_list[] = [
					"reaction_list" => $reaction_list,
					"message_map"   => $item["message_map"],
				];
			}
			$output[] = [
				"block_id"              => (int) $reaction_block_item["block_id"],
				"reaction_message_list" => (array) $reaction_message_list,
			];
		}
		return $output;
	}

	/**
	 * Сущность чата
	 *
	 * @param array $prepared_conversation
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function conversation(array $prepared_conversation):array {

		$output = self::_makeConversationOutput($prepared_conversation);

		// добавляем к ответу last_read_message_key если он есть
		if (mb_strlen($prepared_conversation["last_read_message_map"]) > 0) {
			$output["last_read"]["message_map"] = (string) $prepared_conversation["last_read_message_map"];
		}

		// добавляем к ответу последнее сообщение, если оно есть
		if (isset($prepared_conversation["last_message"])) {
			$output["last_message"] = (object) self::_lastMessage($prepared_conversation["last_message"]);
		}

		return $output;
	}

	/**
	 * Формируем объект conversation
	 *
	 * @param array $prepared_conversation
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _makeConversationOutput(array $prepared_conversation):array {

		return [
			"conversation_map" => (string) $prepared_conversation["conversation_map"],
			"is_have_notice"   => (int) $prepared_conversation["is_have_notice"],
			"is_favorite"      => (int) $prepared_conversation["is_favorite"],
			"is_muted"         => (int) $prepared_conversation["is_muted"],
			"is_mentioned"     => (int) $prepared_conversation["is_mentioned"],
			"is_hidden"        => (int) $prepared_conversation["is_hidden"],
			"is_unread"        => (int) $prepared_conversation["is_unread"],
			"muted_until"      => (int) $prepared_conversation["muted_until"],
			"unread_count"     => (int) $prepared_conversation["unread_count"],
			"created_at"       => (int) $prepared_conversation["created_at"],
			"updated_at"       => (int) $prepared_conversation["updated_at"],
			"type"             => (string) self::getConversationOutputType($prepared_conversation["type"]),
			"talking_hash"     => (string) $prepared_conversation["talking_hash"],
			"users"            => (array) $prepared_conversation["user_id_list"],
			"data"             => (object) self::_conversationData($prepared_conversation["type"], $prepared_conversation["data"]),
		];
	}

	/**
	 * форматируем conversation мету
	 *
	 * @param array $prepared_conversation
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	#[ArrayShape(["conversation_map" => "string", "created_at" => "int", "total_action_count" => "int", "messages_updated_at" => "int", "reactions_updated_at" => "int", "threads_updated_at" => "int", "messages_updated_version" => "int", "reactions_updated_version" => "int", "threads_updated_version" => "int", "type" => "string", "users" => "array", "talking_hash" => "string", "data" => "object"])]
	public static function conversationMeta(array $prepared_conversation):array {

		return [
			"conversation_map"          => (string) $prepared_conversation["conversation_map"],
			"created_at"                => (int) $prepared_conversation["created_at"],
			"total_action_count"        => (int) $prepared_conversation["total_action_count"],
			"messages_updated_at"       => (int) $prepared_conversation["messages_updated_at"],
			"reactions_updated_at"      => (int) $prepared_conversation["reactions_updated_at"],
			"threads_updated_at"        => (int) $prepared_conversation["threads_updated_at"],
			"messages_updated_version"  => (int) $prepared_conversation["messages_updated_version"],
			"reactions_updated_version" => (int) $prepared_conversation["reactions_updated_version"],
			"threads_updated_version"   => (int) $prepared_conversation["threads_updated_version"],
			"type"                      => (string) self::getConversationOutputType($prepared_conversation["type"]),
			"users"                     => (array) $prepared_conversation["users"],
			"talking_hash"              => (string) $prepared_conversation["talking_hash"],
			"data"                      => (object) self::_conversationData($prepared_conversation["type"], $prepared_conversation["data"]),
		];
	}

	// возвращает тип диалога для фронта на основе его свойства type
	public static function getConversationOutputType(int $conversation_type):string {

		if (!isset(self::CONVERSATION_TYPE_SCHEMA[$conversation_type])) {

			throw new ParseFatalException("there is no format output for conversation type {$conversation_type}");
		}

		return self::CONVERSATION_TYPE_SCHEMA[$conversation_type];
	}

	// поле data в conversation
	// @long - содержит switch case
	protected static function _conversationData(int $type, array $data):array {

		switch ($type) {

			case CONVERSATION_TYPE_SINGLE_DEFAULT:
			case CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT:

				$output = [
					"allow_status"     => (int) $data["allow_status"],
					"opponent_user_id" => (int) $data["opponent_user_id"],
				];
				break;

			case CONVERSATION_TYPE_GROUP_DEFAULT:
			case CONVERSATION_TYPE_GROUP_RESPECT:
			case CONVERSATION_TYPE_GROUP_GENERAL:
			case CONVERSATION_TYPE_GROUP_SUPPORT:

				$output = [
					"name"            => (string) $data["name"],
					"members_count"   => (int) $data["member_count"],
					"owner_user_list" => (array) $data["owner_user_list"],
					"role"            => (string) self::getUserRole($data["role"]),
					"group_options"   => (object) $data["group_options"],
					"subtype"         => (string) self::getConversationSubtype($data["subtype"]),
					"description"     => (string) $data["description"],
				];

				// добавляем к ответу avatar_file_key если он есть
				if (mb_strlen($data["avatar_file_map"]) > 0) {
					$output["avatar"]["file_map"] = (string) $data["avatar_file_map"];
				}
				break;

			case CONVERSATION_TYPE_GROUP_HIRING:

				$output = [
					"name"            => (string) $data["name"],
					"members_count"   => (int) $data["member_count"],
					"owner_user_list" => (array) $data["owner_user_list"],
					"role"            => (string) self::getUserRole($data["role"]),
				];

				// добавляем к ответу avatar_file_key если он есть
				if (mb_strlen($data["avatar_file_map"]) > 0) {
					$output["avatar"]["file_map"] = (string) $data["avatar_file_map"];
				}
				break;

			case CONVERSATION_TYPE_SINGLE_NOTES:

				$output = [
					"name"        => (string) $data["name"],
					"description" => (string) $data["description"],
				];

				// добавляем к ответу avatar_file_key если он есть
				if (mb_strlen($data["avatar_file_map"]) > 0) {
					$output["avatar"]["file_map"] = (string) $data["avatar_file_map"];
				}
				break;

			case CONVERSATION_TYPE_PUBLIC_DEFAULT:

				$output = [];
				break;

			default:
				throw new ParseFatalException("conversation type is not available");
		}

		return $output;
	}

	/**
	 * элемент левого меню пользователя
	 *
	 * @param array $prepared_left_menu
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function leftMenu(array $prepared_left_menu):array {

		$output = self::_makeLeftMenuOutput($prepared_left_menu);

		// добавляем к ответу last_read_message_key если он есть
		if (mb_strlen($prepared_left_menu["last_read_message_map"]) > 0) {
			$output["last_read"]["message_map"] = (string) $prepared_left_menu["last_read_message_map"];
		}

		// добавляем к ответу последнее сообщение, если оно есть
		if (isset($prepared_left_menu["last_message"])) {
			$output["last_message"] = (object) self::_lastMessage($prepared_left_menu["last_message"]);
		}

		return $output;
	}

	/**
	 * формируем массив left_menu_item
	 *
	 * @param array $prepared_left_menu_item
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _makeLeftMenuOutput(array $prepared_left_menu_item):array {

		return [
			"conversation_map" => (string) $prepared_left_menu_item["conversation_map"],
			"is_favorite"      => (int) $prepared_left_menu_item["is_favorite"],
			"is_mentioned"     => (int) $prepared_left_menu_item["is_mentioned"],
			"is_hidden"        => (int) $prepared_left_menu_item["is_hidden"],
			"is_leaved"        => (int) $prepared_left_menu_item["is_leaved"],
			"is_muted"         => (int) $prepared_left_menu_item["is_muted"],
			"is_unread"        => (int) $prepared_left_menu_item["is_unread"],
			"is_have_notice"   => (int) $prepared_left_menu_item["is_have_notice"],
			"muted_until"      => (int) $prepared_left_menu_item["muted_until"],
			"unread_count"     => (int) $prepared_left_menu_item["unread_count"],
			"version"          => (int) $prepared_left_menu_item["version"],
			"created_at"       => (int) $prepared_left_menu_item["created_at"],
			"updated_at"       => (int) $prepared_left_menu_item["updated_at"],
			"type"             => (string) self::getConversationOutputType($prepared_left_menu_item["type"]),
			"data"             => (object) self::_leftMenuData($prepared_left_menu_item["type"], $prepared_left_menu_item["data"]),
		];
	}

	/**
	 * поле data в left_menu
	 *
	 * @param int   $type
	 * @param array $data
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @long - большой switch-case
	 */
	protected static function _leftMenuData(int $type, array $data):array {

		$output = [];

		// формируем ответ по типу диалога
		switch ($type) {

			// если single-диалог
			case CONVERSATION_TYPE_SINGLE_DEFAULT:
			case CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT:

				$output["opponent_user_id"] = (int) $data["opponent_user_id"];
				$output["allow_status"]     = (int) $data["allow_status_alias"];
				break;

			// если group-диалог
			case CONVERSATION_TYPE_GROUP_DEFAULT:
			case CONVERSATION_TYPE_GROUP_RESPECT:
			case CONVERSATION_TYPE_GROUP_SUPPORT:

				$output = [
					"name"          => (string) $data["name"],
					"members_count" => (int) $data["member_count"],
					"role"          => (string) self::getUserRole($data["role"]),
					"subtype"       => (string) self::getConversationSubtype($type),
				];

				// добавляем к ответу avatar_file_key если он есть
				if (mb_strlen($data["avatar_file_map"]) > 0) {
					$output["avatar"]["file_map"] = (string) $data["avatar_file_map"];
				}
				break;

			// диалога наема и увольнения
			case CONVERSATION_TYPE_GROUP_HIRING:

				$output = [
					"name"          => (string) $data["name"],
					"members_count" => (int) $data["member_count"],
				];

				// добавляем к ответу avatar_file_key если он есть
				if (mb_strlen($data["avatar_file_map"]) > 0) {
					$output["avatar"]["file_map"] = (string) $data["avatar_file_map"];
				}
				break;

			case CONVERSATION_TYPE_GROUP_GENERAL:

				$output = [
					"name"          => (string) $data["name"],
					"members_count" => (int) $data["member_count"],
					"role"          => (string) self::getUserRole($data["role"]),
				];

				// добавляем к ответу avatar_file_key если он есть
				if (mb_strlen($data["avatar_file_map"]) > 0) {
					$output["avatar"]["file_map"] = (string) $data["avatar_file_map"];
				}
				break;

			// диалога наема и увольнения
			case CONVERSATION_TYPE_SINGLE_NOTES:

				$output = [
					"name" => (string) $data["name"],
				];

				// добавляем к ответу avatar_file_key если он есть
				if (mb_strlen($data["avatar_file_map"]) > 0) {
					$output["avatar"]["file_map"] = (string) $data["avatar_file_map"];
				}
				break;

			default:
				throw new ParseFatalException("conversation type is not available");
		}

		return $output;
	}

	/**
	 * последнее сообщение в left_menu & conversation
	 *
	 * @param array $last_message
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _lastMessage(array $last_message):array {

		$output = [
			"message_map"   => $last_message["message_map"],
			"sender_id"     => $last_message["sender_id"],
			"type"          => self::getConversationMessageOutputType($last_message["type"]),
			"message_index" => $last_message["message_index"],
		];

		// если сообщение не удалено то отдает еще и текст
		$output = self::_attachTextIfLastMessageIsNotDeleted($output, $last_message["type"], $last_message["text"]);

		// если тип файл -> распаковываем file_map и отдаем преобразованный тип
		$output = self::_attachFileTypeIfLastMessageIsFile($output, $last_message["type"], $last_message["file_map"]);

		// если тип файл -> добавляем имя файла
		$output = self::_attachFileNameIfLastMessageIsFile($output, $last_message["type"], $last_message["file_name"]);

		// если тип сообщения звонок -> добавляем call_map к сообщению
		$output = self::_attachCallMapIfLastMessageIsCall($output, $last_message["type"], $last_message["call_map"]);

		// если тип сообщения приглашение -> добавляем invite_map к сообщению
		$output = self::_attachInviteMapIfLastMessageIsInvite($output, $last_message["type"], $last_message["invite_map"]);

		// если тип сообщения репост/цитата -> добавляем количество репостнутых/процитированных сообщений
		$output = self::_attachRepostedMessageCountIfLastMessageIsRepostOrQuote($output, $last_message["type"], $last_message["message_count"]);

		// если тип сообщение с additional-полями -> добавляем данные additional-полей
		$output = self::_attachAdditionalDataIfLastMessageIsMessageWithAdditional($output, $last_message);

		// если тип сообщения конференция - добавляем поля для конференции
		$output = self::_attachConferenceIdIfLastMessageIsMediaConference($output, $last_message["type"], $last_message["data"]["conference_id"]);
		$output = self::_attachAcceptStatusIfLastMessageIsMediaConference($output, $last_message["type"], $last_message["data"]["conference_accept_status"]);

		return $output;
	}

	/**
	 * функция добавляет к last_message текст, если последнее сообщение не удалено
	 *
	 * @param array  $output
	 * @param int    $message_type
	 * @param string $message_text
	 *
	 * @return array
	 */
	protected static function _attachTextIfLastMessageIsNotDeleted(array $output, int $message_type, string $message_text):array {

		if ($message_type == CONVERSATION_MESSAGE_TYPE_DELETED) {
			return $output;
		}

		$output["text"] = $message_text;
		return $output;
	}

	/**
	 * функция добавляет к last_message тип файла, если последнее сообщение типа файл
	 *
	 * @param array  $output
	 * @param int    $message_type
	 * @param string $file_map
	 *
	 * @return array
	 */
	protected static function _attachFileTypeIfLastMessageIsFile(array $output, int $message_type, string $file_map):array {

		if ($message_type != CONVERSATION_MESSAGE_TYPE_FILE && $message_type != CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE) {
			return $output;
		}

		$output["file_type"] = self::_FILE_TYPE_SCHEMA[\CompassApp\Pack\File::getFileType($file_map)];
		return $output;
	}

	/**
	 * функция добавляет к last_message имя файла, если последнее сообщение типа файл
	 *
	 * @param array  $output
	 * @param int    $message_type
	 * @param string $file_name
	 *
	 * @return array
	 */
	protected static function _attachFileNameIfLastMessageIsFile(array $output, int $message_type, string $file_name):array {

		if ($message_type != CONVERSATION_MESSAGE_TYPE_FILE && $message_type != CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE) {
			return $output;
		}

		$output["file_name"] = $file_name;
		return $output;
	}

	/**
	 * функция добавляет к last_message call_map звонка, если последнее сообщение типа звонок
	 *
	 * @param array  $output
	 * @param int    $message_type
	 * @param string $call_map
	 *
	 * @return array
	 */
	protected static function _attachCallMapIfLastMessageIsCall(array $output, int $message_type, string $call_map):array {

		if ($message_type != CONVERSATION_MESSAGE_TYPE_CALL) {
			return $output;
		}

		$output["call_map"] = $call_map;
		return $output;
	}

	// функция добавляет к last_message conference_id конференции
	protected static function _attachConferenceIdIfLastMessageIsMediaConference(array $output, int $message_type, string $conference_id):array {

		if ($message_type != CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE) {
			return $output;
		}

		$output["data"]["conference_id"] = $conference_id;
		return $output;
	}

	// функция добавляет к last_message status конференции
	protected static function _attachAcceptStatusIfLastMessageIsMediaConference(array $output, int $message_type, string $conference_accept_status):array {

		if ($message_type != CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE) {
			return $output;
		}

		$output["data"]["conference_accept_status"] = $conference_accept_status;
		return $output;
	}

	/**
	 * функция добавляет к last_message invite_map, если последнее сообщение типа приглашение
	 *
	 * @param array  $output
	 * @param int    $message_type
	 * @param string $invite_map
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _attachInviteMapIfLastMessageIsInvite(array $output, int $message_type, string $invite_map):array {

		if ($message_type != CONVERSATION_MESSAGE_TYPE_INVITE) {
			return $output;
		}

		// у старых сообщений этого поля не будет
		// для них считаем все такие приглашения как приглашения в группы
		if ($invite_map === "") {

			$output["invite_type"] = "single_invite_to_group";
			return $output;
		}

		// получаем тип инвайта
		$output["invite_type"] = Type_Invite_Utils::getInviteType($invite_map);

		return $output;
	}

	/**
	 * функция добавляет к last_message количество репостнутых/процитированных сообщений, если последнее сообщение типа репост/цитата
	 *
	 * @param array $output
	 * @param int   $message_type
	 * @param int   $message_count
	 *
	 * @return array
	 */
	protected static function _attachRepostedMessageCountIfLastMessageIsRepostOrQuote(array $output, int $message_type, int $message_count):array {

		$message_type_list = [
			CONVERSATION_MESSAGE_TYPE_REPOST,
			CONVERSATION_MESSAGE_TYPE_THREAD_REPOST,
			CONVERSATION_MESSAGE_TYPE_QUOTE,
			CONVERSATION_MESSAGE_TYPE_MASS_QUOTE,
		];

		if (!in_array($message_type, $message_type_list)) {
			return $output;
		}

		$output["message_count"] = $message_count;
		return $output;
	}

	/**
	 * функция добавляет к last_message данные additional
	 *
	 * @param array $output
	 * @param array $last_message
	 *
	 * @return array
	 */
	protected static function _attachAdditionalDataIfLastMessageIsMessageWithAdditional(array $output, array $last_message):array {

		$output["receiver_id"]     = (int) $last_message["receiver_id"];
		$output["additional_type"] = $last_message["additional_type"];

		return $output;
	}

	/**
	 * возвращает тип сообщения для фронта на основе его свойства type
	 *
	 * @param int $message_type
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getConversationMessageOutputType(int $message_type):string {

		if (!isset(self::_CONVERSATION_MESSAGE_TYPE_SCHEMA[$message_type])) {

			throw new ParseFatalException("there is no format output for message type {$message_type}");
		}

		return self::_CONVERSATION_MESSAGE_TYPE_SCHEMA[$message_type];
	}

	/**
	 * получаем роль пользователя
	 *
	 * @param int $role
	 *
	 * @return string
	 */
	public static function getUserRole(int $role):string {

		return self::_CONVERSATION_ROLE_SCHEMA[$role];
	}

	/**
	 * получаем подтип диалога для формата
	 *
	 * @param int $conversation_type
	 *
	 * @return string
	 */
	public static function getConversationSubtype(int $conversation_type):string {

		return self::CONVERSATION_SUBTYPE_SCHEMA[$conversation_type];
	}

	/**
	 * приводит к формату Напоминание
	 */
	#[ArrayShape(["remind_id" => "int", "creator_user_id" => "int", "comment" => "string", "remind_at" => "int"])]
	public static function remind(int $remind_id, int $remind_at, int $creator_user_id, string $comment):array {

		return [
			"remind_id"       => (int) $remind_id,
			"creator_user_id" => (int) $creator_user_id,
			"comment"         => (string) $comment,
			"remind_at"       => (int) $remind_at,
		];
	}

	/**
	 * Список файлов чата
	 *
	 * @param Struct_Conversation_File[] $conversation_file_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function conversationFileList(array $conversation_file_list):array {

		$output = [];
		foreach ($conversation_file_list as $file) {

			if (!isset(Domain_Conversation_Entity_File_Main::FILE_CLIENT_SCHEMA[$file->file_type])) {
				throw new ParseFatalException("cant find required type");
			}

			$temp = [
				"id"         => (int) $file->row_id,
				"file_uuid"  => (string) $file->file_uuid,
				"file_map"   => (string) $file->file_map,
				"file_type"  => (string) Domain_Conversation_Entity_File_Main::FILE_CLIENT_SCHEMA[$file->file_type],
				"user_id"    => (int) $file->user_id,
				"created_at" => (int) $file->created_at,
				"data"       => [
					"parent_type" => Domain_Conversation_Entity_File_Main::PARENT_TYPE_TO_STRING_SCHEMA[$file->parent_type],
					"parent_map"  => (string) $file->parent_map,
					"message_map" => (string) $file->parent_message_map,
				],
			];

			$output[] = $temp;
		}
		return $output;
	}

	/**
	 * Список превью чата
	 *
	 * @param Struct_Db_CompanyConversation_ConversationPreview[] $conversation_preview_list
	 *
	 * @return array
	 */
	public static function conversationPreviewList(array $conversation_preview_list):array {

		$output = [];
		foreach ($conversation_preview_list as $preview) {

			foreach ($preview->link_list as $key => $link) {

				if (isset($link["original_link"])) {
					$preview->link_list[$key]["redirect_link"] = $link["original_link"];
				}
			}

			$temp     = [
				"user_id"                   => (int) $preview->user_id,
				"created_at"                => (int) $preview->created_at,
				"parent_message_created_at" => (int) $preview->parent_message_created_at,
				"preview_map"               => (string) $preview->preview_map,
				"link_list"                 => (array) $preview->link_list,
				"data"                      => [
					"parent_type" => (string) Domain_Conversation_Entity_Preview_Main::PARENT_TYPE_TO_STRING_SCHEMA[$preview->parent_type],
					"message_map" => (string) $preview->parent_message_map,
				],
			];
			$output[] = $temp;
		}
		return $output;
	}

	/**
	 * Форматирует список локаций поиска.
	 */
	public static function searchLocationList(array $search_location_list):array {

		$output = [];

		foreach ($search_location_list as $search_location) {

			$output[] = match ($search_location["type"]) {

				Domain_Search_Entity_Conversation_Location::API_LOCATION_TYPE => static::searchLocationListConversation($search_location),
				Domain_Search_Entity_Thread_Location::API_LOCATION_TYPE       => static::searchLocationListThread($search_location),
				default                                                       => throw new ParseFatalException("got unknown location")
			};
		}

		return $output;
	}

	/**
	 * Форматирует локацию поиска — диалог.
	 */
	public static function searchLocationListConversation(array $search_location):array {

		return [
			"type" => $search_location["type"],
			"data" => [
				"item"      => self::leftMenu($search_location["data"]["item"]),
				"hit_count" => $search_location["data"]["hit_count"],
				"hit_list"  => static::searchHitList($search_location["data"]["hit_list"]),
			],
		];
	}

	/**
	 * Форматирует локацию поиска — тред.
	 */
	public static function searchLocationListThread(array $search_location):array {

		return [
			"type" => $search_location["type"],
			"data" => [
				"item"      => \Compass\Thread\Apiv1_Format::threadMeta($search_location["data"]["item"]),
				"hit_count" => $search_location["data"]["hit_count"],
				"hit_list"  => static::searchHitList($search_location["data"]["hit_list"]),
			],

		];
	}

	/**
	 * Форматирует список локаций поиска.
	 */
	public static function searchHitList(array $search_hit_list):array {

		$output = [];

		foreach ($search_hit_list as $search_hit) {

			$output[] = match ($search_hit["type"]) {

				Domain_Search_Entity_ConversationMessage_Hit::API_HIT_TYPE => static::searchHitListConversationMessage($search_hit),
				Domain_Search_Entity_ThreadMessage_Hit::API_HIT_TYPE       => static::searchHitListThreadMessage($search_hit),
				default                                                    => throw new ParseFatalException("got unknown hit")
			};
		}

		return $output;
	}

	/**
	 * Форматирует локацию поиска — диалог.
	 */
	public static function searchHitListConversationMessage(array $search_hit):array {

		return [
			"type"                 => $search_hit["type"],
			"data"                 => [
				"item"                      => Apiv2_Format::conversationMessage($search_hit["data"]["item"]),
				"extra"                     => $search_hit["data"]["extra"],
				"previous_message_block_id" => $search_hit["data"]["previous_message_block_id"],
				"next_message_block_id"     => $search_hit["data"]["next_message_block_id"],
			],
			"nested_location_list" => static::searchLocationList($search_hit["nested_location_list"]),
		];
	}

	/**
	 * Форматирует локацию поиска — диалог.
	 */
	public static function searchHitListThreadMessage(array $search_hit):array {

		return [
			"type"                 => $search_hit["type"],
			"data"                 => [
				"item"                      => \Compass\Thread\Apiv1_Format::threadMessage($search_hit["data"]["item"]),
				"parent"                    => static::searchHitListThreadMessageParent($search_hit["data"]["parent"]),
				"extra"                     => $search_hit["data"]["extra"],
				"previous_message_block_id" => $search_hit["data"]["previous_message_block_id"],
				"next_message_block_id"     => $search_hit["data"]["next_message_block_id"],
			],
			"nested_location_list" => static::searchLocationList($search_hit["nested_location_list"]),
		];
	}

	/**
	 * Форматирует локацию поиска — диалог.
	 */
	public static function searchHitListThreadMessageParent(array $parent):array {

		return match ($parent["type"]) {

			// сообщение в диалоге
			Domain_Search_Entity_ConversationMessage_Hit::HIT_TYPE => [
				"type" => $parent["type"],
				"data" => [
					"item" => Apiv2_Format::conversationMessage($parent["data"]["item"]),
				],
			],

			// передан неизвестный родитель
			default                                                => throw new ParseFatalException("got unknown hit")
		};
	}

	/**
	 * получаем форматированный итем для списка сообщений
	 */
	#[ArrayShape(["conversation_map" => "string", "previous_block_id_list" => "array", "message_list" => "array", "next_block_id_list" => "array", "hidden_message_key_list" => "array"])]
	public static function feedMessages(string $conversation_map, array $message_list, array $next_block_id_list, array $previous_block_id_list, array $hidden_message_map_list = []):array {

		$formatted_message_list = [];
		foreach ($message_list as $message) {
			$formatted_message_list[] = self::conversationMessage($message);
		}

		$hidden_message_key_list = [];
		foreach ($hidden_message_map_list as $message_map) {
			$hidden_message_key_list[] = \CompassApp\Pack\Message\Conversation::doEncrypt($message_map);;
		}

		return [
			"conversation_map"        => (string) $conversation_map,
			"previous_block_id_list"  => (array) $previous_block_id_list,
			"message_list"            => (array) $formatted_message_list,
			"hidden_message_key_list" => (array) $hidden_message_key_list,
			"next_block_id_list"      => (array) $next_block_id_list,
		];
	}

	/**
	 * получаем форматированный итем для списка реакций
	 */
	#[ArrayShape(["conversation_map" => "string", "reaction_block_list" => "array"])]
	public static function feedReactions(string $conversation_map, array $reaction_list):array {

		return [
			"conversation_map"    => (string) $conversation_map,
			"reaction_block_list" => (array) self::reactionBlockList($reaction_list),
		];
	}

	/**
	 * получаем форматированный итем для списка тредов
	 */
	#[ArrayShape(["conversation_map" => "string", "thread_meta_list" => "array", "thread_menu_list" => "array"])]
	public static function feedThreads(string $conversation_map, array $thread_meta_list, array $thread_menu_list):array {

		return [
			"conversation_map" => (string) $conversation_map,
			"thread_meta_list" => (array) $thread_meta_list,
			"thread_menu_list" => (array) $thread_menu_list,
		];
	}

	/**
	 * получаем форматированный итем для метода conversations/invites/addBatching
	 */
	public static function addBatchingInvites(array $list_ok, array $list_error):array {

		return [
			"list_ok"    => (array) $list_ok,
			"list_error" => (array) $list_error,
		];
	}

	/**
	 * получаем форматированный итем для метода conversations/invites/addBatchingForGroups
	 */
	public static function addBatchingForGroupsInvites(int $is_sent, array $list_ok, array $list_error):array {

		return [
			"is_sent"    => (int) $is_sent,
			"list_ok"    => (array) $list_ok,
			"list_error" => (array) $list_error,
		];
	}
}
