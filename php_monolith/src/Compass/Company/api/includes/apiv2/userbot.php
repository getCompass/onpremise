<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Request\CaseException;

/**
 * контроллер для работы с пользовательским ботом
 */
class Apiv2_Userbot extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"getCard",
		"show",
		"getUserRel",
	];

	/**
	 * Метод для получения карточки бота
	 *
	 * @throws CaseException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \returnException
	 */
	public function getCard():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		// получаем данные по боту
		try {
			[$userbot, $single_conversation] = Domain_Userbot_Scenario_Api::getCard($this->user_id, $userbot_id);
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "userbot not found");
		}

		$this->action->users([$userbot->user_id]);

		return $this->ok([
			"userbot"             => (object) Apiv2_Format::userbot($userbot),
			"single_conversation" => (object) Apiv2_Format::singleConversation($single_conversation),
		]);
	}

	/**
	 * метод для получения данных ботов
	 *
	 * @throws CaseException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function show():array {

		$userbot_id_list = $this->post(\Formatter::TYPE_ARRAY, "userbot_id_list");

		try {
			$userbot_list = Domain_Userbot_Scenario_Api::show($this->user_id, $userbot_id_list);
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "userbot not found");
		}

		return $this->ok([
			"userbot_list" => (array) $userbot_list,
		]);
	}

	/**
	 * получаем связь пользователя и бота
	 */
	public function getUserRel():array {

		$batch_user_list = $this->post(\Formatter::TYPE_ARRAY, "batch_user_list");

		try {

			$userbot_user_rel_list = Domain_Userbot_Scenario_Api::getUserRel($batch_user_list);
		} catch (cs_WrongSignature) {
			throw new \BaseFrame\Exception\Request\ParamException("wrong params");
		}

		return $this->ok([
			"userbot_user_rel_list" => (array) $userbot_user_rel_list,
		]);
	}
}