<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для методов изменения настроек компании
 */
class Socket_Company_Config extends \BaseFrame\Controller\Socket {

	const ALLOW_METHODS = [
		"setExtendedEmployeeCard",
		"getConfigByKey",
	];

	/**
	 * Установка настройки для карточки (базовая/расширенная)
	 *
	 * @throws paramException
	 * @throws \parseException
	 */
	public function setExtendedEmployeeCard():array {

		$is_enabled = $this->post(\Formatter::TYPE_INT, "is_enabled");

		try {
			Domain_Company_Scenario_Socket::setExtendedEmployeeCard($this->user_id, $is_enabled);
		} catch (cs_InvalidConfigValue) {
			throw new ParamException("invalid config value");
		}

		return $this->ok();
	}

	/**
	 * Получить конфиг компании по ключу
	 *
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getConfigByKey():array {

		$key = $this->post(\Formatter::TYPE_STRING, "key");

		$config = Type_Company_Config::init()->get($key);

		return $this->ok([
			"config" => (array) $config,
		]);
	}
}
