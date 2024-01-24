<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для API методов группы Talking
 */
class Apiv1_Talking extends \BaseFrame\Controller\Api {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"getConnection",
	];

	/**
	 * получить параметры подключения к go_sender
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getConnection():array {

		// получаем и возвращаем параметры подключения
		try {
			[$token, $url] = Domain_User_Scenario_Api::getConnection($this->user_id);
		} catch (BusFatalException) {
			throw new ParamException("Failed to connect");
		} catch (cs_PlatformNotFound) {
			throw new ParamException(__METHOD__ . ": unsupported platform");
		}

		return $this->ok([
			"token" => (string) $token,
			"url"   => (string) $url,
		]);
	}
}