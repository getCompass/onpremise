<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\AccountDeleted;
use CompassApp\Domain\Member\Exception\IsLeft;

/**
 * Контроллер, отвечающий за личные диалоги
 */
class Apiv2_Conversations_Single extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"mute",
		"unMute",
		"clearMessages",
		"leave",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"mute",
		"unMute",
		"clearMessages",
		"leave",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [];

	/**
	 * Отключаем уведомления
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 */
	public function mute():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_MUTE);

		try {

			Domain_Conversation_Scenario_Apiv2::mute($this->user_id, $user_id);
		} catch (AccountDeleted) {
			throw new CaseException(2240001, "opponent delete account");
		} catch (IsLeft) {
			throw new CaseException(2240002, "user left space");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2240003, "bot deleted");
		} catch (Domain_Userbot_Exception_DisabledStatus) {
			throw new CaseException(2240004, "bot disabled");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->ok();
	}

	/**
	 * Включаем уведомления
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function unmute():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_UNMUTE);

		Domain_Conversation_Scenario_Apiv2::unmute($this->user_id, $user_id);

		return $this->ok();
	}

	/**
	 * Очищаем диалог
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function clearMessages():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DOCLEARMESSAGES);

		Domain_Conversation_Scenario_Apiv2::clearMessages($this->user_id, $user_id);

		return $this->ok();
	}

	/**
	 * Удаляем диалог
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function leave():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DOREMOVESINGLE);

		Domain_Conversation_Scenario_Apiv2::leave($this->user_id, $user_id);

		return $this->ok();
	}
}
