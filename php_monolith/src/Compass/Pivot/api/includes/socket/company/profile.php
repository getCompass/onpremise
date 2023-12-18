<?php

namespace Compass\Pivot;

/**
 * контроллер для работы с профилем компании
 */
class Socket_Company_Profile extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"setName",
		"setAvatar",
		"setBaseInfo",
	];

	/**
	 * Изменить имя компании
	 */
	public function setName():array {

		$name = $this->post(\Formatter::TYPE_STRING, "name");

		Domain_Company_Scenario_Socket::setName($this->company_id, $name);

		return $this->ok();
	}

	/**
	 * Изменить цвет аватарки компании
	 */
	public function setAvatar():array {

		$avatar_color_id = $this->post(\Formatter::TYPE_INT, "avatar_color_id");

		Domain_Company_Scenario_Socket::setAvatar($this->company_id, $avatar_color_id);

		return $this->ok();
	}

	/**
	 * Изменить основные данные профиля компании
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException|cs_CompanyNotExist
	 */
	public function setBaseInfo():array {

		$name            = $this->post(\Formatter::TYPE_STRING, "name", false);
		$avatar_color_id = $this->post(\Formatter::TYPE_INT, "avatar_color_id", false);

		[$name, $avatar_color_id] = Domain_Company_Scenario_Socket::setBaseInfo($this->company_id, $name, $avatar_color_id);

		return $this->ok([
			"name"            => (string) $name,
			"avatar_color_id" => (int) $avatar_color_id,
		]);
	}
}
