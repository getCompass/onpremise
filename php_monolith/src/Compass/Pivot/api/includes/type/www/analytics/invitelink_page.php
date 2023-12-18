<?php

namespace Compass\Pivot;

/**
 * Класс для основной работы по сохранения аналитики сайта страницы-приглашения
 */
class Type_Www_Analytics_InvitelinkPage {

	protected const _EVENT_KEY = "invitelink_page_analytics";

	// пишем аналитику в collector
	public static function save(int $page_type, string $user_agent):void {

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"uniq_key"   => sha1($page_type . $user_agent . timeUs()),
			"page_type"  => $page_type,
			"user_agent" => $user_agent,
			"event_time" => time(),
		]);
	}
}