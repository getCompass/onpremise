<?php

namespace Compass\Pivot;

/**
 * контроллер для работы с системными штуками в компании
 */
class Socket_Company_System extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"startHibernate",
	];

	/**
	 * Оптравляем компанию в гибернацию
	 */
	public function startHibernate():array {

		try {

			Domain_Company_Scenario_Socket::startHibernate($this->company_id);
		} catch (cs_CompanyIsNotActive) {

			// тут в обоих случаях ок)
			return $this->ok();
		}

		return $this->ok();
	}
}
