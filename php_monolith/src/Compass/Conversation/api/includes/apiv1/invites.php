<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\BlockException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Struct\Main;

/**
 * класс для API методов группы invites
 * @property Type_Api_Action action
 */
class Apiv1_Invites extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"trySend",
		"get",
		"tryAccept",
		"doDecline",
		"getAllowedUsersForInvite",
		"trySendBatching",
		"getBatching",
		"trySendBatchingForGroups",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"trySend",
		"tryAccept",
		"doDecline",
		"trySendBatching",
		"trySendBatchingForGroups",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [
		Member::ROLE_GUEST => [
			"trySend",
			"getAllowedUsersForInvite",
			"trySendBatching",
			"trySendBatchingForGroups",
		],
	];

	protected const _MAX_USERS_COUNT   = 30; // максимальное число пользователей, которое может придти в запрос getAllowedUsersForInvite
	protected const _MAX_GROUPS_COUNT  = 30; // максимальное число групп, которое может придти в запрос trySendBatchingForGroups
	protected const _MAX_INVITES_COUNT = 50; // максимальное количество инвайтов, который могут придти в метод getBatching

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для отправки приглашения в групповой диалог пользователю
	 *
	 * @throws \baseException
	 * @throws \blockException
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function trySend():array {

		// сначала пытаемся понять, пришла нам команда на отправку invite или invitation
		$conversation_key = $this->post("?s", "conversation_key", "");

		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		$user_id = $this->post("?i", "user_id");

		$this->_throwIfUserIdIsMalformed($user_id, "row0");
		$this->_throwIfUserIdIsEqualWithYourself($user_id, "row1");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_TRYSEND, "invites", "row2");

		// бросаем ошибку, если пользователя не существует
		$this->_throwIfUserIsNotExist($user_id);

		// проверяем что диалог является групповым и что пользователь в нем участник
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::SEND_INVITE_FROM_CONVERSATION);

		// пользователь не участник диалога
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("invites", "row6");
			return $this->error(501, "User is not conversation member");
		}

		// пользователь не может отправлять инвайт (не позволяет роль)
		if (!Type_Conversation_Meta_Users::isGroupAdmin($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("invites", "row7");
			return $this->error(902, "you are not allowed to do this action");
		}

		return $this->_sendInviteIfIsPossible($this->user_id, $user_id, $meta_row);
	}

	// пытаемся отослать инвайт
	protected function _sendInviteIfIsPossible(int $user_id, int $opponent_user_id, array $meta_row):array {

		// возвращаем новую (старую) ошибку
		$is_return_code_532 = Type_System_Legacy::isNewErrors();

		$single_meta_row = $this->_getSingleConversationMeta($opponent_user_id);

		// если allow_status не позволяет приглашать пользователя
		try {
			Helper_Conversations::checkIsAllowed($single_meta_row["conversation_map"], $single_meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled) {

			Gateway_Bus_Statholder::inc("invites", "row11");

			if ($is_return_code_532) {
				return $this->error(532, "User is disabled");
			}
			return $this->error(903, "User is disabled");
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			return $this->error(2118001, "You can't write to this conversation because your opponent delete account");
		}
		return $this->_sendInvite($user_id, $opponent_user_id, $meta_row, $single_meta_row);
	}

	// отсылаем инвайт
	protected function _sendInvite(int $user_id, int $opponent_user_id, array $meta_row, array $single_meta_row):array {

		$platform = Type_Api_Platform::getPlatform();

		try {
			Helper_Invites::inviteUserFromSingle($user_id, $opponent_user_id, $meta_row, $single_meta_row, true, true, $platform);
		} catch (cs_InviteActiveSendLimitIsExceeded) {
			return $this->error(915, "Active invite send limit exceeded");
		} catch (cs_InviteStatusIsNotExpected|cs_InviteIsDuplicated) {
			return $this->ok();
		}

		Gateway_Bus_Statholder::inc("invites", "row12");
		return $this->ok();
	}

	/**
	 * метод для получения информации о приглашении
	 *
	 * @throws \baseException
	 * @throws \paramException
	 * @throws \parseException|cs_DecryptHasFailed
	 */
	public function get():array {

		$invite_key    = $this->post("?s", "invite_key");
		$invite_map    = \CompassApp\Pack\Invite::tryDecrypt($invite_key);

		Gateway_Bus_Statholder::inc("invites", "row25");

		// получаем запись с приглашением и записываем invite_map в invite_row на случай, если приглашение старое
		$invite_row = $this->_tryGetInviteRowIfExist($invite_map, "row22");

		// проверяем имеет ли пользователь доступ к инвайту
		$this->_checkIfUserIsAllowedToGetInvite($invite_row);

		// получаем запись в левом меню и мету диалога
		$meta_row = Type_Conversation_Meta::get($invite_row["group_conversation_map"]);

		// добавляем action users к ответу если приглашение не отклонено
		$this->_addActionUsersIfInviteNotIsDeclined($invite_row["status"], $meta_row["users"], "row23");

		// получаем левое меню и формируем ответ для передачи клиенту
		$left_menu_row   = $this->_getUserLeftMenuRow($this->user_id, $meta_row["conversation_map"]);
		$prepared_invite = Type_Invite_Utils::prepareInvite($invite_row, $left_menu_row, $meta_row);
		return $this->ok([
			"invite" => (object) Apiv1_Format::invite($prepared_invite),
		]);
	}

	// получаем приглашение если оно существует
	protected function _tryGetInviteRowIfExist(string $invite_map, string $row):array {

		$invite_row = Type_Invite_Handler::get($invite_map);

		if (!isset($invite_row["user_id"])) {

			Gateway_Bus_Statholder::inc("invites", $row); // проверили что приглашение существует
			throw new ParamException(__METHOD__ . " invite is not found");
		}

		return $invite_row;
	}

	/**
	 * метод для получения информации о приглашениях
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getBatching():array {

		$invite_key_list = $this->post("?a", "invite_key_list");

		Gateway_Bus_Statholder::inc("invites", "row120");

		// оставляем только уникальные значения в массиве
		$invite_key_list = array_unique($invite_key_list);

		// бросаем ошибку, если пришел некорректный массив инвайтов
		$this->_throwIfInviteKeyListIsIncorrect($invite_key_list);

		// преобразуем все key в map
		$invite_map_list = $this->_doDecryptInviteList($invite_key_list);

		// получаем записи из базы
		$invite_list = Type_Invite_Single::getInviteList($invite_map_list, $this->user_id);

		// собираем conversation_map_list
		$conversation_map_list = $this->_makeConversationMapList($invite_list);

		// получаем conversation_meta_list и группируем его по conversation_map
		$grouped_conversation_meta_list = $this->_getConversationMetaListGrouppedByConversationMap($conversation_map_list);

		// получаем левое меню пользователя и группируем его по conversation_map
		$grouped_left_menu_list = $this->_getLeftMenuListGrouppedByConversationMap($this->user_id, $conversation_map_list);

		// собираем ответ для инвайтов в диалоги
		$conversation_invite_list = $this->_makeOutputForGetBatching($invite_list, $grouped_left_menu_list, $grouped_conversation_meta_list);

		Gateway_Bus_Statholder::inc("invites", "row124");

		return $this->ok([
			"invite_list" => (array) $conversation_invite_list,
		]);
	}

	// выбрасываем ошибку, если пришел некорректный массив инвайтов
	protected function _throwIfInviteKeyListIsIncorrect(array $invite_key_list):void {

		// если пришел пустой массив инвайтов
		if (count($invite_key_list) < 1) {

			Gateway_Bus_Statholder::inc("invites", "row121");
			throw new ParamException("passed empty invite_key_list");
		}

		// если пришел слишком большой массив
		if (count($invite_key_list) > self::_MAX_INVITES_COUNT) {

			Gateway_Bus_Statholder::inc("invites", "row122");
			throw new ParamException("passed invite_key_list biggest than max");
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _doDecryptInviteList(array $invite_key_list):array {

		$invite_map_list = [];
		foreach ($invite_key_list as $item) {

			$key = \CompassApp\Pack\Main::checkCorrectKey($item);

			// преобразуем key в map
			$invite_map = \CompassApp\Pack\Invite::tryDecrypt($key);

			// добавляем инвайт в массив
			$invite_map_list[] = $invite_map;
		}

		return $invite_map_list;
	}

	// получаем conversation_meta_list и группируем его по conversation_map
	protected function _getConversationMetaListGrouppedByConversationMap(array $conversation_map_list):array {

		$conversation_meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// группируем по conversation_map
		$grouped_conversation_meta_list = [];
		foreach ($conversation_meta_list as $conversation_meta_row) {
			$grouped_conversation_meta_list[$conversation_meta_row["conversation_map"]] = $conversation_meta_row;
		}

		return $grouped_conversation_meta_list;
	}

	// получаем левое меню пользователя и группируем его по conversation_map
	protected function _getLeftMenuListGrouppedByConversationMap(int $user_id, array $conversation_map_list):array {

		// получаем левое меню пользователя и группируем по conversation_map
		$left_menu_list = $this->_getUserLeftMenuList($user_id, $conversation_map_list);

		$grouped_left_menu_list = [];
		foreach ($left_menu_list as $left_menu_row) {
			$grouped_left_menu_list[$left_menu_row["conversation_map"]] = $left_menu_row;
		}

		return $grouped_left_menu_list;
	}

	// получаем записи из левого меню пользователя
	protected function _getUserLeftMenuList(int $user_id, array $conversation_map_list):array {

		return Type_Conversation_LeftMenu::getList($user_id, $conversation_map_list);
	}

	// собираем conversation_map_list
	protected function _makeConversationMapList(array $invite_list):array {

		$conversation_map_list = [];

		foreach ($invite_list as $k => $_) {
			$conversation_map_list[] = $k;
		}

		return $conversation_map_list;
	}

	// собираем ответ
	protected function _makeOutputForGetBatching(array $invite_list, array $groupped_left_menu_list, array $groupped_conversation_meta_list):array {

		$output = [];

		// проходимся по invite_list
		foreach ($invite_list as $k => $v) {

			$meta_row = $groupped_conversation_meta_list[$k];
			foreach ($v as $item) {

				$this->_addActionUsersIfInviteNotIsDeclined($item["status"], $meta_row["users"], "row23");

				// если запись в левом меню есть
				if (isset($groupped_left_menu_list[$k])) {
					$left_menu_row = $groupped_left_menu_list[$k];
				} else {
					$left_menu_row = [];
				}

				$output[] = $this->_makeFormattedInvite($item, $left_menu_row, $meta_row);
			}
		}

		return $output;
	}

	// форматируем ответ
	protected function _makeFormattedInvite(array $invite_row, array $left_menu_row, array $meta_row):array {

		$prepared_invite = Type_Invite_Utils::prepareInvite($invite_row, $left_menu_row, $meta_row);
		return Apiv1_Format::invite($prepared_invite);
	}

	/**
	 * метод для принятия приглашения в групповой диалог
	 *
	 * @throws \baseException
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException|cs_DecryptHasFailed
	 */
	public function tryAccept():array {

		$invite_key = $this->post(\Formatter::TYPE_STRING, "invite_key");
		$invite_map = \CompassApp\Pack\Invite::tryDecrypt($invite_key);

		$is_new_try_accept_invite_error = Type_System_Legacy::isNewTryAcceptError();

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_TRYACCEPT, "invites", "row41");

		// принимаем инвайт
		try {
			$invite_row = Helper_Invites::setAccepted($invite_map, $this->user_id);
		} catch (cs_InviteIsNotExist) {

			Gateway_Bus_Statholder::inc("invites", "row43");
			throw new ParamException(__METHOD__ . " invite is not found");
		} catch (cs_InviteIsNotMine) {

			Gateway_Bus_Statholder::inc("invites", "row42");
			throw new ParamException("it's not your invite");
		} catch (cs_ConversationIsLocked) {
			throw new BlockException(__METHOD__ . " trying to accept invite in locked conversation");
		} catch (cs_InviteIsDeclined|cs_InviteIsAccepted|cs_InviteIsRevoked|cs_InviteIsNotActive  $e) {

			$error = Helper_Invites::getTryAcceptError($e, $is_new_try_accept_invite_error);
			return $this->error($error["error_code"], $error["message"]);
		} catch (cs_InviteStatusIsNotExpected $e) {

			Gateway_Bus_Statholder::inc("invites", "row46");
			$invite_row = Type_Invite_Handler::get($invite_map);
			$this->_throwIfInviteIsActive($invite_row);
			if ($invite_row["status"] != Type_Invite_Handler::STATUS_ACCEPTED) {

				$error = Helper_Invites::getTryAcceptError($e, $is_new_try_accept_invite_error);
				return $this->error($error["error_code"], $error["message"]);
			}
			$prepared_conversation = $this->_getPreparedConversation($invite_row);
			return $this->_returnOkIfInviteStatusIsNotExpected($prepared_conversation);
		}
		$prepared_conversation = $this->_doJoinUserToConversation($invite_row, $this->user_id, $this->role, $this->permissions);
		return $this->_returnOkIfInviteStatusIsNotExpected($prepared_conversation);
	}

	// бросаем если инвайт активный
	protected function _throwIfInviteIsActive(array $invite_row):void {

		if ($invite_row["status"] == Type_Invite_Handler::STATUS_ACTIVE) {
			throw new ReturnFatalException(__METHOD__ . " invite is active");
		}
	}

	// получаем отформатированный конверсешен
	protected function _getPreparedConversation(array $invite_row):array {

		$meta_row      = Type_Conversation_Meta::get($invite_row["group_conversation_map"]);
		$left_menu_row = $this->_getUserLeftMenuRow($this->user_id, $invite_row["group_conversation_map"]);

		return Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
	}

	// возращаем conversation для принятого инвайта
	protected function _returnOkIfInviteStatusIsNotExpected(array $prepared_conversation):array {

		Gateway_Bus_Statholder::inc("invites", "row45");

		return $this->ok([
			"conversation" => (object) Apiv1_Format::conversation($prepared_conversation),
		]);
	}

	/**
	 * добавляем пользователя в диалог
	 *
	 * @param array $invite_row
	 * @param int   $user_id
	 * @param int   $member_role
	 * @param int   $member_permissions
	 *
	 * @return array
	 * @throws \parseException
	 */
	protected function _doJoinUserToConversation(array $invite_row, int $user_id, int $member_role, int $member_permissions):array {

		// задаем роль обычного участника
		$role = Type_Conversation_Meta_Users::ROLE_DEFAULT;

		return Helper_Groups::doJoin(
			$invite_row["group_conversation_map"],
			$user_id,
			$member_role,
			$member_permissions,
			$invite_row["sender_user_id"],
			$role
		);
	}

	/**
	 * метод для отклонения приглашения в групповой диалог
	 *
	 * @throws \baseException
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \parseException|cs_DecryptHasFailed
	 */
	public function doDecline():array {

		$invite_key = $this->post("?s", "invite_key");
		$invite_map = \CompassApp\Pack\Invite::tryDecrypt($invite_key);

		$is_do_decline_invite_error = Type_System_Legacy::isNewDoDeclineError();

		Gateway_Bus_Statholder::inc("invites", "row67");
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_DODECLINE, "invites", "row61");

		// отклоняем приглашение
		try {
			Helper_Invites::setDeclined($invite_map, $this->user_id);
		} catch (cs_InviteIsNotExist) {

			Gateway_Bus_Statholder::inc("invites", "row62");
			throw new ParamException(__METHOD__ . " invite is not found");
		} catch (cs_InviteIsNotMine) {

			Gateway_Bus_Statholder::inc("invites", "row66");
			throw new ParamException("it's not your invite");
		} catch (cs_ConversationIsLocked) {
			throw new BlockException(__METHOD__ . " trying to accept invite in locked conversation");
		} catch (cs_InviteIsDeclined) {

			Gateway_Bus_Statholder::inc("invites", "row63");
			return $this->ok();
		} catch (cs_InviteIsAccepted|cs_InviteIsRevoked|cs_InviteIsNotActive $e) {

			$error = Helper_Invites::getDoDeclinedError($e, $is_do_decline_invite_error);
			return $this->error($error["error_code"], $error["message"]);
		}

		return $this->ok();
	}

	/**
	 * проверяем, что пришедшему списку пользователей можно отправлять приглашения
	 *
	 * @throws \blockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getAllowedUsersForInvite():array {

		$user_list   = $this->post("?ai", "user_list");
		$is_new_list = $this->post("?i", "is_new_list", 0);

		// нужно ли отдавать разделенно заблокированных пользователей
		$is_new_list = $is_new_list == 1;

		Gateway_Bus_Statholder::inc("invites", "row80");

		$this->_throwIfPassedIncorrectUserList($user_list);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_GETALLOWEDUSERSFORINVITE, "invites", "row82");

		// фильтруем список пользователей
		$user_list = $this->_sanitizeUserList($user_list);

		// получаем всех пользователей одним походом в кэш
		$user_info_list_grouped_by_id = Gateway_Bus_CompanyCache::getMemberList($user_list);

		return $this->_makeAllowedUsersOutput($user_info_list_grouped_by_id, $is_new_list);
	}

	// выбрасываем ошибку, если пришел некорректный user_list
	protected function _throwIfPassedIncorrectUserList(array $user_list):void {

		// если пришло слишком много пользователей в запросе
		if (count($user_list) > self::_MAX_USERS_COUNT) {

			Gateway_Bus_Statholder::inc("invites", "row81");
			throw new ParamException("passed biggest user_list than max: " . self::_MAX_USERS_COUNT);
		}

		// если передан пустой список пользователей
		if (count($user_list) < 1) {
			throw new  ParamException("passed empty user_list");
		}
	}

	// фильтруем список пришедших пользователей
	protected function _sanitizeUserList(array $user_list):array {

		$sanitized_user_list = [];
		foreach ($user_list as $item) {

			// если user_id нулевой
			if ($item < 1) {
				throw new ParamException("passed invalid user_id");
			}

			// исключаем себя из user_list
			if ($item == $this->user_id) {
				continue;
			}

			$sanitized_user_list[] = $item;
		}

		return $sanitized_user_list;
	}

	/**
	 * Метод вывода список пользователей доступых для отправки инвайтов
	 *
	 * @param Main[] $user_info_list_grouped_by_id
	 * @param bool  $is_new_list
	 *
	 * @return array
	 * @long
	 */
	protected function _makeAllowedUsersOutput(array $user_info_list_grouped_by_id, bool $is_new_list):array {

		// формируем списки пользователей, с которыми заблокирован диалог по той или иной причине
		$allowed_user_list        = [];
		$blocked_by_opponent_list = [];
		$blocked_by_me_list       = [];
		$disabled_list            = [];
		$account_deleted_list     = [];

		foreach ($user_info_list_grouped_by_id as $v1) {

			// если пользователь покинул компанию, кидаем в забаненные
			if ($v1->role == Member::ROLE_LEFT) {

				$disabled_list[] = (int) $v1->user_id;
				continue;
			}

			// если пользователь удалил аккаунт
			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($v1->extra)) {

				$account_deleted_list[] = (int) $v1->user_id;
				continue;
			}

			// ботов не можем приглашать
			if (!Type_User_Main::isHuman($v1->npc_type)) {
				continue;
			}

			// если дошли до сюда, значит можно приглашать
			$allowed_user_list[] = (int) $v1->user_id;
		}

		// форматируем ответ
		return $this->_formatOutputForGetAllowedUsersForInvite($allowed_user_list, $blocked_by_opponent_list, $blocked_by_me_list, $disabled_list, $account_deleted_list, $is_new_list);
	}

	// форматируем ответ для списка доступных пользоватлей для инвайты
	// @long
	protected function _formatOutputForGetAllowedUsersForInvite(array $allowed_user_list, array $blocked_by_opponent_list, array $blocked_by_me_list, array $disabled_list, array $account_deleted_list, bool $is_new_list):array {

		if (count($allowed_user_list) < 1) {

			Gateway_Bus_Statholder::inc("invites", "row83");
			return $this->error(910, "there are no available users to invite", [
				"blocked_by_opponent_user_id_list" => (array) $blocked_by_opponent_list,
				"blocked_by_me_user_id_list"       => (array) $blocked_by_me_list,
				"disabled_user_id_list"            => (array) $disabled_list,
				"account_deleted_user_id_list"     => (array) $account_deleted_list,
			]);
		}

		$signature = Type_Conversation_Utils::getSignatureWithCustomSalt($allowed_user_list, time(), SALT_ALLOWED_USERS_FOR_INVITE);
		Gateway_Bus_Statholder::inc("invites", "row84");
		if (!$is_new_list) {

			return $this->ok([
				"signature"             => (string) $signature,
				"allowed_user_list"     => (array) $allowed_user_list,
				"not_allowed_user_list" => (array) array_merge($blocked_by_opponent_list, $blocked_by_me_list, $disabled_list, $account_deleted_list),
			]);
		}
		return $this->ok([
			"signature"                        => (string) $signature,
			"allowed_user_list"                => (array) $allowed_user_list,
			"blocked_by_opponent_user_id_list" => (array) $blocked_by_opponent_list,
			"blocked_by_me_user_id_list"       => (array) $blocked_by_me_list,
			"disabled_user_id_list"            => (array) $disabled_list,
			"account_deleted_user_id_list"     => (array) $account_deleted_list,
		]);
	}

	/**
	 * метод для отправки приглашения в групповой диалог группе пользователей
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \baseException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function trySendBatching():array {

		// сначала пытаемся понять, пришла нам команда на отправку invite или invitation
		$conversation_key = $this->post("?s", "conversation_key", "");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		$batch_user_list = $this->post("?ai", "batch_user_list");
		$signature       = $this->post("?s", "signature");

		$this->_throwIfPassedIncorrectParams($signature, $batch_user_list);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_TRYSENDBATCHING, "invites", "row105");

		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::SEND_INVITE_FROM_CONVERSATION);

		// возвращаем ошибку если приглашающий не является участником группы
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(501, "User is not conversation member");
		}

		if (!Type_Conversation_Meta_Users::isGroupAdmin($this->user_id, $meta_row["users"])) {
			return $this->error(902, "you are not allowed to do this action");
		}

		if (Type_System_Legacy::is504ErrorThenAllUserWasKicked() && self::_isAllUserWasKicked($batch_user_list)) {
			return $this->error(504, "All user are kicked");
		}

		// проверяем, что не достигли лимита активных инвайтов
		$free_active_invite_count = $this->_getFreeActiveInviteCount($conversation_map);
		if (count($batch_user_list) > $free_active_invite_count) {

			return $this->error(915, "Active invite send limit exceeded", [
				"free_active_invite_count" => $free_active_invite_count,
			]);
		}

		return $this->_sendInviteToEveryUser($batch_user_list, $meta_row);
	}

	// если пришли некорректные параметры в запроса
	protected function _throwIfPassedIncorrectParams(string $signature, array $batch_user_list):void {

		// апроверяем, что батч и подпись в поряде
		self::_verifyBatchingList($signature, $batch_user_list);
	}

	/**
	 * проверяем всех пользователей на кик
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

	// получить количество свободных активных инвайтов
	protected function _getFreeActiveInviteCount(string $conversation_map):int {

		// получаем количество имеющихся активных инвайтов нашего пользователя
		$count_sender_active_invite = Type_Invite_Single::getCountSenderActiveInvite($this->user_id, $conversation_map);

		return Type_Invite_Handler::getSendActiveInviteLimit() - $count_sender_active_invite;
	}

	// отправляем приглашение каждому пользователю
	protected function _sendInviteToEveryUser(array $batch_user_list, array $meta_row):array {

		// проходим по списку пользователей - каждому отправляем инвайт
		$output = $this->_makeOutputForTrySendBatching();
		foreach ($batch_user_list as $item) {

			try {
				Helper_Invites::inviteUserFromSingleWithAsyncMessages($this->user_id, $item, $meta_row);
			} catch (cs_InviteActiveSendLimitIsExceeded) {

				$output["list_error"][] = $this->_makeError915ItemForTrySendBatching($item);
				continue;
			} catch (cs_InviteStatusIsNotExpected|cs_InviteIsDuplicated) {

				$output["list_ok"][] = $this->_makeListOkItemForTrySendBatching($item);
				continue;
			}

			$output["list_ok"][] = $this->_makeListOkItemForTrySendBatching($item);
		}

		Gateway_Bus_Statholder::inc("invites", "row110");

		return $this->ok($output);
	}

	// создаем output для метода trySendBatching
	protected function _makeOutputForTrySendBatching():array {

		return [
			"list_ok"    => (array) [],
			"list_error" => (array) [],
		];
	}

	// формируем error_code 915 для trySendBatching
	protected function _makeError915ItemForTrySendBatching(int $user_id):array {

		return [
			"user_id"    => (int) $user_id,
			"error_code" => (int) 915,
			"message"    => (string) "Active invite send limit exceeded",
		];
	}

	// формируем ответ для list_ok для trySendBathing
	protected function _makeListOkItemForTrySendBatching(int $user_id):array {

		return [
			"user_id" => (int) $user_id,
		];
	}

	/**
	 * метод для отправки приглашений одному пользователю во множество групп
	 *
	 * @throws \blockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function trySendBatchingForGroups():array {

		$user_id                     = $this->post("?i", "user_id");
		$group_conversation_key_list = $this->post("?a", "group_conversation_key_list");
		$is_return_code_532          = Type_System_Legacy::isNewErrors();

		// проверяем что пришли корректные параметры
		$this->_throwIfPassedIncorrectParamsForTrySendBatchingForGroups($user_id, $group_conversation_key_list);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::INVITES_TRYSENDBATCHINGFORGROUPS);

		// получаем мету сингл диалога
		$single_meta_row = $this->_getSingleConversationMeta($user_id);

		// если allow_status не позволяет приглашать пользователя
		try {
			Helper_Conversations::checkIsAllowed($single_meta_row["conversation_map"], $single_meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled) {

			if ($is_return_code_532) {
				return $this->error(532, "User is disabled");
			}
			return $this->error(903, "User is disabled");
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			return $this->error(2118001, "You can't write to this conversation because your opponent delete account");
		}

		// получаем conversation_map_list и conversation_meta_list
		$conversation_map_list = $this->_tryDecryptConversationKeyList($group_conversation_key_list);
		$meta_list             = Type_Conversation_Meta::getAll($conversation_map_list);

		// отправляем инвайты, если возможно
		$output = $this->_makeOutputForTrySendBatchingForGroups();
		$output = $this->_sendInviteBatchingForGroups($conversation_map_list, $user_id, $meta_list, $output);

		return $this->ok($output);
	}

	// если пришли некорректные параметры в запроса
	protected function _throwIfPassedIncorrectParamsForTrySendBatchingForGroups(int $user_id, array $group_conversation_key_list):void {

		// проверяем что присланный user_id корректный
		$this->_throwIfUserIdIsMalformed($user_id);

		// проверяем что отправляем не самому себе
		$this->_throwIfUserIdIsEqualWithYourself($user_id);

		// проверяем что такой пользователь существует
		$this->_throwIfUserIsNotExist($user_id);

		// если массив пустой
		if (count($group_conversation_key_list) < 1) {
			throw new ParamException("Passed empty group_list");
		}

		// если в массиве слишком много пользователей
		if (count($group_conversation_key_list) > self::_MAX_GROUPS_COUNT) {
			throw new ParamException("Passed group_list more than max: " . self::_MAX_GROUPS_COUNT);
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _tryDecryptConversationKeyList(array $conversation_key_list):array {

		$conversation_map_list = [];
		foreach ($conversation_key_list as $item) {

			$key = \CompassApp\Pack\Main::checkCorrectKey($item);

			// преобразуем key в map
			$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($key);

			// добавляем диалог в массив
			$conversation_map_list[] = $conversation_map;
		}

		return $conversation_map_list;
	}

	// создаем output для метода trySendBatchingForGroups
	protected function _makeOutputForTrySendBatchingForGroups():array {

		return [
			"is_sent"    => (int) 1,
			"list_ok"    => (array) [],
			"list_error" => (array) [],
		];
	}

	// отправляем инвайты
	protected function _sendInviteBatchingForGroups(array $conversation_map_list, int $user_id, array $meta_list, array $output):array {

		// получаем count_sender_active_invite_list
		$count_sender_active_invite_list = Type_Invite_Single::getAllCountSenderActiveInviteListForGroupList($this->user_id, $conversation_map_list);

		// отправляем приглашение в каждую групп, если возможно
		return $this->_sendInviteToEveryGroupIfIsPossible($output, $meta_list, $user_id, $count_sender_active_invite_list);
	}

	// отправляем приглашение в каждую группу
	protected function _sendInviteToEveryGroupIfIsPossible(array $output, array $conversation_meta_list, int $user_id, array $count_sender_active_invite_list):array {

		foreach ($conversation_meta_list as $v) {

			// если нельзя отправить инвайт - записываем ошибку
			$error = $this->_getErrorIfNotSendInviteToGroup($v["type"], $v["users"], $v["conversation_map"], $count_sender_active_invite_list);
			if (count($error) > 0) {

				$output["list_error"][] = $error;
				continue;
			}

			$output["list_ok"][] = $this->_makeListOkItemForTrySendBathingGroups($v["conversation_map"]);
		}

		// если невозможно отправить хоть один инвайт - не отправляем все инвайты
		if (count($output["list_error"]) > 0) {

			$output["is_sent"] = (int) 0;
			return $output;
		}

		$this->_sendInviteToEveryGroup($conversation_meta_list, $user_id);
		return $output;
	}

	// получаем ошибку, если не удалось отправить инвайт
	protected function _getErrorIfNotSendInviteToGroup(int $type, array $users, string $conversation_map, array $count_sender_active_invite_list):array {

		// проверяем, что действия валидно для данного типа диалога
		if (!Type_Conversation_Action::isValidForAction($type, Type_Conversation_Action::SEND_INVITE_FROM_CONVERSATION)) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 400, "Conversation type is not available action");
		}

		// если пользователь не участник диалога
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $users)) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 501, "User is not conversation member");
		}

		// если пользователь не может отправлять инвайт (не позволяет роль)
		if (!Type_Conversation_Meta_Users::isGroupAdmin($this->user_id, $users)) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 902, "You are not allowed to do this action");
		}

		// если запись счечика для данного диалога существует и достигнут лимит отправки активных инвайтов в данную группу для отправителя инвайта
		if (isset($count_sender_active_invite_list[$conversation_map])
			&& $count_sender_active_invite_list[$conversation_map]["count_sender_active_invite"] == Type_Invite_Handler::getSendActiveInviteLimit()) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 915, "Active invite send limit exceeded");
		}

		return [];
	}

	// формируем ответ с error_code для trySendBatchingForGroups
	protected function _makeErrorForTrySendBathingForGroups(string $conversation_map, int $error_code, string $error_message):array {

		return [
			"conversation_map" => (string) $conversation_map,
			"error_code"       => (int) $error_code,
			"message"          => (string) $error_message,
		];
	}

	// формируем ответ для list_ok для trySendBatchingForGroups
	protected function _makeListOkItemForTrySendBathingGroups(string $group_conversation_map):array {

		return [
			"conversation_map" => (string) $group_conversation_map,
		];
	}

	// отправляем инвайты
	protected function _sendInviteToEveryGroup(array $conversation_meta_list, int $user_id):void {

		// отправляем инвайт в каждую группу ассинхронно
		foreach ($conversation_meta_list as $v) {

			try {
				Helper_Invites::inviteUserFromSingleWithAsyncMessages($this->user_id, $user_id, $v);
			} catch (cs_InviteIsDuplicated) {
				continue;
			}
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// проверяет что присланный user_id - корректный
	protected function _throwIfUserIdIsMalformed(int $user_id, string $inc_row = null):void {

		if ($user_id < 1) {

			if (!is_null($inc_row)) {
				Gateway_Bus_Statholder::inc("invites", $inc_row);
			}
			throw new ParamException("passed invalid user_id");
		}
	}

	// проверяет что присланный user_id не равен user_id пользователя совершающего запрос
	protected function _throwIfUserIdIsEqualWithYourself(int $user_id, string $inc_row = null):void {

		if ($user_id == $this->user_id) {

			if (!is_null($inc_row)) {
				Gateway_Bus_Statholder::inc("invites", $inc_row);
			}
			throw new ParamException("Passed yourself in user_id parameter");
		}
	}

	// выбрасываем ошибку, если пользователя не существует
	protected function _throwIfUserIsNotExist(int $user_id):void {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new ParamException("dont found user in company cache");
		}
	}

	// получаем мету single диалога
	protected function _getSingleConversationMeta(int $user_id):array {

		$single_conversation_map = Type_Conversation_Single::getMapByUsers($this->user_id, $user_id);
		if ($single_conversation_map) {
			return Type_Conversation_Meta::get($single_conversation_map);
		}

		// создаем сингл-диалог
		return Helper_Single::createIfNotExist($this->user_id, $user_id, true, false);
	}

	// проверяем, имеет ли пользователь доступ к этому инвайту
	protected function _checkIfUserIsAllowedToGetInvite(array $invite_row):void {

		if ($this->user_id != $invite_row["user_id"] && $this->user_id != $invite_row["sender_user_id"]) {
			throw new ParamException("user does not have access to invite");
		}
	}

	// получаем запись из левого меню пользователя
	protected function _getUserLeftMenuRow(int $user_id, string $conversation_map):array {

		return Type_Conversation_LeftMenu::get($user_id, $conversation_map);
	}

	// добавляем action users если приглашение не отклонено или отозвано
	protected function _addActionUsersIfInviteNotIsDeclined(string $status, array $users, string $row = null):void {

		// проверяем что приглашение отклонено/отозвано
		if (Type_Invite_Handler::isDeclined($status)) {

			if (!is_null($row)) {
				Gateway_Bus_Statholder::inc("invites", $row);
			}
			return;
		}

		$this->action->users(array_keys($users));
	}

	// проверяет подпись по пользовтелям при батч вызовах
	protected static function _verifyBatchingList(string $signature, array $batch_user_list):void {

		// если массив пустой
		if (count($batch_user_list) < 1) {

			Gateway_Bus_Statholder::inc("invites", "row102");
			throw new ParamException("passed empty batch_user_list");
		}

		// если в массиве слишком много пользователей
		if (count($batch_user_list) > self::_MAX_USERS_COUNT) {

			Gateway_Bus_Statholder::inc("invites", "row103");
			throw new ParamException("passed batch_user_list more than max: " . self::_MAX_USERS_COUNT);
		}

		// если пришла некорректная подпись
		if (!Type_Conversation_Utils::verifySignatureWithCustomSalt($batch_user_list, $signature, SALT_ALLOWED_USERS_FOR_INVITE)) {

			Gateway_Bus_Statholder::inc("invites", "row104");
			throw new ParamException(__METHOD__ . " wrong signature");
		}
	}
}