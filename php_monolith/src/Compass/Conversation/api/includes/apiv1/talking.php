<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\System\Company;

/**
 * класс для API методов группы Talking
 */
class Apiv1_Talking extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getConnection",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	// получить параметры подключения к go_sender
	public function getConnection():array {

		Gateway_Bus_Statholder::inc("messages", "row822");

		// получаем device_id и платформу клиента
		$device_id = getDeviceId();
		$platform  = Type_Api_Platform::getPlatform();
		$token     = COMPANY_ID . ":" . generateUUID();

		// получаем и возвращаем параметры подключения
		try {
			Gateway_Bus_Sender::setToken($this->user_id, $token, $device_id, $platform);
		} catch (cs_TalkingBadResponse) {

			Gateway_Bus_Statholder::inc("messages", "row820");
			throw new ParamException("Failed to connect");
		}

		Gateway_Bus_Statholder::inc("messages", "row821");
		return $this->ok([
			"token" => (string) $token,
			"url"   => (string) PUBLIC_WEBSOCKET_DOMINO . "?a=" . Company::getServicePostFix() . "&b=" . Company::getWsServicePort(),
		]);
	}
}