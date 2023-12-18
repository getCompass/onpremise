<?php

namespace Compass\Company;

/**
 * Событие — пользователь присоединился к компании.
 *
 * @event_category user_company
 * @event_name     user_joined_company
 */
class Type_Event_UserCompany_UserJoinedCompany {

	/** @var string тип события */
	public const EVENT_TYPE = "user_company.user_joined_company";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param int    $user_id
	 * @param array  $hiring_request
	 * @param int    $entry_type
	 * @param int    $company_inviter_user_id
	 * @param string $locale
	 * @param int    $approved_by_user_id
	 * @param bool   $is_user_already_was_in_company
	 * @param bool   $is_need_create_intercom_conversation
	 * @param string $ip
	 * @param string $user_agent
	 *
	 * @return Struct_Event_Base
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function create(int  $user_id, array $hiring_request, int $entry_type, int $company_inviter_user_id, string $locale, int $approved_by_user_id,
						bool $is_user_already_was_in_company, bool $is_need_create_intercom_conversation, string $ip, string $user_agent):Struct_Event_Base {

		// получаем название компании
		$company_name = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];

		$event_data = Struct_Event_UserCompany_UserJoinedCompany::build(
			$user_id, $hiring_request, $entry_type, $company_inviter_user_id, $company_name, $locale, $approved_by_user_id,
			$is_user_already_was_in_company, $is_need_create_intercom_conversation, $ip, $user_agent
		);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @param array $event
	 *
	 * @return Struct_Event_UserCompany_UserJoinedCompany
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function parse(array $event):Struct_Event_UserCompany_UserJoinedCompany {

		return Struct_Event_UserCompany_UserJoinedCompany::build(...$event["event_data"]);
	}
}
