<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Контроллер для методов изменения настроек компании
 */
class Apiv1_Company_Config extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"setPushBodyDisplay",
		"getPushBodyDisplay",
		"setExtendedEmployeeCard",
		"getExtendedEmployeeCard",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"setPushBodyDisplay",
		"setExtendedEmployeeCard",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => [
			"setPushBodyDisplay",
			"setExtendedEmployeeCard",
		],
	];

	/**
	 * Изменение настройки отображение сообщения в пуше
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \queryException
	 */
	public function setPushBodyDisplay():array {

		$value = $this->post(\Formatter::TYPE_INT, "is_display_push_body");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PUSH_BODY_DISPLAY);

		if ($value == 1) {
			Gateway_Bus_CollectorAgent::init()->inc("row50");
		} else {
			Gateway_Bus_CollectorAgent::init()->inc("row51");
		}

		try {

			Gateway_Bus_CollectorAgent::init()->inc("row52");
			Domain_Company_Scenario_Api::setPushBodyDisplayConfig($this->role, $this->permissions, $value);
		} catch (cs_InvalidConfigValue) {

			Gateway_Bus_CollectorAgent::init()->inc("row53");
			return $this->error(660, "Value is not valid");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			Gateway_Bus_CollectorAgent::init()->inc("row54");
			return $this->error(655, "User is not a company owner");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row55");
		return $this->ok([
			"is_display_push_body" => (int) $value,
		]);
	}

	/**
	 * Получение настройки отображения сообщения в пуше
	 */
	public function getPushBodyDisplay():array {

		$value = Domain_Company_Scenario_Api::getPushBodyDisplayConfig();

		return $this->ok([
			"is_display_push_body" => (int) $value,
		]);
	}

	/**
	 * Установка настройки для карточки (базовая/расширенная)
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function setExtendedEmployeeCard():array {

		$is_enabled = $this->post(\Formatter::TYPE_INT, "is_enabled");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::EXTENDED_EMPLOYEE_CARD);

		if ($is_enabled == 1) {
			Gateway_Bus_CollectorAgent::init()->inc("row56");
		} else {
			Gateway_Bus_CollectorAgent::init()->inc("row57");
		}

		try {

			Gateway_Bus_CollectorAgent::init()->inc("row58");
			Domain_Company_Scenario_Api::setExtendedEmployeeCard($this->user_id, $this->role, $this->permissions, $is_enabled);
		} catch (cs_InvalidConfigValue) {

			Gateway_Bus_CollectorAgent::init()->inc("row59");
			throw new ParamException("invalid config value");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			Gateway_Bus_CollectorAgent::init()->inc("row60");
			return $this->error(655, "User is not a company owner");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row61");
		return $this->ok();
	}

	/**
	 * Получение значения настройки расширенной карточки
	 *
	 * @return array
	 */
	public function getExtendedEmployeeCard():array {

		$is_enabled = Domain_Company_Scenario_Api::getExtendedEmployeeCard();

		return $this->ok([
			"is_enabled" => (int) $is_enabled,
		]);
	}
}