<?php

namespace Compass\Company;

use BaseFrame\ApiGateway\ScopePermission;

/**
 * Методы api второй версии для работы с уведомлениями.
 */
class Apiv2_Notifications extends \BaseFrame\Controller\Api
{
	// зона ответственности API токена
	public const API_SCOPE = ScopePermission::SCOPE_NOTIFICATIONS;

	// методы на чтение
	public const READ_METHOD_LIST = [];

	// методы на запись
	public const WRITE_METHOD_LIST = [
		"addDevice",
	];

	// разрешенные методы
	public const ALLOW_METHODS = [
		"addDevice",
	];

	/**
	 * Метод добавляет текущее устройство в список известных для отправки пушей.
	 * Нужен для актуализации и синхронизации данных с пивотом, если у пользователя вдруг смениться id устройства.
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 */
	public function addDevice(): array
	{

		// блокируем за превышенное число вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_ADDDEVICE);

		// фиксируем ивент как замьюченный
		Domain_Notifications_Scenario_Api::addDevice($this->user_id, getDeviceId());
		return $this->ok();
	}
}
