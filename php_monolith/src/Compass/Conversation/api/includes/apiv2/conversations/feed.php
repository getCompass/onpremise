<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\PaymentRequiredException;

/**
 * контроллер, отвечающий за подгрузку ленты сообщений
 */
class Apiv2_Conversations_Feed extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getMessages",
		"getReactions",
		"getThreads",
		"readMessage",
		"getBatchingMessages",
		"getBatchingReactions",
		"getBatchingThreads",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"readMessage",
	];

	##########################################################
	# region диалоги
	##########################################################

	/**
	 * Метод для получения сообщений из диалога
	 *
	 * @return array
	 * @throws ParamException
	 * @throws PaymentRequiredException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws CaseException
	 */
	public function getMessages():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$block_id_list    = $this->post("?ai", "block_id_list", []);
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		try {

			[$previous_block_id_list, $message_list, $next_block_id_list, $users] = Domain_Conversation_Feed_Scenario_Api::getMessages(
				$this->user_id, $conversation_map, $block_id_list, $this->extra["space"]["is_restricted_access"]
			);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not conversation member");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->action->users($users);

		$output = [];
		foreach ($message_list as $message) {
			$output[] = Apiv2_Format::conversationMessage($message);
		}
		return $this->ok([
			"previous_block_id_list" => (array) $previous_block_id_list,
			"message_list"           => (array) $output,
			"next_block_id_list"     => (array) $next_block_id_list,
		]);
	}

	/**
	 * Метод для получения батчингом сообщений для списка диалогов
	 *
	 * @return array
	 * @throws ParamException
	 * @throws PaymentRequiredException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws CaseException
	 */
	public function getBatchingMessages():array {

		$batch_message_list = $this->post("?a", "batch_message_list");

		$filtered_batch_message_list = $this->_prepareParamBatchList($batch_message_list);

		try {
			[$feed_message_list, $users] = Domain_Conversation_Feed_Scenario_Api::getBatchingMessages(
				$this->user_id, $filtered_batch_message_list, $this->extra["space"]["is_restricted_access"]
			);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not conversation member");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->action->users($users);

		return $this->ok([
			"feed_message_list" => (array) $feed_message_list,
		]);
	}

	/**
	 * Метод для получения реакций из диалога
	 *
	 * @return array
	 * @throws ParamException
	 * @throws PaymentRequiredException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws CaseException
	 */
	public function getReactions():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$block_id_list    = $this->post("?ai", "block_id_list", []);
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		try {
			[$reaction_block_list, $users] = Domain_Conversation_Feed_Scenario_Api::getReactions(
				$this->user_id, $conversation_map, $block_id_list, $this->extra["space"]["is_restricted_access"]);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not conversation member");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->action->users($users);

		$reaction_block_list = Apiv2_Format::reactionBlockList($reaction_block_list);

		return $this->ok([
			"reaction_block_list" => (array) $reaction_block_list,
		]);
	}

	/**
	 * Метод для получения батчингом реакций для списка диалогов
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws PaymentRequiredException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws CaseException
	 */
	public function getBatchingReactions():array {

		$batch_reaction_list = $this->post("?a", "batch_reaction_list");

		$filtered_batch_reaction_list = $this->_prepareParamBatchList($batch_reaction_list);

		try {

			[$feed_reaction_list, $users] = Domain_Conversation_Feed_Scenario_Api::getBatchingReactions(
				$this->user_id, $filtered_batch_reaction_list, $this->extra["space"]["is_restricted_access"]
			);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not conversation member");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->action->users($users);

		return $this->ok([
			"feed_reaction_list" => (array) $feed_reaction_list,
		]);
	}

	/**
	 * Метод для получаения тредов из диалога
	 *
	 * @return array
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws CaseException
	 */
	public function getThreads():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$block_id_list    = $this->post("?ai", "block_id_list", []);
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		try {
			[$thread_meta_list, $thread_menu_list, $users] = Domain_Conversation_Feed_Scenario_Api::getThreads(
				$this->user_id, $conversation_map, $block_id_list, $this->extra["space"]["is_restricted_access"]);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not conversation member");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->action->users($users);

		return $this->ok([
			"thread_meta_list" => (array) $thread_meta_list,
			"thread_menu_list" => (array) $thread_menu_list,
		]);
	}

	/**
	 * Метод для получения батчингом тредов для списка диалогов
	 *
	 * @return array
	 * @throws ParamException
	 * @throws PaymentRequiredException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws CaseException
	 */
	public function getBatchingThreads():array {

		$batch_thread_list = $this->post("?a", "batch_thread_list");

		$filtered_batch_thread_list = $this->_prepareParamBatchList($batch_thread_list);

		try {

			[$feed_batching_list, $users] = Domain_Conversation_Feed_Scenario_Api::getBatchingThreads(
				$this->user_id, $filtered_batch_thread_list, $this->extra["space"]["is_restricted_access"]
			);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not conversation member");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->action->users($users);

		return $this->ok([
			"feed_thread_list" => (array) $feed_batching_list,
		]);
	}

	/**
	 * Метод для прочтения одного непрочитанного сообщения диалога
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function readMessage():array {

		$message_key = $this->post("?s", "message_key");
		$message_map = \CompassApp\Pack\Message::tryDecrypt($message_key);

		try {
			Domain_Conversation_Feed_Scenario_Api::readMessage($this->user_id, $message_map);
		} catch (Domain_Conversation_Exception_User_IsNotMember) {
			throw new CaseException(2218001, "User is not conversation member");
		} catch (Domain_Conversation_Exception_Message_IsNotRead) {
			return $this->ok();
		} catch (Domain_Conversation_Exception_Message_IsNotFromConversation) {
			throw new ParamException("Message is not from conversation");
		}

		return $this->ok();
	}
	
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * подготавливаем параметры для батчинга
	 * 
	 * @throws ParamException
	 * @throws \paramException
	 */
	protected function _prepareParamBatchList(array $batch_list):array {

		$filtered_batch_list = [];
		foreach ($batch_list as $v) {

			if (!isset($v["conversation_key"])) {
				throw new ParamException("Passed incorrect param conversation_key");
			}

			if (isset($v["block_id_list"]) && !is_array($v["block_id_list"])) {
				throw new ParamException("Passed incorrect param block_id_list");
			}

			$filtered_batch_list[] = [
				"conversation_map" => \CompassApp\Pack\Conversation::tryDecrypt($v["conversation_key"]),
				"block_id_list"    => $v["block_id_list"] ?? [],
			];
		}

		return $filtered_batch_list;
	}
}
