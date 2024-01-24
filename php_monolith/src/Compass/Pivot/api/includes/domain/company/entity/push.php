<?php

namespace Compass\Pivot;

use JetBrains\PhpStorm\ArrayShape;

use Compass\Pivot\Domain_Push_Entity_Locale_Message as Message;
use Compass\Pivot\Domain_Push_Entity_Locale_Message_Title as Title;
use Compass\Pivot\Domain_Push_Entity_Locale_Message_Body as Body;

/**
 * класс для работы с обьектами пушей
 */
class Domain_Company_Entity_Push {

	// пуш по умолчанию для принятой заявки найма
	protected const _DEFAULT_CONFIRM_JOIN_REQUEST_BODY  = "Добро пожаловать! Нажмите, чтобы начать общение";
	protected const _DEFAULT_CONFIRM_JOIN_REQUEST_TYPE  = "confirm_join_request";

	// пуш по умолчанию для отклоненной заявки найма
	protected const _DEFAULT_REJECT_JOIN_REQUEST_BODY  = "Доступ в команду закрыт";
	protected const _DEFAULT_REJECT_JOIN_REQUEST_TYPE  = "reject_join_request";

	// создаем пуш-уведомление
	#[ArrayShape(["badge_inc_count" => "int", "push_type" => "int", "text_push" => "array"])]
	public static function makeConfirmPushData(int $company_id, int $inviter_user_id, string $company_name):array {

		// получаем данные о локализации пуша
		$title_localization = (new Title(Message::SPACE_ENTITY))->setType(Title::MESSAGE_CONFIRM_JOIN_REQUEST)->addArg($company_name)->getLocaleResult();
		$body_localization  = (new Body(Message::SPACE_ENTITY))->setType(Title::MESSAGE_CONFIRM_JOIN_REQUEST)->getLocaleResult();

		return [
			"badge_inc_count" => 0,
			"push_type"       => 1,
			"text_push"       => [
				"company_id"         => $company_id,
				"entity_type"        => self::_DEFAULT_CONFIRM_JOIN_REQUEST_TYPE,
				"sender_user_id"     => $inviter_user_id,
				"title"              => "Заявка в команду «{$company_name}» одобрена ✅",
				"title_localization" => $title_localization,
				"body"               => self::_DEFAULT_CONFIRM_JOIN_REQUEST_BODY,
				"body_localization"  => $body_localization,
			],
		];
	}

	// создаем пуш-уведомление
	#[ArrayShape(["badge_inc_count" => "int", "push_type" => "int", "text_push" => "array"])]
	public static function makeRejectedPushData(int $company_id, string $company_name):array {

		// получаем данные о локализации пуша
		$title_localization = (new Title(Message::SPACE_ENTITY))->setType(Title::MESSAGE_REJECT_JOIN_REQUEST)->addArg($company_name)->getLocaleResult();
		$body_localization  = (new Body(Message::SPACE_ENTITY))->setType(Title::MESSAGE_REJECT_JOIN_REQUEST)->getLocaleResult();

		return [
			"badge_inc_count" => 0,
			"push_type"       => 1,
			"text_push"       => [
				"company_id"         => $company_id,
				"entity_type"        => self::_DEFAULT_REJECT_JOIN_REQUEST_TYPE,
				"title"              => "Заявка в команду «{$company_name}» отклонена",
				"title_localization" => $title_localization,
				"body"               => self::_DEFAULT_REJECT_JOIN_REQUEST_BODY,
				"body_localization"  => $body_localization,
			],
		];
	}
}