<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Структура данных события пользователь присоединился к компании.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserCompany_UserJoinedCompany extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var int способ присоединения к компании */
	public int $entry_type;

	/** @var int кто пригласил в компанию */
	public int $company_inviter_user_id;

	/** @var int кто одобрил вступление в компанию */
	public int $approved_by_user_id;

	/** @var string название компании */
	public string $company_name;

	/** @var array заявка на найм */
	public array $hiring_request;

	/** @var string язык пользователя */
	public string $locale;

	/** @var bool был ли пользователь в компании ранее */
	public bool $is_user_already_was_in_company;

	/** @var bool нужно ли создавать диалог с пользователем в intercom */
	public bool $is_need_create_intercom_conversation;

	/** @var string ip пользователя */
	public string $ip;

	/** @var string user_agent пользователя */
	public string $user_agent;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $user_id
	 * @param array  $hiring_request
	 * @param int    $entry_type
	 * @param int    $company_inviter_user_id
	 * @param string $company_name
	 * @param string $locale
	 * @param int    $approved_by_user_id
	 * @param bool   $is_user_already_was_in_company
	 * @param bool   $is_need_create_intercom_conversation
	 * @param string $ip
	 * @param string $user_agent
	 *
	 * @return static
	 * @throws ParseFatalException
	 */
	public static function build(int    $user_id, array $hiring_request, int $entry_type, int $company_inviter_user_id, string $company_name, string $locale,
					     int    $approved_by_user_id = 0, bool $is_user_already_was_in_company = false, bool $is_need_create_intercom_conversation = false,
					     string $ip = "", string $user_agent = ""):static {

		return new static([
			"user_id"                              => $user_id,
			"entry_type"                           => $entry_type,
			"company_inviter_user_id"              => $company_inviter_user_id,
			"approved_by_user_id"                  => $approved_by_user_id,
			"company_name"                         => $company_name,
			"hiring_request"                       => $hiring_request,
			"locale"                               => $locale,
			"is_user_already_was_in_company"       => $is_user_already_was_in_company,
			"is_need_create_intercom_conversation" => $is_need_create_intercom_conversation,
			"ip"                                   => $ip,
			"user_agent"                           => $user_agent,
		]);
	}
}
