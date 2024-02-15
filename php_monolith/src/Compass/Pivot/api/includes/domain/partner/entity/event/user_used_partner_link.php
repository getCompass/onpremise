<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о регистрации пользователя после посещения партнерской ссылки
 */
class Domain_Partner_Entity_Event_UserUsedPartnerLink extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.user_used_partner_link";

	/**
	 * Создаем событие
	 *
	 * @param int    $user_id
	 * @param string $source_type
	 * @param array  $source_extra
	 * @param int    $registered_at
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, string $link_url, int $registered_at):void {

		$params = [
			"user_id"       => $user_id,
			"link_url"      => $link_url,
			"registered_at" => $registered_at,
		];

		// отправляем в партнерское ядро
		self::_sendToPartner($params);
	}

}