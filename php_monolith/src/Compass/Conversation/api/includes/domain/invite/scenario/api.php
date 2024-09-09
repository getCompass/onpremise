<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use CompassApp\Domain\Member\Entity\Member;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Struct\Short;

/**
 * API-сценарии домена «инвайты».
 */
class Domain_Invite_Scenario_Api {

	protected const _MAX_USERS_COUNT_ADD  = 30; // максимальное число пользователей, которое может быть добавлено за раз в группу
	protected const _MAX_GROUPS_COUNT_ADD = 30; // максимальное число групп, в которое за раз можно добавить

	/**
	 * добавляем батчингом
	 *
	 * @return array[]
	 * @throws Domain_Invite_Exception_AllUserWasKicked
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws cs_UserIsNotAdmin
	 * @throws cs_UserIsNotMember|cs_PlatformNotFound
	 */
	public static function addBatching(int $user_id, array $batch_user_list, string $signature, string $conversation_map):array {

		// проверяем, что батч и подпись в порядке
		self::_verifyBatchingList($signature, $batch_user_list);

		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::SEND_INVITE_FROM_CONVERSATION);

		// возвращаем ошибку если приглашающий не является участником группы
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			throw new cs_UserIsNotMember();
		}

		// проверяем
		if (!Type_Conversation_Meta_Users::isGroupAdmin($user_id, $meta_row["users"])) {
			throw new cs_UserIsNotAdmin();
		}

		if (self::_isAllUserWasKicked($batch_user_list)) {
			throw new Domain_Invite_Exception_AllUserWasKicked("All user are kicked");
		}

		return self::_processBatchUserList($user_id, $batch_user_list, $meta_row);
	}

	/**
	 * выполняем процесс добавления списка пользователей
	 *
	 * @return array[]
	 * @throws cs_PlatformNotFound
	 */
	protected static function _processBatchUserList(int $user_id, array $batch_user_list, array $meta_row):array {

		$list_ok    = [];
		$list_error = [];

		$platform = Type_Api_Platform::getPlatform();

		$member_list = Gateway_Bus_CompanyCache::getShortMemberList($batch_user_list, is_only_human: false);

		// проходимся по каждому пользователю
		foreach ($member_list as $item) {

			try {

				Domain_Invite_Action_SendAutoAccepted::run($user_id, $item, $meta_row, $platform);
				$list_ok[] = ["user_id" => (int) $item->user_id];
			} catch (cs_InviteStatusIsNotExpected|cs_InviteIsDuplicated) {
				$list_ok[] = ["user_id" => (int) $item->user_id];
			} catch (ParamException|Domain_Invite_Exception_IsNotHuman $e) {
				$list_error[] = ["user_id" => (int) $item->user_id, "error_code" => (int) 400, "error" => (string) $e->getMessage()];
			} catch (Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_MemberIsDisabled $e) {
				$list_error[] = ["user_id" => (int) $item->user_id, "error_code" => (int) 2141007, "error" => (string) $e->getMessage()];
			}
		}

		return [$list_ok, $list_error];
	}

	/**
	 * проверяет подпись по пользователям
	 *
	 * @throws paramException
	 */
	protected static function _verifyBatchingList(string $signature, array $batch_user_list):void {

		// если массив пустой
		if (count($batch_user_list) < 1) {
			throw new ParamException("passed empty batch_user_list");
		}

		// если в массиве слишком много пользователей
		if (count($batch_user_list) > self::_MAX_USERS_COUNT_ADD) {
			throw new ParamException("passed batch_user_list more than max: " . self::_MAX_USERS_COUNT_ADD);
		}

		// если пришла некорректная подпись
		if (!Type_Conversation_Utils::verifySignatureWithCustomSalt($batch_user_list, $signature, SALT_ALLOWED_USERS_FOR_INVITE)) {
			throw new ParamException(__METHOD__ . " wrong signature");
		}
	}

	/**
	 * проверяем всех пользователей на кик
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 */
	protected static function _isAllUserWasKicked(array $invited_user_list):bool {

		$kicked_member_list = [];
		$member_list        = Gateway_Bus_CompanyCache::getMemberList($invited_user_list);
		foreach ($member_list as $member) {

			// если пользователь уволен и об этом надо вывести ошибку
			if ($member->role == Member::ROLE_LEFT) {
				$kicked_member_list[] = $member->user_id;
			}
		}

		return count($invited_user_list) == count($kicked_member_list);
	}

	/**
	 * добавляем пользователя в группы батчингом
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws paramException
	 */
	public static function addBatchingForGroup(int $user_id, int $invited_user_id, array $group_conversation_key_list):array {

		// проверяем что пришли корректные параметры
		self::_throwIfPassedIncorrectParamsForTrySendBatchingForGroups($user_id, $invited_user_id, $group_conversation_key_list);

		// получаем мету сингл диалога
		$single_meta_row = self::_getSingleConversationMeta($user_id, $invited_user_id);

		// если allow_status не позволяет приглашать пользователя
		Helper_Conversations::checkIsAllowed($single_meta_row["conversation_map"], $single_meta_row, $user_id);

		// получаем conversation_map_list и conversation_meta_list
		$conversation_map_list = self::_tryDecryptConversationKeyList($group_conversation_key_list);
		$meta_list             = Type_Conversation_Meta::getAll($conversation_map_list);

		// отправляем приглашение в каждую групп, если возможно
		return self::_sendInviteToEveryGroupIfIsPossible($user_id, $invited_user_id, $meta_list);
	}

	/**
	 * если пришли некорректные параметры в запроса
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	protected static function _throwIfPassedIncorrectParamsForTrySendBatchingForGroups(int $user_id, int $invited_user_id, array $group_conversation_key_list):void {

		// проверяем что присланный user_id корректный
		if ($user_id < 1) {
			throw new ParamException("passed invalid user_id");
		}

		// проверяем что отправляем не самому себе
		if ($user_id == $invited_user_id) {
			throw new ParamException("Passed yourself in user_id parameter");
		}

		// проверяем что такой пользователь существует
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new ParamException("dont found user in company cache");
		}

		// если массив пустой
		if (count($group_conversation_key_list) < 1) {
			throw new ParamException("Passed empty group_list");
		}

		// если в массиве слишком много пользователей
		if (count($group_conversation_key_list) > self::_MAX_GROUPS_COUNT_ADD) {
			throw new ParamException("Passed group_list more than max: " . self::_MAX_GROUPS_COUNT_ADD);
		}
	}

	/**
	 * получаем мету single диалога
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 */
	protected static function _getSingleConversationMeta(int $user_id, int $invited_user_id):array {

		$single_conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $invited_user_id);
		if ($single_conversation_map) {
			return Type_Conversation_Meta::get($single_conversation_map);
		}

		// создаем сингл-диалог
		return Helper_Single::createIfNotExist($user_id, $invited_user_id, true, false);
	}

	/**
	 * преобразуем пришедшие ключи в map
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected static function _tryDecryptConversationKeyList(array $conversation_key_list):array {

		$conversation_map_list = [];
		foreach ($conversation_key_list as $item) {

			$item = \CompassApp\Pack\Main::checkCorrectKey($item);

			// преобразуем key в map
			try {
				$conversation_map = \CompassApp\Pack\Conversation::doDecrypt($item);
			} catch (\cs_DecryptHasFailed) {

				Gateway_Bus_Statholder::inc("conversation", "row493");
				throw new ParamException("passed wrong conversation key");
			}

			// добавляем диалог в массив
			$conversation_map_list[] = $conversation_map;
		}

		return $conversation_map_list;
	}

	/**
	 * отправляем приглашение в каждую группу
	 *
	 * @param int   $user_id
	 * @param int   $invited_user_id
	 * @param array $conversation_meta_list
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws BlockException
	 * @throws ControllerMethodNotFoundException
	 * @throws \ErrorException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws cs_ErrorSocketRequest
	 * @throws cs_InviteActiveSendLimitIsExceeded
	 * @throws cs_InviteStatusIsNotExpected
	 * @throws cs_PlatformNotFound
	 */
	protected static function _sendInviteToEveryGroupIfIsPossible(int $user_id, int $invited_user_id, array $conversation_meta_list):array {

		$output = [
			"is_sent"    => 1,
			"list_ok"    => [],
			"list_error" => [],
		];

		foreach ($conversation_meta_list as $conversation_meta) {
			$error = self::_getErrorIfNotSendInviteToGroup($user_id, $conversation_meta["type"], $conversation_meta["users"], $conversation_meta["conversation_map"]);

			if (!empty($error)) {
				$output["list_error"][] = $error;
				continue;
			}

			$output["list_ok"][] = self::_makeListOkItemForTrySendBathingGroups($conversation_meta["conversation_map"]);
		}

		if (!empty($output["list_error"])) {

			$output["is_sent"] = 0;
			return [$output["is_sent"], $output["list_ok"], $output["list_error"]];
		}

		// отправляем инвайт в каждую группу
		self::_sendInviteToEveryGroup($conversation_meta_list, $user_id, $invited_user_id);

		return [$output["is_sent"], $output["list_ok"], $output["list_error"]];
	}

	/**
	 * формируем ответ для list_ok для trySendBatchingForGroups
	 *
	 * @return string[]
	 */
	protected static function _makeListOkItemForTrySendBathingGroups(string $group_conversation_map):array {

		return [
			"conversation_map" => (string) $group_conversation_map,
		];
	}

	/**
	 * получаем ошибку, если не удалось отправить инвайт
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _getErrorIfNotSendInviteToGroup(int $user_id, int $type, array $users, string $conversation_map):array {

		// проверяем, что действия валидно для данного типа диалога
		if (!Type_Conversation_Action::isValidForAction($type, Type_Conversation_Action::SEND_INVITE_FROM_CONVERSATION)) {
			return self::_makeErrorForTrySendBathingForGroups($conversation_map, 400, "Conversation type is not available action");
		}

		// если пользователь не участник диалога
		if (!Type_Conversation_Meta_Users::isMember($user_id, $users)) {
			return self::_makeErrorForTrySendBathingForGroups($conversation_map, 2141001, "User is not conversation member");
		}

		// если пользователь не может отправлять инвайт (не позволяет роль)
		if (!Type_Conversation_Meta_Users::isGroupAdmin($user_id, $users)) {
			return self::_makeErrorForTrySendBathingForGroups($conversation_map, 2141002, "You are not allowed to do this action");
		}

		return [];
	}

	/**
	 * отправляем инвайты
	 *
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \ErrorException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws cs_ErrorSocketRequest
	 * @throws cs_InviteActiveSendLimitIsExceeded
	 * @throws cs_InviteStatusIsNotExpected
	 * @throws cs_PlatformNotFound
	 */
	protected static function _sendInviteToEveryGroup(array $conversation_meta_list, int $user_id, int $invited_user_id):void {

		$platform = Type_Api_Platform::getPlatform();

		$member_list = Gateway_Bus_CompanyCache::getShortMemberList([$invited_user_id]);

		if ($member_list === []) {
			throw new ParseFatalException("cant find member");
		}
		$invited_member = $member_list[$invited_user_id];

		// отправляем инвайт и добавляем в каждую группу
		foreach ($conversation_meta_list as $v) {

			try {
				Domain_Invite_Action_SendAutoAccepted::run($user_id, $invited_member, $v, $platform);
			} catch (cs_InviteIsDuplicated) {
				continue;
			}
		}
	}

	/**
	 * формируем ответ с error_code для trySendBatchingForGroups
	 *
	 * @return array
	 */
	protected static function _makeErrorForTrySendBathingForGroups(string $conversation_map, int $error_code, string $error_message):array {

		return [
			"conversation_map" => (string) $conversation_map,
			"error_code"       => (int) $error_code,
			"message"          => (string) $error_message,
		];
	}
}
