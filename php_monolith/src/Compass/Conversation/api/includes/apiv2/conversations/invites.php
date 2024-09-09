<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Pack\Conversation;

/**
 * контроллер, отвечающий за инвайты
 */
class Apiv2_Conversations_Invites extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"addBatching",
		"addBatchingForGroups",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"addBatching",
		"addBatchingForGroups",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [
		Member::ROLE_GUEST => [
			"addBatching",
			"addBatchingForGroups",
		],
	];

	/**
	 * метод для добавления в групповой диалог группе пользователей
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException|CaseException
	 */
	public function addBatching():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key", "");
		$conversation_map = Conversation::tryDecrypt($conversation_key);

		$batch_user_list = $this->post(\Formatter::TYPE_ARRAY_INT, "batch_user_list");
		$signature       = $this->post(\Formatter::TYPE_STRING, "signature");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_TRYSENDBATCHING);

		try {
			[$prepared_list_ok, $prepared_list_error] = Domain_Invite_Scenario_Api::addBatching($this->user_id, $batch_user_list, $signature, $conversation_map);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2141001, "User is not conversation member");
		} catch (cs_UserIsNotAdmin) {
			throw new CaseException(2141002, "you are not allowed to do this action");
		} catch (Domain_Invite_Exception_AllUserWasKicked) {
			throw new CaseException(2141003, "All user are kicked");
		} catch (cs_PlatformNotFound) {
			throw new ParamException("invalid platform");
		}

		return $this->ok(Apiv2_Format::addBatchingInvites($prepared_list_ok, $prepared_list_error));
	}

	/**
	 * метод для добавления в групповые диалоги пользователя
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \returnException
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled|CaseException
	 */
	public function addBatchingForGroups():array {

		$user_id                     = $this->post(\Formatter::TYPE_INT, "user_id");
		$group_conversation_key_list = $this->post(\Formatter::TYPE_ARRAY, "group_conversation_key_list");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_TRYSENDBATCHINGFORGROUPS);

		try {
			[$is_sent, $prepared_list_ok, $prepared_list_error] = Domain_Invite_Scenario_Api::addBatchingForGroup($this->user_id, $user_id, $group_conversation_key_list);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2141001, "User is not conversation member");
		} catch (cs_UserIsNotAdmin) {
			throw new CaseException(2141002, "you are not allowed to do this action");
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_MemberIsDisabled) {
			throw new CaseException(2141005, "You can't write to this conversation because your opponent delete account");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			throw new CaseException(2141006, "You can't write to this conversation because your opponent delete account");
		}

		return $this->ok(Apiv2_Format::addBatchingForGroupsInvites($is_sent, $prepared_list_ok, $prepared_list_error));
	}
}
