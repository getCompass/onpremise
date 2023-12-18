<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Методы для работы с профилями пользователей через веб-сайт on-premise решений.
 */
class Onpremiseweb_Profile extends \BaseFrame\Controller\Api {

	public const ECODE_USER_BAD_NAME = 1708020;

	public const ALLOW_METHODS = [
		"set",
	];

	/**
	 * Метод установки имени пользователя через веб-сайт.
	 */
	public function set():array {

		$name = $this->post(\Formatter::TYPE_STRING, "name");

		// оставим проверку на всякий случай
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SET);

		try {
			Domain_User_Scenario_Api::setProfile($this->user_id, $name, false);
		} catch (\cs_InvalidProfileName) {
			return $this->error(static::ECODE_USER_BAD_NAME, "invalid name");
		} catch (cs_InvalidAvatarFileMap|cs_FileIsNotImage|Domain_User_Exception_AvatarIsDeleted|\cs_RowIsEmpty) {
			throw new ReturnFatalException("unexpected error");
		}

		return $this->ok();
	}
}