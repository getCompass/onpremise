<?php

namespace Compass\Pivot;

/**
 * контроллер для работы с анонсами
 */
class Apiv1_Announcement extends \BaseFrame\Controller\Api {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"getAuthorizationToken",
	];

	/**
	 * Определяет параметры подключения к php_announcement.
	 * Возвращает action для подключения к анонсам, если все хорошо и можно подключаться.
	 *
	 * @throws \paramException
	 */
	public function getAuthorizationToken():array {

		// получаем и возвращаем параметры подключения
		$authorization_token = Domain_User_Scenario_Api::getAnnouncementAuthorizationToken($this->user_id, getDeviceId());
		if (mb_strlen($authorization_token) > 0) {
			$this->action->announcementConnect($authorization_token);
		}
		return $this->ok();
	}
}