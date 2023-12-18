<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Контроллер сокет методов для взаимодействия с
 * данными пользователя между pivot сервером и компаниями
 */
class Socket_Notifications extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"clearTokenList",
	];

	/**
	 * Метод для очистки всех токенов пользователя
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function clearTokenList():array {

		$user_id        = $this->post(\Formatter::TYPE_INT, "user_id");
		$device_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "device_id_list", []);
		$token          = $this->post(\Formatter::TYPE_STRING, "token");

		Domain_User_Action_Notifications_ClearTokenList::do($user_id, $device_id_list, $this->company_id, $token);

		return $this->ok();
	}
}