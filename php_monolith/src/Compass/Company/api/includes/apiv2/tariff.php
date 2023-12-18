<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\CaseException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Контроллер для методов тарифов пространства
 */
class Apiv2_Tariff extends \BaseFrame\Controller\Api {

	// доступные методы контроллера
	public const ALLOW_METHODS = [
		"get",
		"onShowcaseOpened",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	/**
	 * Получить тариф пространства
	 */
	public function get():array {

		$plan_list = $this->post(\Formatter::TYPE_ARRAY, "plan_list");

		try {
			$tariff = Domain_SpaceTariff_Scenario_Api::get($this->role, $plan_list);
		} catch (\CompassApp\Domain\Member\Exception\IsNotAdministrator) {
			throw new CaseException(2235001, "User is not a company owner");
		}

		return $this->ok([
			"tariff" => (object) $tariff,
		]);
	}

	/**
	 * открыт попап тарифов пространства
	 *
	 * @throws CaseException
	 */
	public function onShowcaseOpened():array {

		try {
			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SHOWCASE_OPENED);
		} catch (\BaseFrame\Exception\Request\BlockException) {
			return $this->ok();
		}

		try {
			Domain_SpaceTariff_Scenario_Api::onShowcaseOpened($this->user_id, $this->role);
		} catch (\CompassApp\Domain\Member\Exception\IsNotAdministrator) {
			throw new CaseException(2235001, "User is not a company owner");
		}

		return $this->ok();
	}
}
