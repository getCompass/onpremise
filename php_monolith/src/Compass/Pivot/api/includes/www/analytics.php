<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * группа методов для сбора аналитики с внешних сервисов
 */
class Www_Analytics extends \BaseFrame\Controller\Www {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"saveInviteLinkPage",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для сохранения информации о ссылке-приглашении
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \paramException
	 */
	public function saveInviteLinkPage():array {

		$page_type = $this->post(\Formatter::TYPE_INT, "page_type");

		try {
			Domain_Www_Scenario_Api::saveAnalyticsInviteLinkPage($page_type);
		} catch (cs_IncorrectJoinLinkPageType) {
			throw new ParamException("Not available page_type");
		}

		return $this->ok();
	}
}