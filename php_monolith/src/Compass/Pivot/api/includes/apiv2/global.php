<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для технических методов клиента
 */
class Apiv2_Global extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getStartData",
		"detachVoipToken",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод получает служебные данные для инициализации клиента: конфиги, файлы, словари
	 *
	 * @throws ParamException
	 * @throws ParamException
	 * @throws cs_PlatformNotFound|\ParamException
	 */
	public function getStartData():array {

		$need_data_list = $this->post(\Formatter::TYPE_ARRAY, "need_data_list", []);

		try {
			$start_data = Domain_System_Scenario_Api::getStartDataV2($this->user_id, $need_data_list);
		} catch (Domain_System_Exception_EmojiKeywordsNotFound|Domain_System_Exception_VideoOnboardingNotFound) {
			throw new ParamException("config not found");
		} catch (cs_IncorrectVersion) {
			throw new ParamException("incorrect version number");
		} catch (\BaseFrame\Exception\Domain\LocaleNotFound) {
			throw new ParamException("incorrect locale");
		}

		return $this->ok(Apiv2_Format::formatStartData($start_data));
	}

	/**
	 * Открепить voip токен
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \returnException
	 */
	public function detachVoipToken():array {

		$token = $this->post(\Formatter::TYPE_STRING, "token");

		// инкрементим блокировку
		Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::DETACH_VOIP_TOKEN);

		Domain_Notifications_Scenario_Api::detachVoipToken($token);

		return $this->ok();
	}
}