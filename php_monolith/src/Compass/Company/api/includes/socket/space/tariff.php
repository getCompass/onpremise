<?php

namespace Compass\Company;

/**
 * контроллер для работы с тарифом пространства
 */
class Socket_Space_Tariff extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"publishAnnouncement",
		"disableAnnouncements",
		"checkIsUnblocked",
	];

	/**
	 * Публикуем анонс в пространстве
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function publishAnnouncement():array {

		$announcement_type = $this->post(\Formatter::TYPE_INT, "announcement_type");
		$data              = $this->post(\Formatter::TYPE_ARRAY, "data");

		Domain_Space_Scenario_Socket::publishAnnouncement($announcement_type, $data);

		return $this->ok();
	}

	/**
	 * Убираем анонсы в пространстве
	 *
	 * @return array
	 */
	public function disableAnnouncements():array {

		Domain_Space_Scenario_Socket::disableAnnouncements();

		return $this->ok();
	}

	/**
	 * Проверяем, разблокировано ли пространство
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public function checkIsUnblocked():array {

		$is_unblocked = Domain_Space_Scenario_Socket::checkIsUnblocked();

		return $this->ok([
			"is_unblocked" => (int) $is_unblocked,
		]);
	}
}