<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\PaymentRequiredException;

/**
 * контроллер, отвечающий за работу диалогов
 */
class Apiv2_Conversations extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"get",
	];

	############################	##############################
	# region диалоги
	##########################################################

	/**
	 * Метод для получения списка диалогов
	 * @return array
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @long
	 */
	public function get():array {

		$conversation_key_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_key_list");

		if (count($conversation_key_list) > 100) {
			throw new ParamException("Passed conversation_key_list biggest than max");
		}

		try {

			$conversation_map_list = $this->_tryDecryptConversationKeyList($conversation_key_list);
			[$prepared_conversation_meta_list, $not_allowed_conversation_map_list, $users] = Domain_Conversation_Scenario_Apiv2::get(
				$this->user_id, $conversation_map_list, $this->extra["space"]["is_restricted_access"]
			);
		} catch (cs_IncorrectConversationMapList) {
			throw new ParamException("Passed conversation_key_list biggest than max");
		} catch (\cs_DecryptHasFailed|ParamException) {
			throw new ParamException("You passed invalid conversation key");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->action->users($users);

		$output = [];
		foreach ($prepared_conversation_meta_list as $conversation_meta) {
			$output[] = Apiv2_Format::conversationMeta($conversation_meta);
		}

		$not_allowed_conversation_key_list = [];
		foreach ($not_allowed_conversation_map_list as $conversation_map) {
			$not_allowed_conversation_key_list[] = \CompassApp\Pack\Conversation::doEncrypt($conversation_map);
		}
		return $this->ok([
			"conversation_meta_list"            => (array) $output,
			"not_allowed_conversation_key_list" => $not_allowed_conversation_key_list,
		]);
	}

	/**
	 * декриптим список диалогов
	 *
	 * @throws ParamException
	 * @throws \paramException
	 */
	protected function _tryDecryptConversationKeyList(array $conversation_key_list):array {

		$conversation_map_list = [];
		foreach ($conversation_key_list as $conversation_key) {
			$conversation_map_list[] = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		}

		return $conversation_map_list;
	}
}
