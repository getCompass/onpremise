<?php

namespace Compass\Company;

use JetBrains\PhpStorm\ArrayShape;

use Compass\Company\Domain_Push_Entity_Locale_Message as Message;
use Compass\Company\Domain_Push_Entity_Locale_Message_Title as Title;
use Compass\Company\Domain_Push_Entity_Locale_Message_Body as Body;

/**
 * класс для работы с обьектами пушей
 */
class Domain_Space_Entity_Push {

	protected const _TEXT_PUSH_TYPE = 1;

	protected const _CATEGORY = "SPACE_MEMBER_NOTIFICATION";

	// пуш по умолчанию для активного участника
	protected const _DEFAULT_ACTIVE_MEMBER_NOTIFICATION_TYPE = "active_member";

	// пуш по умолчанию для новой заявки
	protected const _DEFAULT_JOIN_REQUEST_NOTIFICATION_TYPE = "join_request";

	// пуш по умолчанию для нового гостя
	protected const _DEFAULT_GUEST_MEMBER_NOTIFICATION_TYPE = "guest_member";

	// создаем пуш-уведомление вступления участника в компанию
	#[ArrayShape(["badge_inc_count" => "int", "event_type" => "int", "push_type" => "int", "text_push" => "array"])]
	public static function makeActiveMemberPushData(string $action_user_full_name, string $company_name):array {

		// получаем данные о локализации пуша
		$title_localization = (new Title(Message::SPACE_ENTITY))->setType(Title::MESSAGE_ACTIVE_MEMBER)->addArg($company_name)->getLocaleResult();
		$body_localization  = (new Body(Message::SPACE_ENTITY))->setType(Body::MESSAGE_ACTIVE_MEMBER)->addArg($action_user_full_name)->getLocaleResult();

		return [
			"badge_inc_count" => 0,
			"push_type"       => self::_TEXT_PUSH_TYPE,
			"event_type"      => EVENT_TYPE_MEMBER_NOTIFICATION_MASK,
			"text_push"       => [
				"category"           => self::_CATEGORY,
				"entity_type"        => self::_DEFAULT_ACTIVE_MEMBER_NOTIFICATION_TYPE,
				"company_id"         => COMPANY_ID,
				"title"              => "Новый участник в «{$company_name}»",
				"title_localization" => $title_localization,
				"body"               => "{$action_user_full_name} присоединился к команде",
				"body_localization"  => $body_localization,
			],
		];
	}

	// создаем пуш-уведомление заявки на вступление в компанию
	#[ArrayShape(["badge_inc_count" => "int", "event_type" => "int", "push_type" => "int", "text_push" => "array"])]
	public static function makeJoinRequestPushData(string $action_user_full_name, string $company_name, int $join_request_id):array {

		if (!NEED_SEND_JOIN_REQUEST_PUSH) {
			return [];
		}

		// получаем данные о локализации пуша
		$title_localization = (new Title(Message::SPACE_ENTITY))->setType(Title::MESSAGE_JOIN_REQUEST)->addArg($company_name)->getLocaleResult();
		$body_localization  = (new Body(Message::SPACE_ENTITY))->setType(Body::MESSAGE_JOIN_REQUEST)->addArg($action_user_full_name)->getLocaleResult();

		return [
			"badge_inc_count" => 0,
			"push_type"       => self::_TEXT_PUSH_TYPE,
			"event_type"      => EVENT_TYPE_MEMBER_NOTIFICATION_MASK,
			"text_push"       => [
				"category"           => self::_CATEGORY,
				"entity_type"        => self::_DEFAULT_JOIN_REQUEST_NOTIFICATION_TYPE,
				"company_id"         => COMPANY_ID,
				"join_request_id"    => $join_request_id,
				"title"              => "Новая заявка в «{$company_name}»",
				"title_localization" => $title_localization,
				"body"               => "{$action_user_full_name} оставил заявку на вступление в команду",
				"body_localization"  => $body_localization,
			],
		];
	}

	// создаем пуш-уведомление вступления гостя в компанию
	#[ArrayShape(["badge_inc_count" => "int", "event_type" => "int", "push_type" => "int", "text_push" => "array"])]
	public static function makeGuestMemberPushData(string $action_user_full_name, string $company_name):array {

		if (!NEED_SEND_GUEST_MEMBER_PUSH) {
			return [];
		}

		// получаем данные о локализации пуша
		$title_localization = (new Title(Message::SPACE_ENTITY))->setType(Title::MESSAGE_GUEST_MEMBER)->addArg($company_name)->getLocaleResult();
		$body_localization  = (new Body(Message::SPACE_ENTITY))->setType(Body::MESSAGE_GUEST_MEMBER)->addArg($action_user_full_name)->getLocaleResult();

		return [
			"badge_inc_count" => 0,
			"push_type"       => self::_TEXT_PUSH_TYPE,
			"event_type"      => EVENT_TYPE_MEMBER_NOTIFICATION_MASK,
			"text_push"       => [
				"category"           => self::_CATEGORY,
				"entity_type"        => self::_DEFAULT_GUEST_MEMBER_NOTIFICATION_TYPE,
				"company_id"         => COMPANY_ID,
				"title"              => "Новый гость в «{$company_name}»",
				"title_localization" => $title_localization,
				"body"               => "{$action_user_full_name} присоединился к команде",
				"body_localization"  => $body_localization,
			],
		];
	}
}