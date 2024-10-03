<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * контроллер для сокет методов класса conversations
 */
class Socket_Conversations extends \BaseFrame\Controller\Socket {

	/** @var int код возврата ошибки, дубликат ид сообщения */
	protected const _ERROR_CODE_DUPLICATE_MESSAGE = 10511;

	protected const _MAX_REPOSTED_MESSAGES_COUNT_IN_CHUNK = 15;  // маскисмальное количество сообщений в одном репостнутом сообщении
	protected const _MAX_CONVERSATION_MAP_LIST_IN_CHUNK   = 100; // максимальное количество диалогов пользователя

	// поддерживаемые методы. регистр не имеет значение */
	public const ALLOW_METHODS = [
		"addCallMessage",
		"addMediaConferenceMessage",
		"getMessageData",
		"getMessageDataForSendRemind",
		"getMessage",
		"getMessageList",
		"getUsers",
		"getUsersByConversationList",
		"getUsersIdListForShareWikiPage",
		"getMetaForCreateThread",
		"getMetaForMigrationCreateThread",
		"addThreadToMessage",
		"addRepostFromThread",
		"addRepostFromThreadV2",
		"addRepostFromThreadBatching",
		"checkIsAllowedForCall",
		"updateProfileDataToSphinxGroupMemberList",
		"createPublicForUserId",
		"isCanCommitWorkedHours",
		"tryCommitWorkedHoursFromThread",
		"getLinkListFromText",
		"addRespect",
		"tryEditMessageText",
		"tryExactingFromThread",
		"tryDeleteMessageList",
		"sendMessageWithSharedPageList",
		"getConversationType",
		"doAutoCommitWorkedHours",
		"getPublicHeroesConversationMap",
		"getConversationCardList",
		"addAchievement",
		"getManagedByMapList",
		"isUserCanSendInvitesInGroups",
		"addDismissalRequestMessage",
		"getConversationInfoList",
		"getHiringConversationMap",
		"getConversationDataForCreateThreadInHireRequest",
		"getHiringConversationUserIdList",
		"hideThreadForUser",
		"revealThread",
		"joinToGroupConversationList",
		"addThreadFileListToConversation",
		"addThreadFileListToHiringConversation",
		"hideThreadFileList",
		"deleteThreadFileList",
		"clearConversationsForUser",
		"checkClearConversationsForUser",
		"doRead",
		"updateThreadsUpdatedData",
		"getThreadsUpdatedVersion",
		"getRepostMessages",
		"confirmThreadRepost",
		"getDynamicForThread",
		"sendMessage",
		"sendMessageToUser",
		"getHiringRequestMessageMaps",
		"getDismissalRequestMessageMaps",
		"createRemindOnMessage",
		"sendRemindMessage",
		"actualizeTestRemindForMessage",
		"attachPreview",
		"deletePreviewList",
		"hidePreviewList",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для добавления в группы
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function joinToGroupConversationList():array {

		$user_id               = $this->post(\Formatter::TYPE_INT, "user_id");
		$conversation_map_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_map_list");

		//  добавляем в группы
		$output = Domain_Conversation_Scenario_Socket::joinToGroupConversationList($this->user_id, $user_id, $conversation_map_list);

		if ($output !== false) {

			return $this->error(10120, "not_found", [
				"ok_list"           => (array) $output["ok_list"],
				"is_not_exist_list" => (array) $output["is_not_exist_list"],
				"is_not_owner_list" => (array) $output["is_not_owner_list"],
				"is_not_group_list" => (array) $output["is_not_group_list"],
				"is_leaved_list"    => (array) $output["is_leaved_list"],
				"is_kicked_list"    => (array) $output["is_kicked_list"],
			]);
		}

		return $this->ok();
	}

	/**
	 * добавляем в диалог сообщение-звонок
	 *
	 * @throws \busException
	 * @throws \parseException
	 * @throws \paramException|\cs_RowIsEmpty
	 */
	public function addCallMessage():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$call_map         = $this->post(\Formatter::TYPE_STRING, "call_map");
		$platform         = $this->post(\Formatter::TYPE_STRING, "platform", Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM);

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, можем ли писать этому пользователю
		try {
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted) {
			return $this->error(10021, "You can't write to this conversation because your opponent is blocked in our system");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		// формируем сообщение
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeCall($this->user_id, $call_map, $platform);

		try {

			Helper_Conversations::addMessage(
				$conversation_map,
				$message, $meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"]
			);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		}

		return $this->ok();
	}

	/**
	 * добавляем в диалог сообщение-конференцию
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \returnException
	 */
	public function addMediaConferenceMessage():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$conference_id    = $this->post(\Formatter::TYPE_STRING, "conference_id");
		$status           = $this->post(\Formatter::TYPE_STRING, "status");
		$link             = $this->post(\Formatter::TYPE_STRING, "link");
		$conference_code  = $this->post(\Formatter::TYPE_STRING, "conference_code");
		$platform         = $this->post(\Formatter::TYPE_STRING, "platform", Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM);

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, можем ли писать этому пользователю
		try {
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted) {
			return $this->error(10021, "You can't write to this conversation because your opponent is blocked in our system");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		// формируем сообщение
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeMediaConference(
			$this->user_id, $conference_id, $status, $link, $conference_code, $platform
		);

		try {

			Helper_Conversations::addMessage(
				$conversation_map,
				$message, $meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"]
			);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		}

		return $this->ok();
	}

	/**
	 * метод для получения даты сообщения
	 *
	 * @throws \parseException|\paramException
	 */
	public function getMessageData():array {

		$message_map      = $this->post("?s", "message_map");
		$is_need_reaction = $this->post("?s", "is_need_reaction", false);

		// проверяем что сообщение из диалога
		$this->_throwIfMessageMapNotFromConversation($message_map);

		// пробуем получить мессадж дату
		try {
			$message_data = Helper_Conversations::getMessageData($this->user_id, $message_map, $is_need_reaction);
		} catch (cs_Message_UserHaveNotPermission) {
			return $this->error(10101, "User have not access to message");
		} catch (cs_Message_IsNotExist) {
			return $this->error(10100, "Message block is not found");
		}

		return $this->ok([
			"message_data" => (array) $message_data,
		]);
	}

	/**
	 * получаем данные сообщения для отправки Напоминания
	 *
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws ParamException
	 */
	public function getMessageDataForSendRemind():array {

		$message_map = $this->post(\Formatter::TYPE_STRING, "message_map");

		// проверяем что сообщение из диалога
		$this->_throwIfMessageMapNotFromConversation($message_map);

		// пробуем получить данные сообщения
		try {
			$message_data = Domain_Conversation_Action_Message_GetMessageDataForSendRemind::do($message_map, $this->user_id);
		} catch (cs_Message_UserHaveNotPermission) {
			return $this->error(10101, "User have not access to message");
		} catch (cs_Message_IsNotExist) {
			return $this->error(10100, "Message block is not found");
		}

		return $this->ok([
			"message_data" => (array) $message_data,
		]);
	}

	/**
	 * метод для получения сообщения
	 *
	 * @throws \parseException|\paramException
	 */
	public function getMessage():array {

		$message_map = $this->post("?s", "message_map");

		// проверяем что сообщение из диалога
		$this->_throwIfMessageMapNotFromConversation($message_map);

		// пробуем получить мессадж дату
		try {
			$message_data = Helper_Conversations::getMessageData($this->user_id, $message_map, true);
		} catch (cs_Message_UserHaveNotPermission) {
			return $this->error(10101, "User have not access to message");
		} catch (cs_Message_IsNotExist) {
			return $this->error(10100, "Message block is not found");
		}
		$message = $message_data["message"];

		$prepared_message = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy(
			$message, $this->user_id, $message_data["thread_rel"], $message_data["reaction_user_list"], $message_data["last_reaction_edited"], true
		);
		$output           = [
			"users"   => (array) Type_Conversation_Message_Main::getHandler($message)::getUsers($message),
			"message" => (object) Apiv1_Format::conversationMessage($prepared_message),
		];
		return $this->ok($output);
	}

	/**
	 * метод для получения списка сообщений
	 *
	 * @return mixed
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getMessageList():array {

		$message_map_list = $this->post("?a", "message_map_list");

		// проверяем что корректность каждого map из списка сообщений
		foreach ($message_map_list as $v) {
			$this->_throwIfMessageMapNotFromConversation($v);
		}

		// получаем все необходимые данные о сообщениях
		[$message_data_list, $not_access_message_map_list, $thread_rel_list] = Helper_Messages::getMessageDataListFromAnotherConversationsAttachReaction(
			$this->user_id, $message_map_list
		);

		$output = [
			"message_list"                => (array) [],
			"not_access_message_map_list" => (array) $not_access_message_map_list,
		];

		// достаем сообщения и подготавливаем к ответу
		foreach ($message_data_list as $v) {

			$message          = $v["message"];
			$prepared_message = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy(
				$message,
				$this->user_id,
				$thread_rel_list,
				$v["reaction_user_list"],
				$v["last_reaction_edited"],
				true,
			);

			$output["message_list"][] = [
				"users"   => (array) Type_Conversation_Message_Main::getHandler($message)::getUsers($message),
				"message" => (object) Apiv1_Format::conversationMessage($prepared_message),
			];
		}
		return $this->ok($output);
	}

	/**
	 * получить список пользователей диалога по его conversation_map
	 *
	 * @return array
	 * @throws ParamException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getUsers():array {

		$conversation_map = $this->post("?s", "conversation_map");

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что имеем право взаимодействовать с диалогом
		try {
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {
			return $this->_returnErrorCodeWithAllowStatusByCustomException($e);
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		if (Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {

			$group_administrator_id_list = Domain_Conversation_Action_GetGroupAdministrators::do($meta_row);
			$meta_row                    = self::_updateUserRolesForGroups($meta_row, $group_administrator_id_list);
		}

		// получаем user_list
		$users = Type_Conversation_Meta_Users::formatUsersForThread($meta_row["users"]);

		return $this->ok([
			"users" => (array) $users,
		]);
	}

	/**
	 * получаем пользователей сразу нескольких диалогов
	 *
	 * @throws ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getUsersByConversationList():array {

		$conversation_map_list = $this->post("?a", "conversation_map_list");

		$conversation_map_list = array_unique($conversation_map_list);

		// получаем список мет диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// проверяем, что имеем право взаимодействовать с диалогами
		$allowed_meta_list                = [];
		$not_access_conversation_map_list = [];
		foreach ($meta_list as $v) {

			try {
				Helper_Conversations::checkIsAllowed($v["conversation_map"], $v, $this->user_id);
			} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted|Domain_Conversation_Exception_Guest_AttemptInitialConversation) {

				// добавляем map диалога в список недоступных для пользователя
				$not_access_conversation_map_list[] = $v["conversation_map"];
				continue;
			}

			$allowed_meta_list[] = $v;
		}

		// получаем user_list для каждого доступного для пользователя диалога
		$users_by_conversation = [];
		foreach ($allowed_meta_list as $v) {

			if (Type_Conversation_Meta::isSubtypeOfGroup($v["type"])) {

				$group_administrator_id_list = Domain_Conversation_Action_GetGroupAdministrators::do($v);
				$v                           = self::_updateUserRolesForGroups($v, $group_administrator_id_list);
			}

			$users_by_conversation[$v["conversation_map"]] = Type_Conversation_Meta_Users::formatUsersForThread($v["users"]);
		}

		return $this->ok([
			"users_by_conversation"            => (array) $users_by_conversation,
			"not_access_conversation_map_list" => (array) $not_access_conversation_map_list,
		]);
	}

	/**
	 * Обновить роли администраторов всех групп
	 *
	 * @param array $meta_row
	 * @param array $group_administrator_id_list
	 *
	 * @return array
	 */
	protected static function _updateUserRolesForGroups(array $meta_row, array $group_administrator_id_list):array {

		foreach ($group_administrator_id_list as $group_administrator_id) {

			$meta_row["users"][$group_administrator_id] =
				Type_Conversation_Meta_Users::setUserRole($meta_row["users"][$group_administrator_id], Type_Conversation_Meta_Users::ROLE_OWNER);
		}

		return $meta_row;
	}

	/**
	 * Получаем идентификаторы оппонентов для того чтобы поделиться заметками
	 *
	 * @throws \parseException|\paramException|cs_DecryptHasFailed
	 */
	public function getUsersIdListForShareWikiPage():array {

		$conversation_key_list = $this->post("?a", "conversation_key_list");
		$conversation_map_list = [];

		foreach ($conversation_key_list as $conversation_key) {
			$conversation_map_list[] = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		}

		// получаем список мет диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// проходимся по каждому диалогу
		foreach ($meta_list as $meta_row) {

			// если в такой диалог нельзя делиться
			if (!Type_Conversation_Action::isValidForAction($meta_row["type"], Type_Conversation_Action::WIKI_PAGE_SHARING)) {
				return $this->error(10090, "wiki page sharing not allowed in one of conversation");
			}
		}

		// получаем пользователей каждого диалога
		$users_id_list_by_conversation = [];
		foreach ($meta_list as $v) {
			$users_id_list_by_conversation[$v["conversation_map"]] = Type_Conversation_Meta_Users::getUserIdListSortedByJoinTime($v["users"]);
		}

		return $this->ok([
			"users_id_list_by_conversation" => (array) $users_id_list_by_conversation,
		]);
	}

	/**
	 * метод проверяет может ли пользователь право создать тред к этому сообщению и если может то отдает meta диалога
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Message_IsNotExist
	 * @throws ParamException
	 */
	public function getMetaForCreateThread():array {

		$message_map      = $this->post(\Formatter::TYPE_STRING, "message_map");
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// проверяем, что пользователь состоит в диалоге
		try {
			$meta_row = Type_Conversation_Meta::get($conversation_map);
		} catch (ParamException) {
			return $this->error(10023, "conversation not found");
		}

		// проверяем, что можно взаимодействовать с диалогом
		if ($this->user_id != REMIND_BOT_USER_ID && !Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(10017, "user should be member of conversation");
		}

		// бот Напоминаний при создании треда должен проходить проверку в любом случае
		if ($this->user_id != REMIND_BOT_USER_ID) {

			try {
				Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);
			} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {
				return $this->_returnErrorCodeWithAllowStatusByCustomException($e);
			} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
				return $this->error($e->getSocketErrorCode(), "action not allowed");
			}
		}

		// запрошенный блок - горячий, не заархивирован
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// проверяем что диалог не закрыт и получаем сообщение
		if ($dynamic_row["is_locked"] == 1) {
			return $this->error(10018, "Conversation is locked");
		}

		$block_row = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $message_map, $dynamic_row);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// если сообщение уже удалено
		if (Type_Conversation_Message_Main::getHandler($message)::isDeleted($message)) {
			return $this->error(10028, "Message is deleted");
		}

		return $this->_getDataForGetMetaForCreateThread($message, $dynamic_row, $conversation_map, $message_map, $meta_row);
	}

	/**
	 * метод проверяет может ли пользователь право создать тред к этому сообщению и если может то отдает meta диалога
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_Message_IsNotExist
	 */
	public function getMetaForMigrationCreateThread():array {

		$message_map      = $this->post(\Formatter::TYPE_STRING, "message_map");
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// проверяем, что пользователь состоит в диалоге
		try {
			$meta_row = Type_Conversation_Meta::get($conversation_map);
		} catch (ParamException) {
			return $this->error(10023, "conversation not found");
		}

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		$block_row = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $message_map, $dynamic_row);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// если сообщение уже удалено
		if (Type_Conversation_Message_Main::getHandler($message)::isDeleted($message)) {
			return $this->error(10028, "Message is deleted");
		}

		return $this->_getDataForGetMetaForCreateThread($message, $dynamic_row, $conversation_map, $message_map, $meta_row);
	}

	/**
	 * функция для того что бы получить мету для треда
	 *
	 * @throws \parseException
	 */
	protected function _getDataForGetMetaForCreateThread(array $message, array $dynamic_row, string $conversation_map, string $message_map, array $meta_row):array {

		// проверяем, быть может уже прикреплен тред
		try {

			$thread_relation_row = Gateway_Db_CompanyConversation_MessageThreadRel::getOneByMessageMap($conversation_map, $message_map);
			return $this->ok([
				"is_exist"   => (int) 1,
				"thread_map" => (string) $thread_relation_row->thread_map,
			]);
		} catch (\cs_RowIsEmpty) {
			//ничего не делаем
		}

		$message_created_at = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);

		$table_id = \CompassApp\Pack\Conversation::getTableId($conversation_map);
		return $this->_makeGetMetaForCreateThreadOutput($meta_row, $message_created_at, $table_id, $dynamic_row, $message);
	}

	/**
	 * функция для форматирования ответа метода getMetaForCreateThread
	 *
	 * @throws \parseException
	 */
	protected function _makeGetMetaForCreateThreadOutput(array $meta_row, int $message_created_at, int $table_id, array $dynamic_row, array $message):array {

		// получаем идентификатор отправителя сообщения, к которому пытаются добавить тред
		$creator_user_id             = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);
		$creator_clear_until         = Domain_Conversation_Entity_Dynamic::getClearUntil(
			$dynamic_row["user_clear_info"],
			$dynamic_row["conversation_clear_info"],
			$creator_user_id
		);
		$message_hidden_by_user_list = Type_Conversation_Message_Main::getHandler($message)::getHiddenByUserIdList($message);
		$time_at                     = time();

		$user_clear_info = Domain_Conversation_Entity_Dynamic::getClearInfoFormattedForThread($dynamic_row["user_clear_info"], $dynamic_row["conversation_clear_info"]);

		return $this->ok([
			"conversation_type"           => (int) $meta_row["type"],
			"is_exist"                    => (int) 0,
			"conversation_meta_row"       => (array) $meta_row,
			"users"                       => (array) Type_Conversation_Meta_Users::formatUsersForThread($meta_row["users"]),
			"creator_user_id"             => (int) $creator_user_id,
			"message_created_at"          => (int) $message_created_at,
			"table_id"                    => (int) $table_id,
			"message_hidden_by_user_list" => (array) $message_hidden_by_user_list,
			"creator_clear_until"         => (int) $creator_clear_until,
			"user_mute_info"              => (array) Domain_Conversation_Entity_Dynamic::getMuteInfoFormattedForThread($dynamic_row["user_mute_info"], $time_at),
			"user_clear_info"             => (array) $user_clear_info,
		]);
	}

	/**
	 * метод для добавляения треда к сообщению
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addThreadToMessage():array {

		$message_map                    = $this->post(\Formatter::TYPE_STRING, "message_map");
		$thread_map                     = $this->post(\Formatter::TYPE_STRING, "thread_map");
		$is_thread_hidden_for_all_users = $this->post(\Formatter::TYPE_INT, "is_thread_hidden_for_all_users", 0);

		// получаем conversation_map сообщения
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		if (Type_Conversation_Meta::isSubtypeOfPublicGroup($meta_row["type"])) {
			return $this->error(10018, "Conversation is locked");
		}

		// получаем dynamic диалога
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// если диалог закрыт
		if ($dynamic_row["is_locked"] == 1) {
			return $this->error(10018, "Conversation is locked");
		}

		$thread_map = Domain_Conversation_Action_Message_Thread_Add::do($conversation_map, $thread_map, $message_map, $is_thread_hidden_for_all_users);

		return $this->ok([
			"thread_map" => (string) $thread_map,
		]);
	}

	/**
	 * метод формирующий и добавляющий в диалог сообщение типа репост из треда
	 *
	 * @throws \busException
	 * @throws \parseException
	 * @throws \paramException|\cs_RowIsEmpty
	 */
	public function addRepostFromThread():array {

		$conversation_map      = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$client_message_id     = $this->post(\Formatter::TYPE_STRING, "client_message_id");
		$text                  = $this->post(\Formatter::TYPE_STRING, "text");
		$platform              = $this->post(\Formatter::TYPE_STRING, "platform", Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM);
		$reposted_message_list = $this->post(\Formatter::TYPE_ARRAY, "reposted_message_list");
		$parent_message_data   = $this->post(\Formatter::TYPE_ARRAY, "parent_message_data", []);
		$is_add_repost_quote   = $this->post(\Formatter::TYPE_INT, "is_add_repost_quote", 0);

		Gateway_Bus_Statholder::inc("conversations", "row320");

		// получаем мету диалога, проверяем что пользователь участник и статус allow_status позволяет взаимодействовать с диалогом
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("conversations", "row321");
			return $this->error(10011, "You can't write to this conversation because you isn`t conversation member");
		}
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		try {

			$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::ADD_REPOST_FROM_CONVERSATION);
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled $e) {

			Gateway_Bus_Statholder::inc("conversations", "row322");
			if ($is_add_repost_quote == 1) {
				return $this->_doReturnErrorIfBlockedUser($e);
			}
			return $this->_doReturnAllowStatusInError($meta_row);
		} catch (cs_ConversationTypeIsNotValidForAction) {
			return $this->error(10013, "You can not perform this action with this conversation");
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			return $this->error(2118001, "You can't write to this conversation because your opponent delete account");
		} catch (cs_Conversation_UserbotIsDisabled) {
			return $this->error(2134001, "You can't write to this conversation because userbot is disabled");
		} catch (cs_Conversation_UserbotIsDeleted) {
			return $this->error(2134002, "You can't write to this conversation because userbot is deleted");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		// отправляем
		$message = $this->_formatThreadMessage($text, $client_message_id, $reposted_message_list, $parent_message_data, $platform);
		$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		return $this->_addThreadRepostMessage($message, $conversation_map, $meta_row, $text);
	}

	/**
	 * метод формирующий и добавляющий в диалог сообщение типа репост из треда V2
	 *
	 * @throws \busException
	 * @throws \parseException
	 * @throws \paramException|\cs_RowIsEmpty
	 */
	public function addRepostFromThreadV2():array {

		$conversation_map  = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$client_message_id = $this->post(\Formatter::TYPE_STRING, "client_message_id");
		$text              = $this->post(\Formatter::TYPE_STRING, "text");
		$repost_list       = $this->post(\Formatter::TYPE_ARRAY, "repost_list");
		$platform          = $this->post(\Formatter::TYPE_STRING, "platform", Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM);

		// получаем мету диалога, проверяем что пользователь участник диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(10011, "You can't write to this conversation because you isn`t conversation member");
		}

		// проверяем что статус allow_status позволяет взаимодействовать с диалогом
		try {

			$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::ADD_REPOST_FROM_CONVERSATION);
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted) {
			return $this->_doReturnAllowStatusInError($meta_row);
		} catch (cs_ConversationTypeIsNotValidForAction) {
			return $this->error(10013, "You can not perform this action with this conversation");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		// получаем список упомянутых
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		// подготавливаем сообщения для репоста из треда
		$repost_list = $this->_prepareChunkRepostMessageListFromThread($repost_list, $text, $client_message_id, $mention_user_id_list, $platform);

		// отправляем сообщения репостов
		try {

			$message_list = Helper_Conversations::addMessageList(
				$conversation_map, $repost_list, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]
			);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		} catch (cs_Message_DuplicateClientMessageId) {
			return $this->error(static::_ERROR_CODE_DUPLICATE_MESSAGE, "passed duplicate client message id");
		}

		$prepared_message_list = [];
		foreach ($message_list as $message) {

			// форматируем сообщения и добавлем их к ответу
			$prepared_message        = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy($message);
			$prepared_message_list[] = (object) Apiv1_Format::conversationMessage($prepared_message);
		}

		return $this->ok([
			"message_list" => (array) $prepared_message_list,
		]);
	}

	/**
	 * подготавливаем сообщения для репоста из треда
	 *
	 * @throws \parseException
	 */
	protected function _prepareChunkRepostMessageListFromThread(array $repost_list, string $text, string $client_message_id, array $mention_user_id_list, string $platform):array {

		$message_list = [];
		foreach ($repost_list as $k => $reposted_message_list) {

			// текст нужен только у первого сообщения
			if ($k !== 0) {
				$text = "";
			}

			// подготавливаем сообщения для репоста из треда
			$prepared_reposted_message_list = Type_Conversation_Message_Handler_Default::prepareThreadMessageListBeforeRepost($reposted_message_list);

			// создаем сообщение типа тред-репост
			$handler_class = Type_Conversation_Message_Main::getLastVersionHandler();
			$message       = $handler_class::makeThreadRepost(
				$this->user_id, $text, $client_message_id . "_" . $k, $prepared_reposted_message_list, [], $platform
			);

			$message_list[] = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}

		return $message_list;
	}

	/**
	 * возвращаем код ошибки и текст ошибки, если пользователь заблокирован
	 *
	 * @param $e
	 *
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	protected function _doReturnErrorIfBlockedUser($e):array {

		$error = Helper_Conversations::getCheckIsAllowedErrorForSocket($e);
		return $this->error($error["error_code"], $error["message"]);
	}

	/**
	 * добавляем в код ошибки allow_status
	 *
	 */
	protected function _doReturnAllowStatusInError(array $meta_row):array {

		// получаем id собеседника
		$opponent_user_id = Type_Conversation_Meta_Users::getOpponentId($this->user_id, $meta_row["users"]);

		// получаем allow_status чтобы его передать в threads при репосте
		$allow_status = Type_Conversation_Utils::getAllowStatus($meta_row["allow_status"], $meta_row["extra"], $opponent_user_id);
		return $this->error(10010, "You can't write to this conversation because one of participants blocked another", [
			"allow_status" => (int) $allow_status,
		]);
	}

	/**
	 * метод для отправки репоста
	 *
	 */
	protected function _addThreadRepostMessage(array $message, string $conversation_map, array $meta_row, string $text):array {

		// добавляем сообщение в диалог и оповещаем пользователей
		try {

			$message = Helper_Conversations::addMessage(
				$conversation_map,
				$message,
				$meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"]);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		} catch (cs_Message_DuplicateClientMessageId) {
			return $this->error(static::_ERROR_CODE_DUPLICATE_MESSAGE, "passed duplicate client message id");
		}

		// форматируем сообщение для возвращения в метод
		$formatted_message = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForFormatLegacy($message);
		$formatted_message = Apiv1_Format::conversationMessage($formatted_message);

		Gateway_Bus_Statholder::inc("conversations", "row324");

		return $this->ok([
			"message" => (object) $formatted_message,
		]);
	}

	/**
	 * метод формирующий и добавляющий в диалог сообщение типа репост из треда
	 *
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	public function addRepostFromThreadBatching():array {

		$conversation_map      = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$client_message_id     = $this->post(\Formatter::TYPE_STRING, "client_message_id");
		$text                  = $this->post(\Formatter::TYPE_STRING, "text");
		$reposted_message_list = $this->post(\Formatter::TYPE_ARRAY, "reposted_message_list");
		$parent_message_data   = $this->post(\Formatter::TYPE_ARRAY, "parent_message_data", []);
		$platform              = $this->post(\Formatter::TYPE_STRING, "platform", Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM);

		Gateway_Bus_Statholder::inc("conversations", "row320");

		$this->_throwIfIncorrectClientMessageId($client_message_id);
		$this->_throwIfIncorrectMessageList($reposted_message_list);

		// получаем мету диалога, проверяем что пользователь участник и статус allow_status позволяет взаимодействовать с диалогом
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("conversations", "row321");
			return $this->error(10011, "You can't write to this conversation because you isn`t conversation member");
		}
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		try {

			$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::ADD_REPOST_FROM_CONVERSATION);
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted) {
			return $this->_doReturnAllowStatusInErrorLegacy($meta_row);
		} catch (cs_ConversationTypeIsNotValidForAction) {
			return $this->error(10013, "You can not perform this action with this conversation");
		} catch (cs_Message_DuplicateClientMessageId) {
			return $this->error(static::_ERROR_CODE_DUPLICATE_MESSAGE, "passed duplicate client message id");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		try {

			$response_message_list = $this->_addMessages(
				$reposted_message_list, $text, $client_message_id, $conversation_map, $parent_message_data, $meta_row, $mention_user_id_list, $platform
			);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		}

		return $this->ok([
			"message_list" => (array) $response_message_list,
		]);
	}

	/**
	 * Метод формирующий сообщения для репоста в тред
	 * Без записи в базу истории репоста - мы еще не уверены, что репост улетел в тред
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_MessageList_IsEmpty
	 */
	public function getRepostMessages():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$message_map_list = $this->post(\Formatter::TYPE_ARRAY, "message_map_list");

		$meta_row = Domain_Conversation_Action_GetMeta::do($conversation_map);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(10011, "user is not member of conversation");
		}

		try {
			$reposted_message_list = Helper_Conversations::prepareMessageListForRepost($this->user_id, $conversation_map, $message_map_list);
		} catch (BlockException) {
			return $this->error(10018, "conversation is locked");
		} catch (Domain_Conversation_Exception_Message_IsFromDifferentConversation) {
			throw new ParamException("messages are from different conversations");
		} catch (cs_MessageList_IsEmpty) {
			return $this->error(2418001, "message list is empty");
		} catch (cs_Message_Limit) {
			return $this->error(552, "exceeded the limit on the number of quoted messages");
		}

		return $this->ok([
			"message_list" => (array) $reposted_message_list,
		]);
	}

	/**
	 * Метод для подтверждения репоста в тред
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_UnpackHasFailed
	 */
	public function confirmThreadRepost():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$message_map_list = $this->post(\Formatter::TYPE_ARRAY, "message_map_list");
		$thread_map       = $this->post(\Formatter::TYPE_STRING, "thread_map");

		$set = [];

		foreach ($message_map_list as $v) {

			if (\CompassApp\Pack\Message\Conversation::getConversationMap($v) == $conversation_map) {

				$set[] = [
					"conversation_map"    => $conversation_map,
					"message_map"         => $v,
					"receiver_thread_map" => $thread_map,
					"user_id"             => $this->user_id,
					"is_deleted"          => 0,
					"created_at"          => time(),
					"updated_at"          => 0,
					"deleted_at"          => 0,
				];
			}
		}

		// если что то репостнули по итогу, добавляем в базу
		if (count($set) > 0) {
			Gateway_Db_CompanyConversation_MessageRepostThreadRel::insertArray($set);
		}

		return $this->ok();
	}

	// добавляем в код ошибки allow_status
	protected function _doReturnAllowStatusInErrorLegacy(array $meta_row):array {

		// получаем id собеседника
		$opponent_user_id = Type_Conversation_Meta_Users::getOpponentId($this->user_id, $meta_row["users"]);

		// получаем allow_status чтобы его передать в threads при репосте
		$allow_status = Type_Conversation_Utils::getAllowStatus($meta_row["allow_status"], $meta_row["extra"], $opponent_user_id);

		Gateway_Bus_Statholder::inc("conversations", "row322");
		return $this->error(10010, "You can't write to this conversation because one of participants blocked another",
			["allow_status" => (int) $allow_status]);
	}

	// делаем репост сообщений
	protected function _addMessages(array $reposted_message_list, string $text, string $client_message_id, string $conversation_map, array $parent_message_data, array $meta_row, array $mention_user_id_list, string $platform):array {

		$chunked_reposted_message_list = array_chunk($reposted_message_list, self::_MAX_REPOSTED_MESSAGES_COUNT_IN_CHUNK);

		// для каждого блока из 15 сообщений
		$response_message_list = [];
		foreach ($chunked_reposted_message_list as $k => $v) {

			// чтобы текст был только у первого сообщения
			if ($k != 0) {
				$text = "";
			}

			// формируем свой $client_message_id для каждого сообщения на которое разбиваем
			$chunked_client_message_id = $client_message_id . "_" . $k;

			$message = $this->_formatThreadMessageLegacy($text, $chunked_client_message_id, $v, $parent_message_data, $platform);
			$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

			$response_message_list[] = $this->_addThreadRepostMessageLegacy($message, $conversation_map, $meta_row);
		}

		return $response_message_list;
	}

	// форматируем message типа thread_message
	protected function _formatThreadMessageLegacy(string $text, string $client_message_id, array $reposted_message_list, array $parent_message, string $platform):array {

		// для каждого сообщения из reposted_message_list
		foreach ($reposted_message_list as $k => $v) {

			// формируем стандартную структуру сообщения
			$reposted_message = $this->_createStandardMessageStructureLegacy($v);

			// подготавливаем сообщение к записи и записываем в сообщение в массив
			$reposted_message_list[$k] = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForInsert($reposted_message, $v["message_map"]);
		}

		// создаем сообщение типа тред-репост
		return Type_Conversation_Message_Main::getLastVersionHandler()
			::makeThreadRepost($this->user_id, $text, $client_message_id, $reposted_message_list, $parent_message, $platform);
	}

	// метод для создания стандартной стуктуры сообщения
	protected function _createStandardMessageStructureLegacy(array $v):array {

		$sender_user_id     = Type_Thread_Message_Main::getHandler($v)::getSenderUserId($v);
		$message_text       = Type_Thread_Message_Main::getHandler($v)::getText($v);
		$client_message_id  = Type_Thread_Message_Main::getHandler($v)::getClientMessageId($v);
		$message_created_at = Type_Thread_Message_Main::getHandler($v)::getCreatedAt($v);
		$platform           = Type_Thread_Message_Main::getHandler($v)::getPlatform($v);

		$thread_message_type = Type_Thread_Message_Main::getHandler($v)::getType($v);
		switch ($thread_message_type) {

			case THREAD_MESSAGE_TYPE_TEXT:

				$mention_user_id_list = Type_Thread_Message_Main::getHandler($v)::getMentionUserIdList($v);

				$reposted_message = Type_Conversation_Message_Main::getLastVersionHandler()
					::makeThreadRepostItemText($sender_user_id, $message_text, $client_message_id, $message_created_at, $mention_user_id_list, $platform);
				break;

			case THREAD_MESSAGE_TYPE_FILE:

				$file_map = Type_Thread_Message_Main::getHandler($v)::getFileMap($v);

				$reposted_message = Type_Conversation_Message_Main::getLastVersionHandler()
					::makeThreadRepostItemFile($sender_user_id, $message_text, $client_message_id, $file_map, $message_created_at, "", $platform);
				break;

			default:

				Gateway_Bus_Statholder::inc("conversations", "row323");
				throw new ParseFatalException("Unknown message type");
		}

		return $reposted_message;
	}

	/**
	 * метод для отправки репоста
	 *
	 */
	protected function _addThreadRepostMessageLegacy(array $message, string $conversation_map, array $meta_row):array {

		// добавляем сообщение в диалог и оповещаем пользователей
		$message = Helper_Conversations::addMessage(
			$conversation_map,
			$message,
			$meta_row["users"],
			$meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]);

		// форматируем сообщение для возвращения в метод
		$formatted_message = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForFormatLegacy($message);
		$formatted_message = Apiv1_Format::conversationMessage($formatted_message);
		Gateway_Bus_Statholder::inc("conversations", "row324");

		return $formatted_message;
	}

	/**
	 * Метод для проверки можем ли звонить пользователю
	 *
	 * @throws BlockException
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public function checkIsAllowedForCall():array {

		$opponent_user_id = $this->post("?i", "opponent_user_id");
		$user_id          = $this->post("?i", "user_id");
		$method_version   = $this->post("?i", "method_version", 2);

		try {
			Domain_Member_Entity_Permission::check($this->user_id, Permission::IS_CALL_ENABLED);
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(10022, "Action not allowed");
		}

		// достаем запись из cluster_user_conversation_uniq
		$single_conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $opponent_user_id);
		if (!$single_conversation_map) {

			// попытаемся создать диалог
			try {

				$meta_row = Helper_Single::createIfNotExist(
					$user_id, $opponent_user_id, false, true, true, method_version: $method_version, is_forcing_creation: true
				);
			} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
				return $this->error($e->getSocketErrorCode(), "Action not allowed");
			}

			return $this->ok([
				"conversation_map" => (string) $meta_row["conversation_map"],
			]);
		}

		// проверяем, можем ли писать этому пользователю
		$meta_row = Type_Conversation_Meta::get($single_conversation_map);

		try {

			Domain_Member_Entity_Permission::checkSingle($user_id, $method_version, $single_conversation_map);
			Helper_Conversations::checkIsAllowed($single_conversation_map, $meta_row, $user_id);
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted) {
			return $this->error(10021, "You can't write to this conversation because your opponent is blocked in our system");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(10022, "Action not allowed");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		return $this->ok([
			"conversation_map" => (string) $single_conversation_map,
		]);
	}

	/**
	 * обновляем пользовательские данные в сфинксе для групп, где пользователь числится участником
	 *
	 * @throws \parseException|\paramException
	 */
	public function updateProfileDataToSphinxGroupMemberList():array {

		$user_id = $this->post("?i", "user_id");

		// получаем всё левое меню пользователя
		$all_left_menu_list = Type_Conversation_LeftMenu::getAllLeftMenuList($user_id);

		// собираем список групповых диалогов, где пользователь числится участником
		$conversation_map_list = [];
		foreach ($all_left_menu_list as $v) {

			if ($v["type"] != CONVERSATION_TYPE_GROUP_DEFAULT) {
				continue;
			}

			$conversation_map_list[] = $v["conversation_map"];
		}

		// если в левом меню у пользователя отсутствуют группы, то просто возвращаем ok
		if (count($conversation_map_list) < 1) {
			return $this->ok();
		}

		// разбиваем полученный список групповых диалогов по частям
		$chunked_conversation_map_list = array_chunk($conversation_map_list, self::_MAX_CONVERSATION_MAP_LIST_IN_CHUNK);

		// отправляем задачи на обновление данных пользователя в тех группах, где тот числится участником
		foreach ($chunked_conversation_map_list as $conversation_map_list) {
			Type_Phphooker_Main::updateProfileDataToSphinxGroupMember($user_id, $conversation_map_list);
		}

		return $this->ok();
	}

	/**
	 * создает public диалог для переданного user_id
	 *
	 * @throws \parseException|\queryException
	 */
	public function createPublicForUserId():array {

		// проверяем, может нужнный диалог уже имеется
		try {
			$user_conversation_rel_obj = Type_UserConversation_UserConversationRel::get($this->user_id, CONVERSATION_TYPE_PUBLIC_DEFAULT);
		} catch (\cs_RowIsEmpty) {

			// создаем public диалог и возвращаем conversation_map в ответе
			$meta_row = Helper_Public::create($this->user_id);

			return $this->ok([
				"conversation_map" => (string) $meta_row["conversation_map"],
			]);
		}

		return $this->ok([
			"conversation_map" => (string) $user_conversation_rel_obj->conversation_map,
		]);
	}

	/**
	 * проверяем, может ли диалог фиксировать рабочее время
	 *
	 * @throws \parseException|\paramException
	 */
	public function isCanCommitWorkedHours():array {

		$conversation_map = $this->post("?s", "conversation_map");

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		return $this->ok([
			"is_can_commit_worked_hours" => (int) Type_Conversation_Meta_Extra::isCanCommitWorkedHours($meta_row["extra"]) ? 1 : 0,
		]);
	}

	/**
	 * пытаемся зафиксировать отработанные часы
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function tryCommitWorkedHoursFromThread():array {

		$selected_message_list = $this->post("?a", "selected_message_list");
		$parent_message_data   = $this->post("?a", "parent_message_data", []);
		$worked_hours          = $this->post("?f", "worked_hours");

		if (ServerProvider::isOnPremise()) {
			throw new \ParseException("action not allowed on on-premise environment");
		}

		// создаем объект worked_hours
		$worked_hours_data = Type_Conversation_Public_WorkedHours::doCommit($this->user_id, $worked_hours);

		// форматируем сообщения из треда в сообщения диалога
		try {

			$forwarding_message_list = Type_Conversation_Message_Handler_Default::transferThreadMessageListToConversationMessageStructure(
				$selected_message_list, $parent_message_data
			);
		} catch (cs_Message_Limit) {
			return $this->error(6000, "message limit exceeded");
		}

		// достаем ключ диалога "Личный Heroes" пользователя
		try {
			$user_conversation_rel_obj = Type_UserConversation_UserConversationRel::get($this->user_id, CONVERSATION_TYPE_PUBLIC_DEFAULT);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("row with public conversation not found");
		}

		// отправляем сообщения в чат "Личный Heroes"
		$message_list = Helper_Conversations::doForwardMessageList(
			$this->user_id,
			$user_conversation_rel_obj->conversation_map,
			$forwarding_message_list,
			$worked_hours_data["worked_hours_id"],
			$worked_hours_data["day_start_at_iso"],
			$worked_hours_data["worked_hours_created_at"]
		);

		// приводим сообщения к формату
		return $this->_prepareResponse($message_list);
	}

	/**
	 * подготавливаем список сообщений к ответу
	 *
	 * @throws \parseException
	 */
	protected function _prepareResponse(array $message_list):array {

		$formatted_message_list = [];
		$user_list              = [];
		foreach ($message_list as $message) {

			// получаем пользователей из сообщения
			$users     = Type_Conversation_Message_Main::getHandler($message)::getUsers($message);
			$user_list = array_merge($user_list, $users);

			// форматируем сообщения и добавлем их к ответу
			$prepared_message         = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy($message);
			$formatted_message_list[] = (object) Apiv1_Format::conversationMessage($prepared_message);
		}

		return $this->ok([
			"message_list" => (array) $formatted_message_list,
			"user_list"    => (array) $user_list,
		]);
	}

	/**
	 * получаем список ссылок из текста
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getLinkListFromText():array {

		$text             = $this->post("?s", "text");
		$creator_user_id  = $this->post("?i", "creator_user_id");
		$opposite_user_id = $this->post("?i", "opposite_user_id");
		$entity_type      = $this->post("?i", "entity_type");
		$user_list        = $this->post("?a", "user_list");
		$entity_id        = $this->post("?i", "entity_id");

		if (strlen($text) < 1) {

			return $this->ok([
				"link_list" => (array) [],
			]);
		}

		// крепим ссылки
		$link_list = Type_Preview_Producer::addTaskIfLinkExistInText($creator_user_id, $opposite_user_id, $user_list, $text, $entity_type, $entity_id);

		return $this->ok([
			"link_list" => (array) $link_list,
		]);
	}

	/**
	 * добавляем сообщение-респект в чат
	 *
	 * @throws \blockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addRespect():array {

		$receiver_user_id = $this->post(\Formatter::TYPE_INT, "receiver_user_id");
		$respect_id       = $this->post(\Formatter::TYPE_INT, "respect_id");
		$respect_text     = $this->post(\Formatter::TYPE_STRING, "respect_text");

		// проверяем параметры на корректность
		if ($receiver_user_id < 1 || $respect_id < 1) {
			throw new ParamException("incorrect params");
		}

		// проверяем, что получатель сообщения существует в системе
		$this->_getUserInfo($receiver_user_id);

		// получаем map группы Респекты
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMap(Type_Company_Default::RESPECT);

		// получаем meta диалога, куда отправляются Респекты
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что групповой диалог
		if (!Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {
			return $this->error(10022, "dialog does not have type is group");
		}

		// проверяем, что участник диалога
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(10017, "user should be member of conversation");
		}

		// добавляем респект в чат
		try {
			$message_map = $this->_addRespectToChat($receiver_user_id, $respect_id, $respect_text, $meta_row);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		}

		return $this->ok([
			"message_map" => (string) $message_map,
		]);
	}

	/**
	 * респект в чат
	 *
	 * @param int    $receiver_user_id
	 * @param int    $respect_id
	 * @param string $respect_text
	 * @param array  $meta_row
	 *
	 * @return string
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	protected function _addRespectToChat(int $receiver_user_id, int $respect_id, string $respect_text, array $meta_row):string {

		// формируем сообщение-текст
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeRespect($this->user_id, $respect_text, generateUUID());

		// добавляем additional-поля для сущности респекта
		$message = Type_Conversation_Message_Main::getHandler($message)::attachRespectData($message, $respect_id, $receiver_user_id);

		// отправляем сообщение-респект в чат
		try {

			$message_list = Helper_Conversations::addMessageList(
				$meta_row["conversation_map"],
				[$message],
				$meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"]
			);
		} catch (cs_ConversationIsLocked) {
			throw new BlockException(__METHOD__ . ": conversation is locked");
		} catch (cs_Message_DuplicateClientMessageId) {
			throw new ParamException("client_message_id is duplicated");
		}

		return $message_list[0]["message_map"];
	}

	/**
	 * пробуем отредактировать текст сообщения
	 *
	 * @throws \blockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function tryEditMessageText():array {

		$user_id       = $this->post(\Formatter::TYPE_INT, "user_id");
		$message_map   = $this->post(\Formatter::TYPE_STRING, "message_map");
		$new_text      = $this->post(\Formatter::TYPE_STRING, "new_text");
		$is_force_edit = $this->post(\Formatter::TYPE_INT, "is_force_edit", 0) == 1;

		// проверяем параметры на корректность
		if ($user_id < 1 || mb_strlen($message_map) < 1 || mb_strlen($new_text) < 1) {
			throw new ParamException("incorrect params");
		}

		// заменяем emoji на :short_name: и очищаем текст от левака
		$new_text = Type_Api_Filter::replaceEmojiWithShortName($new_text);
		$new_text = Type_Api_Filter::sanitizeMessageText($new_text);

		// проверяем, что полученный текст не слишком длинный
		if (mb_strlen($new_text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(10052, "Message is too long");
		}

		// проверяем, что пользователь существует в системе
		$this->_getUserInfo($user_id);

		// получаем conversation_map диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// достаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что пользователь является участником диалога
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			return $this->error(10017, "user should be member of conversation");
		}

		// получаем список упомянутых из текста
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $new_text);

		// пробуем отредактировать сообщение
		try {
			Helper_Conversations::editMessageText($conversation_map, $message_map, $user_id, $new_text, $meta_row, $mention_user_id_list, $is_force_edit);
		} catch (cs_Message_IsEmptyText) {
			throw new ParamException("Empty text");
		} catch (cs_Message_IsDeleted) {
			return $this->error(10028, "Message is deleted");
		} catch (cs_Message_UserHaveNotPermission) {
			return $this->error(10101, "User have not access to message");
		} catch (cs_Message_IsNotAllowForEdit) {
			return $this->error(10050, "You are NOT allowed to do this action");
		} catch (cs_Message_TimeIsOver) {
			return $this->error(10051, "Timed out for edit message");
		}

		return $this->ok();
	}

	/**
	 * проявляем требовательность из треда
	 *
	 * @throws \parseException
	 * @throws \returnException|\paramException
	 */
	public function tryExactingFromThread():array {

		$selected_message_list = $this->post(\Formatter::TYPE_ARRAY, "selected_message_list");
		$parent_message_data   = $this->post(\Formatter::TYPE_ARRAY, "parent_message_data");
		$user_id_list          = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		// получаем ключ диалога Требовательность
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMap(Type_Company_Default::EXACTINGNESS);

		// получаем мету диалог
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// если наш пользователь не является участником группы Требовательность
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			return $this->error(10011, "user is not member of group");
		}

		// для каждого сообщения из reposted_message_list
		$reposted_message_list = $this->_prepareBeforeRepost($selected_message_list);

		// добавляем Требовательность в карточку пользователя
		[$exactingness_id_list_by_user_id, $week_count, $month_count] = Gateway_Socket_Company::addExactingnessToEmployeeCard($this->user_id, $user_id_list);

		// для каждого выбранного для Требовательности пользователя
		$message_list = [];
		foreach ($exactingness_id_list_by_user_id as $receiver_id => $exactingness_id) {

			// создаем сообщение типа тред-репост
			$thread_repost = Type_Conversation_Message_Main::getLastVersionHandler()
				::makeThreadRepost($this->user_id, "", generateUUID(), $reposted_message_list, $parent_message_data);

			// добавляем additional-поля для Требовательности, прикрепляя пользователя, которому предъявляем & id требовательности
			$message_list[] = Type_Conversation_Message_Main::getHandler($thread_repost)
				::attachExactingnessData($thread_repost, $receiver_id, $exactingness_id);
		}

		// добавляем репост в чат Требовательность
		Helper_Conversations::tryExacting($this->user_id, $message_list, $conversation_map, $meta_row, $week_count, $month_count);

		return $this->ok([
			"conversation_map" => (string) $conversation_map,
		]);
	}

	/**
	 * пробуем удалить сообщения в диалоге
	 *
	 * @throws \parseException|\paramException
	 */
	public function tryDeleteMessageList():array {

		$message_map_list = $this->post("?a", "message_map_list");
		$conversation_map = $this->post("?s", "conversation_map");
		$is_force_delete  = $this->post("?i", "is_force_delete", 0) == 1;

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// если пользователь не является участником диалога, то возвращаем ошибку
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(10011, "user is not member of conversation");
		}

		Gateway_Bus_CompanyCache::getMember($this->user_id);

		// пробуем удалить сообщения
		try {
			Helper_Conversations::deleteMessageList($this->user_id, $conversation_map, $meta_row["type"], $message_map_list, $meta_row, $is_force_delete);
		} catch (cs_Message_UserHaveNotPermission) {
			return $this->error(10101, "User have not access to message");
		} catch (cs_Message_IsNotAllowForDelete) {
			return $this->error(10050, "You are NOT allowed to do this action");
		} catch (cs_Message_IsTimeNotAllowToDelete) {
			return $this->error(10051, "Timed out for delete message");
		} catch (cs_ConversationIsLocked) {

		}

		return $this->ok();
	}

	/**
	 * получить тип диалога
	 *
	 * @throws \parseException|\paramException
	 */
	public function getConversationType():array {

		$conversation_map = $this->post("?s", "conversation_map");

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		return $this->ok([
			"conversation_type" => (int) $meta_row["type"],
		]);
	}

	/**
	 * автофиксация рабочих часов
	 *
	 * @throws \busException
	 * @throws \parseException|\paramException
	 */
	public function doAutoCommitWorkedHours():array {

		$worked_hours        = $this->post("?f", "worked_hours");
		$user_id_list        = $this->post("?ai", "user_id_list");
		$auto_commit_time_at = $this->post("?i", "auto_commit_time_at");

		// получаем информацию для запрошенных пользователей
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($user_id_list);

		// проходимся по ним
		foreach ($user_info_list as $user_info) {

			// если пользователь заблочен или не человек, то пропускаем
			if (\CompassApp\Domain\Member\Entity\Member::isDisabledProfile($user_info->role) || !Type_User_Main::isHuman($user_info->npc_type)) {
				continue;
			}

			$user_id = $user_info->user_id;

			// получаем временную метку когда начался прошлый день, фиксировать рабочие часы будем к нему
			$past_day_start_at = dayStart(dayStart() - HOUR1);

			// создаем данные рабочих часов за вчерашний день
			$worked_hours_data = Type_Conversation_Public_WorkedHours::doCommit($user_id, $worked_hours, $past_day_start_at, true);

			// формируем сообщение для автофиксации в личном чате Heroes
			$forwarding_message = Type_Conversation_Message_Main::getLastVersionHandler()
				::makeText($user_id, "Время отправлено в личный Heroes автоматически.", generateUUID(), Type_Conversation_Message_Handler_Default::SYSTEM_PLATFORM);

			// время создания сообщения устанавливаем определенное время
			$forwarding_message = Type_Conversation_Message_Main::getHandler($forwarding_message)::changeCreatedAt($forwarding_message, $auto_commit_time_at);

			// добавляем в additional-поля данные рабочих часов
			$forwarding_message = Type_Conversation_Message_Main::getHandler($forwarding_message)::attachWorkedHours(
				$forwarding_message,
				$worked_hours_data["worked_hours_id"],
				$worked_hours_data["day_start_at_iso"],
				$worked_hours_data["worked_hours_created_at"],
				false
			);

			// получаем meta личного чата Heroes
			try {
				$user_conversation_rel_obj = Type_UserConversation_UserConversationRel::get($user_id, CONVERSATION_TYPE_PUBLIC_DEFAULT);
			} catch (\cs_RowIsEmpty) {
				throw new ParseFatalException("row with public conversation not found");
			}
			$conversation_map = $user_conversation_rel_obj->conversation_map;
			$meta_row         = Type_Conversation_Meta::get($conversation_map);

			// отправляем сообщение в Личный чат Heroes пользователя
			Helper_Conversations::addMessageList(
				$conversation_map, [$forwarding_message], $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"], false
			);
		}

		return $this->ok();
	}

	/**
	 * получаем ключ диалога пользователя по типу диалога
	 *
	 * @throws ParamException
	 * @throws \parseException
	 */
	public function getPublicHeroesConversationMap():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// получаем значение типа нужного нам диалога
		$conversation_type = Type_UserConversation_UserConversationRel::getTypeByName("public_heroes");

		// достаем объект пользовательского диалога
		try {
			$user_conversation_rel_obj = Type_UserConversation_UserConversationRel::get($user_id, $conversation_type);
		} catch (\cs_RowIsEmpty) {
			return $this->error(10023, "row with public conversation not found");
		}

		return $this->ok([
			"conversation_map" => (string) $user_conversation_rel_obj->conversation_map,
		]);
	}

	/**
	 * Получаем данные по диалогам для карточки
	 *
	 * @throws ParamException
	 */
	public function getConversationCardList():array {

		$executor_user_id = $this->post(\Formatter::TYPE_INT, "executor_user_id");
		$opponent_user_id = $this->post(\Formatter::TYPE_INT, "opponent_user_id");

		[$single_conversation, $heroes_conversation] = Domain_Conversation_Scenario_Socket::getConversationCardList($executor_user_id, $opponent_user_id);

		return $this->ok([
			"single_conversation" => (array) $single_conversation,
			"heroes_conversation" => (array) $heroes_conversation,
		]);
	}

	/**
	 * добавляем сообщение-достижение в чат
	 *
	 * @throws \blockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addAchievement():array {

		$receiver_user_id = $this->post(\Formatter::TYPE_INT, "receiver_user_id");
		$achievement_id   = $this->post(\Formatter::TYPE_INT, "achievement_id");
		$message_text     = $this->post(\Formatter::TYPE_STRING, "message_text");
		$platform         = $this->post(\Formatter::TYPE_STRING, "platform", Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM);

		// проверяем параметры на корректность
		if ($receiver_user_id < 1 || $achievement_id < 1 || mb_strlen($message_text) < 1) {
			throw new ParamException("incorrect params");
		}

		// проверяем, что получатель сообщения существует в системе
		$this->_getUserInfo($receiver_user_id);

		// получаем map группы Достижения
		try {
			$conversation_map = Type_Company_Default::getCompanyGroupConversationMap(Type_Company_Default::ACHIEVEMENT);
		} catch (\Exception|\Error $e) {

			Type_System_Admin::log("achievement_group_found_fail", $e->getMessage());

			// на случай если в компании не нашлась такая группа
			return $this->ok([
				"message_map" => (string) "",
			]);
		}

		// получаем meta диалога, куда отправляются Достижения
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что групповой диалог
		if (!Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {
			return $this->error(10022, "dialog does not have type is group");
		}

		// проверяем, что участник диалога
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(10017, "user should be member of conversation");
		}

		// добавляем достижение в чат
		try {
			$message_map = $this->_addAchievementToChat($receiver_user_id, $achievement_id, $message_text, $meta_row, $platform);
		} catch (cs_ConversationIsLocked) {
			return $this->error(10018, "Conversation is locked");
		}

		return $this->ok([
			"message_map" => (string) $message_map,
		]);
	}

	/**
	 * достижение в чат
	 *
	 * @throws \blockException
	 * @throws \paramException
	 * @throws \parseException
	 */
	protected function _addAchievementToChat(int $receiver_user_id, int $achievement_id, string $message_text, array $meta_row, string $platform):string {

		// формируем сообщение-текст
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($this->user_id, $message_text, generateUUID(), $platform);

		// добавляем additional-поля для сущности Достижения
		$message = Type_Conversation_Message_Main::getHandler($message)::attachAchievementData($message, $achievement_id, $receiver_user_id);

		// отправляем сообщение-достижение в чат
		try {

			$message_list = Helper_Conversations::addMessageList(
				$meta_row["conversation_map"],
				[$message],
				$meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"]
			);
		} catch (cs_ConversationIsLocked) {
			throw new BlockException(__METHOD__ . ": conversation is locked");
		} catch (cs_Message_DuplicateClientMessageId) {
			throw new ParamException("client_message_id is duplicated");
		}

		return $message_list[0]["message_map"];
	}

	/**
	 * Получить по массиву ключей список диалогов, управляемых пользователем
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getManagedByMapList():array {

		$user_id               = $this->post(\Formatter::TYPE_INT, "user_id");
		$conversation_map_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_map_list");

		[
			$can_send_invite_conversation_map_list,
			$cannot_send_invite_conversation_map_list,
			$leaved_member_conversation_map_list,
			$kicked_member_conversation_map_list,
			$not_exist_in_company_conversation_map_list,
			$not_group_conversation_map_list,
		] = Domain_Conversation_Scenario_Socket::getManagedByMapList($user_id, $conversation_map_list);

		return $this->ok([
			"can_send_invite_conversation_map_list"      => (array) $can_send_invite_conversation_map_list,
			"cannot_send_invite_conversation_map_list"   => (array) $cannot_send_invite_conversation_map_list,
			"leaved_member_conversation_map_list"        => (array) $leaved_member_conversation_map_list,
			"kicked_member_conversation_map_list"        => (array) $kicked_member_conversation_map_list,
			"not_exist_in_company_conversation_map_list" => (array) $not_exist_in_company_conversation_map_list,
			"not_group_conversation_map_list"            => (array) $not_group_conversation_map_list,
		]);
	}

	/**
	 * Возвращаем conversation_map чата "Наймы и увольнения"
	 *
	 * @return array
	 * @throws \parseException
	 */
	public function getHiringConversationMap():array {

		// получаем map чата найма/увольнения
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMap(Type_Company_Default::HIRING);

		return $this->ok([
			"conversation_map" => (string) $conversation_map,
		]);
	}

	/**
	 * получаем данные чата найма/увольнения для создания треда заявки
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getConversationDataForCreateThreadInHireRequest():array {

		// получаем map чата найма/увольнения
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMap(Type_Company_Default::HIRING);

		// получаем мету чата
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// получаем dynamic-данные чата
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		$user_mute_info  = Domain_Conversation_Entity_Dynamic::getMuteInfoFormattedForThread($dynamic_row["user_mute_info"], time());
		$user_clear_info = Domain_Conversation_Entity_Dynamic::getClearInfoFormattedForThread($dynamic_row["user_clear_info"], $dynamic_row["conversation_clear_info"]);

		return $this->ok([
			"conversation_map"  => (string) $meta_row["conversation_map"],
			"conversation_type" => (int) $meta_row["type"],
			"users"             => (array) Type_Conversation_Meta_Users::formatUsersForThread($meta_row["users"]),
			"user_mute_info"    => (array) $user_mute_info,
			"user_clear_info"   => (array) $user_clear_info,
		]);
	}

	/**
	 * Проверяем что пользователь может слать инвайты в переданные группы
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function isUserCanSendInvitesInGroups():array {

		$conversation_key_list_to_join = $this->post(\Formatter::TYPE_ARRAY, "conversation_key_list_to_join");

		try {

			// получаем conversation_map_list и conversation_meta_list
			$conversation_map_list = $this->_tryDecryptConversationKeyList($conversation_key_list_to_join);
		} catch (ParamException $e) {
			return $this->error(10019, $e->getMessage());
		}

		$output = [
			"list_ok"    => [],
			"list_error" => [],
		];

		try {

			// проверяем группы из списка
			[$output["list_ok"], $output["list_error"]] = $this->_checkUserCanSendInvitesInGroups($conversation_map_list, $this->user_id);
		} catch (ParseFatalException $e) {
			return $this->error(10120, $e->getMessage());
		}

		return $this->ok($output);
	}

	// преобразуем пришедшие ключи в map
	protected function _tryDecryptConversationKeyList(array $conversation_key_list):array {

		$conversation_map_list = [];
		foreach ($conversation_key_list as $key) {

			// преобразуем key в map
			$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($key);

			// добавляем диалог в массив
			$conversation_map_list[] = $conversation_map;
		}

		return $conversation_map_list;
	}

	/**
	 * проверяем список групп на возможность пользователя приглашать
	 *
	 * @throws \parseException
	 * @long
	 */
	protected function _checkUserCanSendInvitesInGroups(array $conversation_map_list, int $user_id):array {

		$ok_list    = [];
		$error_list = [];

		// получаем данные для всех диалогов сразу
		$meta_and_left_menu_list_by_map         = self::_makeConversationMapMetaLeftMenuList($user_id, $conversation_map_list);
		$conversation_map_exist_in_company_list = array_keys($meta_and_left_menu_list_by_map);

		// формируем ошибки, если диалог не найден в текущей компании
		$error_list = self::_makeConversationNotFoundInCompanyErrorList($error_list, $conversation_map_list, $conversation_map_exist_in_company_list);

		// для каждой группы из списка проверяем
		foreach ($meta_and_left_menu_list_by_map as $conversation_map => $meta_and_left_menu_list_by_map_row) {

			[$is_no_errors, $error_list] = self::_checkIsSubtypeOfGroup($conversation_map, $meta_and_left_menu_list_by_map_row["meta"]["type"], $error_list);

			if ($is_no_errors !== false) {

				[$is_no_errors, $error_list] = self::_checkIsMember(
					$user_id,
					$error_list,
					$conversation_map,
					$meta_and_left_menu_list_by_map_row["meta"],
					$meta_and_left_menu_list_by_map_row["left_menu"]
				);
			}

			if ($is_no_errors !== false) {
				[$is_no_errors, $error_list] = self::_checkIsCanSendInvite($user_id, $error_list, $conversation_map, $meta_and_left_menu_list_by_map_row["meta"]);
			}

			if ($is_no_errors === true) {

				// если нет ошибок, значит пользователь может приглашать
				$ok_list[] = [
					"conversation_key" => \CompassApp\Pack\Conversation::doEncrypt($conversation_map),
				];
			}
		}

		return [$ok_list, $error_list];
	}

	/**
	 * Формирует список, где ключом является conversation_map, в значениях мета диалога и левое меню
	 *
	 */
	protected static function _makeConversationMapMetaLeftMenuList(int $user_id, array $conversation_map_list):array {

		$conversation_map_meta_list  = Type_Conversation_Meta::getAll($conversation_map_list, true);
		$conversation_left_menu_list = Type_Conversation_LeftMenu::getList($user_id, $conversation_map_list);

		$meta_and_left_menu_list_by_map = [];

		foreach ($conversation_left_menu_list as $conversation_left_menu_row) {

			$meta_and_left_menu_list_by_map[$conversation_left_menu_row["conversation_map"]] = [
				"meta"      => $conversation_map_meta_list[$conversation_left_menu_row["conversation_map"]],
				"left_menu" => $conversation_left_menu_row,
			];
		}

		return $meta_and_left_menu_list_by_map;
	}

	/**
	 * формирует ошибки для диалогов, которые не были найдены в компании
	 *
	 * @throws \parseException
	 */
	public static function _makeConversationNotFoundInCompanyErrorList(array $exist_error_list, array $conversation_map_from_post_list, array $conversation_map_after_get_data_list):array {

		$user_can_send_invites_in_groups_error_list = $exist_error_list;

		// находим расхождение исходного списка conversation_map и списка, после запроса данных из компании
		$conversation_map_not_exist_in_company_list = array_diff($conversation_map_from_post_list, $conversation_map_after_get_data_list);

		foreach ($conversation_map_not_exist_in_company_list as $conversation_map_not_exist_in_company_item) {
			$user_can_send_invites_in_groups_error_list[] = self::_makeUserCantSendInviteInGroupError(1208, $conversation_map_not_exist_in_company_item);
		}

		return $user_can_send_invites_in_groups_error_list;
	}

	/**
	 * проверяет, является ли диалог групповым
	 *
	 * @throws \parseException
	 */
	protected static function _checkIsSubtypeOfGroup(string $conversation_map, int $conversation_type, array $exist_error_list):array {

		$user_can_send_invites_in_groups_error_list = $exist_error_list;
		$is_no_errors                               = true;

		if (!Type_Conversation_Meta::isSubtypeOfGroup($conversation_type)) {

			// диалог не является групповым
			$user_can_send_invites_in_groups_error_list[] = self::_makeUserCantSendInviteInGroupError(400, $conversation_map);
			$is_no_errors                                 = false;
		}

		return [$is_no_errors, $user_can_send_invites_in_groups_error_list];
	}

	/**
	 * проверяет, является ли пользователь участником диалоога
	 *
	 * @throws \parseException
	 */
	protected static function _checkIsMember(int $user_id, array $exist_error_list, string $conversation_map, array $meta_row, array $left_menu):array {

		$user_can_send_invites_in_groups_error_list = $exist_error_list;
		$is_no_errors                               = true;

		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

			if (self::_getConversationLeaveReason($left_menu) === Type_Conversation_LeftMenu::LEAVE_REASON_KICKED) {

				// пользователь был исключен из группы
				$user_can_send_invites_in_groups_error_list[] = self::_makeUserCantSendInviteInGroupError(502, $conversation_map);
				$is_no_errors                                 = false;
			} elseif (self::_getConversationLeaveReason($left_menu) === Type_Conversation_LeftMenu::LEAVE_REASON_LEAVED) {

				// пользователь заявки покинул группу
				$user_can_send_invites_in_groups_error_list[] = self::_makeUserCantSendInviteInGroupError(503, $conversation_map);
				$is_no_errors                                 = false;
			} else {
				throw new ParseFatalException("Not valid leave reason");
			}
		}

		return [$is_no_errors, $user_can_send_invites_in_groups_error_list];
	}

	/**
	 * проверяет, может ли пользователь отправлять приглашения в группу (она публичная или он владелец/администратор)
	 *
	 * @throws \parseException
	 */
	protected static function _checkIsCanSendInvite(int $user_id, array $exist_error_list, string $conversation_map, array $meta_row):array {

		$member = Gateway_Bus_CompanyCache::getMember($user_id);

		$user_can_send_invites_in_groups_error_list = $exist_error_list;
		$is_no_errors                               = true;

		// диалог не является диалогом по-умолчанию, в который можно приглашать без наличия прав администратора, владельца
		if (!Type_Company_Default::checkIsDefaultGroupOnAddMember($conversation_map)) {

			if (!Type_Conversation_Meta_Users::isGroupAdmin($member->user_id, $meta_row["users"])) {

				// пользователь не может приглашать в группу
				$user_can_send_invites_in_groups_error_list[] = self::_makeUserCantSendInviteInGroupError(902, $conversation_map);
				$is_no_errors                                 = false;
			}
		}

		return [$is_no_errors, $user_can_send_invites_in_groups_error_list];
	}

	/**
	 * Возвращает причину покидания группы пользователем
	 *
	 * @throws \parseException
	 */
	protected static function _getConversationLeaveReason(array $conversation_left_menu):int {

		$reason = $conversation_left_menu["leave_reason"] ?? false;

		if ($reason === false) {
			throw new ParseFatalException("No leave reason for employee who is no longer in company");
		}

		return $reason;
	}

	// список возможных ошибок с групповыми диалогами при создании заявки на наем
	protected const _USER_HIRING_ADD_GROUP_ERRORS_LIST = [
		400  => [
			"error_code"       => 400,
			"message"          => "Conversation is not a group",
			"conversation_map" => "",
		],
		502  => [
			"error_code"       => 502,
			"message"          => "User was excluded from the group",
			"conversation_map" => "",
		],
		503  => [
			"error_code"       => 503,
			"message"          => "User left group",
			"conversation_map" => "",
		],
		902  => [
			"error_code"       => 902,
			"message"          => "User can't send invite because of role",
			"conversation_map" => "",
		],
		1208 => [
			"error_code"       => 1208,
			"message"          => "Conversation not exist in company",
			"conversation_map" => "",
		],
	];

	/**
	 * Возвращает ошибку в нужном формате для случая, когда пользователь не может отправить инвайт в группу
	 *
	 * @throws \parseException
	 */
	protected static function _makeUserCantSendInviteInGroupError(int $error_code, string $conversation_map):array {

		// список возможных ошибок
		$error_list = self::_USER_HIRING_ADD_GROUP_ERRORS_LIST;

		// ищем ошибку в списке
		$error = $error_list[$error_code] ?? false;

		// если не нашли, значит false и ошибка
		if ($error === false) {
			throw new ParseFatalException("invalid error code " . $error_code . " when make user can't send invite in group error");
		}

		$error["conversation_map"] = $conversation_map;

		return $error;
	}

	/**
	 * добавляем сообщение о увольнении в чат найма и увольнения
	 *
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addDismissalRequestMessage():array {

		$dismissal_request_id = $this->post(\Formatter::TYPE_INT, "dismissal_request_id");
		$dismissal_user_id    = $this->post(\Formatter::TYPE_INT, "dismissal_user_id");

		// получаем map чата найма/увольнения
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMap(Type_Company_Default::HIRING);

		// получаем мету чата
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// отправляем сообщение заявки увольнения
		$hiring_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeDismissalRequest($this->user_id, generateUUID(), $dismissal_request_id);
		$message        = Helper_Conversations::addMessage(
			$meta_row["conversation_map"], $hiring_message,
			$meta_row["users"], $meta_row["type"],
			$meta_row["conversation_name"], $meta_row["extra"],
			false,
			[$dismissal_user_id],
		);

		// создаем тред к заявке
		$thread_map = Gateway_Socket_Thread::addThreadForDismissalRequest($this->user_id, $dismissal_request_id);

		// крепим тред к сообщению
		Domain_Conversation_Action_Message_Thread_Add::do($conversation_map, $thread_map, $message["message_map"], false);

		return $this->ok([
			"conversation_map" => (string) $meta_row["conversation_map"],
			"message_map"      => (string) $message["message_map"],
			"thread_map"       => (string) $thread_map,
		]);
	}

	/**
	 * получить информацию о нескольких диалогах
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getConversationInfoList():array {

		$conversation_key_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_key_list");

		// получаем диалоги
		$conversation_info_list = Domain_Conversation_Scenario_Socket::getConversationInfoList($conversation_key_list);

		return $this->ok([
			"conversation_info_list" => (array) $conversation_info_list,
		]);
	}

	/**
	 * Получаем всех участников чата найм и увольнение
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 */
	public function getHiringConversationUserIdList():array {

		// получаем ключ диалога найма и увольнения
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey(Domain_Company_Entity_Config::HIRING_CONVERSATION_KEY_NAME);

		// получаем участников чата
		$meta_row          = Type_Conversation_Meta::get($conversation_map);
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		return $this->ok([
			"user_id_list"      => (array) $meta_row["users"],
			"talking_user_list" => (array) $talking_user_list,
		]);
	}

	/**
	 * Добавить пользователя в список, скрывших тред
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function hideThreadForUser():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$thread_map       = $this->post(\Formatter::TYPE_STRING, "thread_map");

		Domain_Conversation_Scenario_Socket::hideThreadForUser($this->user_id, $conversation_map, $thread_map);

		return $this->ok();
	}

	/**
	 * Очистить список пользователей, скрывших тред
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function revealThread():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$thread_map       = $this->post(\Formatter::TYPE_STRING, "thread_map");

		Domain_Conversation_Scenario_Socket::revealThread($conversation_map, $thread_map);

		return $this->ok();
	}

	/**
	 * Метод для добавления списка файлов в диалог
	 *
	 * @throws cs_Message_IsNotExist
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addThreadFileListToConversation():array {

		$conversation_map         = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$message_map              = $this->post(\Formatter::TYPE_STRING, "message_map");
		$need_add_file_list       = $this->post(\Formatter::TYPE_ARRAY, "need_add_file_list");
		$conversation_message_map = $this->post(\Formatter::TYPE_STRING, "conversation_message_map");

		Domain_Conversation_Scenario_Socket::addThreadFileListToConversation($conversation_map, $message_map, $conversation_message_map, $need_add_file_list);
		return $this->ok();
	}

	/**
	 * Метод для добавления списка файлов в диалог найма и увольнения
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addThreadFileListToHiringConversation():array {

		$conversation_map   = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$message_map        = $this->post(\Formatter::TYPE_STRING, "message_map");
		$need_add_file_list = $this->post(\Formatter::TYPE_ARRAY, "need_add_file_list");
		$created_at         = $this->post(\Formatter::TYPE_INT, "created_at");

		Domain_Conversation_Scenario_Socket::addThreadFileListToHiringConversation($conversation_map, $message_map, $need_add_file_list, $created_at);
		return $this->ok();
	}

	/**
	 * Метод для скрытия файлов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function hideThreadFileList():array {

		$file_uuid_list = $this->post(\Formatter::TYPE_ARRAY, "file_uuid_list");

		Domain_Conversation_Scenario_Socket::hideThreadFileList($file_uuid_list, $this->user_id);
		return $this->ok();
	}

	/**
	 * Метод для удаления файлов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function deleteThreadFileList():array {

		$file_uuid_list = $this->post(\Formatter::TYPE_ARRAY, "file_uuid_list");

		Domain_Conversation_Scenario_Socket::deleteThreadFileList($file_uuid_list);
		return $this->ok();
	}

	/**
	 * Метод для удаления пользователя из диалогов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function clearConversationsForUser():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$limit   = $this->post(\Formatter::TYPE_INT, "limit", 500);
		$offset  = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$is_complete = Domain_Conversation_Scenario_Socket::clearConversationsForUser($user_id, $limit, $offset);
		return $this->ok([
			"is_complete" => (bool) $is_complete,
		]);
	}

	/**
	 * Проверяем что пользователя нет в диалогах
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function checkClearConversationsForUser():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$limit   = $this->post(\Formatter::TYPE_INT, "limit", 500);
		$offset  = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$is_cleared = Domain_Conversation_Scenario_Socket::checkClearConversationsForUser($user_id, $limit, $offset);
		return $this->ok([
			"is_cleared" => (bool) $is_cleared,
		]);
	}

	/**
	 * отметить диалог прочитанным
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function doRead():array {

		$user_local_date = $this->post(\Formatter::TYPE_STRING, "user_local_date");
		$user_local_time = $this->post(\Formatter::TYPE_STRING, "user_local_time");
		$message_key     = $this->post(\Formatter::TYPE_STRING, "message_key", "");
		$message_map     = "";
		if (!isEmptyString($message_key)) {
			$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		}

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key", "");
		$conversation_map = "";
		if (!isEmptyString($conversation_key)) {
			$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		}

		Gateway_Bus_Statholder::inc("conversations", "row241");
		$this->_checkParamsForDoRead($message_map, $conversation_map);

		// поулчаем conversation_map если передали сообщение
		if (isEmptyString($conversation_key)) {
			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		}

		// читаем диалог
		try {

			Domain_Conversation_Scenario_Api::doRead($this->user_id, $conversation_map, $message_map, $user_local_date, $user_local_time);
		} catch (cs_LeftMenuRowIsNotExist) {
			throw new ParamException("action is not allowed");
		}

		return $this->ok();
	}

	// обновляем временную метку и версию обновления тредов
	public function updateThreadsUpdatedData():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");

		$dynamic = Domain_Conversation_Scenario_Socket::updateThreadsUpdatedData($conversation_map);

		return $this->ok([
			"threads_updated_version" => (int) $dynamic->threads_updated_version,
		]);
	}

	/**
	 * получаем версию обновления тредов
	 *
	 * @throws ParamException
	 * @throws \cs_RowIsEmpty
	 */
	public function getThreadsUpdatedVersion():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");

		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		return $this->ok([
			"threads_updated_version" => (int) $dynamic->threads_updated_version,
		]);
	}

	// получаем динамические данные треда
	public function getDynamicForThread():array {

		$conversation_map = $this->post("?s", "conversation_map");

		$meta_row    = Gateway_Db_CompanyConversation_ConversationMeta::getOne($conversation_map);
		$dynamic_row = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		return $this->ok([
			"location_type"           => (int) $meta_row->type,
			"user_clear_info"         => (string) toJson($dynamic_row->user_clear_info),
			"user_mute_info"          => (string) toJson($dynamic_row->user_mute_info),
			"conversation_clear_info" => (string) toJson($dynamic_row->conversation_clear_info),
		]);
	}

	/**
	 * отправляем сообщуху
	 *
	 * @throws ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_UserIsNotMember
	 */
	public function sendMessage():array {

		$sender_user_id   = $this->post(\Formatter::TYPE_INT, "sender_user_id");
		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$text             = $this->post(\Formatter::TYPE_STRING, "text");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		// получаем информацию о диалоге и проверяем что юзер его участник
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::ADD_MESSAGE_FROM_CONVERSATION);
		if (!Type_Conversation_Meta_Users::isMember($sender_user_id, $meta_row["users"])) {
			throw new cs_UserIsNotMember("User is not conversation member");
		}

		// готовим сообщуху
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($sender_user_id, $text, generateUUID(), Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM);
		$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		// отправляем сообщение
		Helper_Conversations::addMessageList(
			$meta_row["conversation_map"],
			[$message],
			$meta_row["users"],
			(int) $meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]
		);

		return $this->ok();
	}

	/**
	 * отправляем сообщение пользователю
	 *
	 * @throws ParamException
	 * @throws \parseException
	 */
	public function sendMessageToUser():array {

		$user_id          = $this->post(\Formatter::TYPE_INT, "user_id");
		$opponent_user_id = $this->post(\Formatter::TYPE_INT, "opponent_user_id");
		$text             = $this->post(\Formatter::TYPE_STRING, "text");

		$meta_row = Helper_Single::createIfNotExist($user_id, $opponent_user_id, false, true, true);

		// готовим сообщуху
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($user_id, $text, generateUUID());
		$message = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);

		// отправляем сообщение
		Helper_Conversations::addMessageList(
			$meta_row["conversation_map"],
			[$message],
			$meta_row["users"],
			(int) $meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]
		);

		return $this->ok();
	}

	/**
	 * Возвращает сообщения, с которыми связаны указанные заявки на наем.
	 *
	 * @return void
	 * @throws ParamException
	 */
	public function getHiringRequestMessageMaps():array {

		$hiring_request_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "hiring_request_id_list");
		$date_from              = $this->post(\Formatter::TYPE_INT, "date_from");

		$result = Domain_Conversation_Scenario_Socket::getHiringRequestMessageRels($date_from, ...$hiring_request_id_list);

		return $this->ok([
			"hiring_request_conversation_map_rel" => (array) $result,
		]);
	}

	/**
	 * Возвращает сообщения, с которыми связаны указанные заявки на увольнения.
	 *
	 * @return void
	 * @throws ParamException
	 */
	public function getDismissalRequestMessageMaps():array {

		$dismissal_request_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "dismissal_request_id_list");
		$date_from                 = $this->post(\Formatter::TYPE_INT, "date_from");

		$result = Domain_Conversation_Scenario_Socket::getDismissalRequestMessageRels($date_from, ...$dismissal_request_id_list);

		return $this->ok([
			"dismissal_request_conversation_map_rel" => (array) $result,
		]);
	}

	/**
	 * создаём Напоминание к сообщению диалога
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function createRemindOnMessage():array {

		$message_map = $this->post(\Formatter::TYPE_STRING, "message_map");
		$remind_at   = $this->post(\Formatter::TYPE_INT, "remind_at");
		$comment     = $this->post(\Formatter::TYPE_STRING, "comment");

		try {
			$remind_id = Domain_Conversation_Scenario_Socket::createRemindOnMessage($this->user_id, $message_map, $remind_at, $comment);
		} catch (cs_Message_IsDeleted) {
			return $this->error(2418003, "Message is deleted");
		} catch (Domain_Conversation_Exception_Message_NotAllowForRemind|Domain_Conversation_Exception_Message_NotAllowForUser) {
			return $this->error(2418004, "You are not allowed to do this action");
		} catch (cs_Message_IsTooLong) {
			return $this->error(2418005, "Comment text is too long");
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {
			return $this->_returnErrorCodeWithAllowStatusByCustomException($e);
		} catch (cs_UserIsNotMember) {
			return $this->error(2418001, "User is not member of conversation");
		} catch (Domain_Remind_Exception_AlreadyExist) {
			return $this->error(2435001, "Remind already set in message");
		} catch (cs_Message_IsNotExist) {
			return $this->error(2418007, "Message is deleted");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getSocketErrorCode(), "action not allowed");
		}

		return $this->ok([
			"remind_id" => (int) $remind_id,
		]);
	}

	/**
	 * отправляем сообщение-Напоминание в чат
	 *
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws ParamException
	 */
	public function sendRemindMessage():array {

		$message_map = $this->post(\Formatter::TYPE_STRING, "message_map");
		$comment     = $this->post(\Formatter::TYPE_STRING, "comment");

		try {
			Domain_Conversation_Scenario_Socket::sendRemindMessage($message_map, $comment);
		} catch (cs_Message_IsDeleted|Domain_Remind_Exception_AlreadyRemoved) {
			// обработчик события не смог отправить сообщение - ничего не делаем в случае ошибки
		} catch (cs_Message_IsTooLong) {

			// а вот это странно - кидаем ошибку
			return $this->error(2418005, "Comment text is too long");
		}

		return $this->ok();
	}

	/**
	 * актуализируем данные Напоминания для сообщения-оригинала
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function actualizeTestRemindForMessage():array {

		$message_map = $this->post(\Formatter::TYPE_STRING, "message_map");

		assertTestServer();

		Domain_Conversation_Scenario_Socket::actualizeTestRemindForMessage($message_map);

		return $this->ok();
	}

	/**
	 * Прикрепить превью за чатом
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public function attachPreview():array {

		$thread_message_map       = $this->post(\Formatter::TYPE_STRING, "thread_message_map");
		$conversation_message_map = $this->post(\Formatter::TYPE_STRING, "conversation_message_map");
		$message_created_at       = $this->post(\Formatter::TYPE_INT, "message_created_at");
		$preview_map              = $this->post(\Formatter::TYPE_STRING, "preview_map");
		$link_list                = $this->post(\Formatter::TYPE_ARRAY, "link_list");

		Domain_Conversation_Scenario_Socket::attachPreview(
			$this->user_id, $thread_message_map, $conversation_message_map, $preview_map, $message_created_at, $link_list);

		return $this->ok();
	}

	/**
	 * Удалить превью из чата
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function deletePreviewList():array {

		$thread_message_map_list = $this->post(\Formatter::TYPE_ARRAY, "thread_message_map_list");

		Domain_Conversation_Scenario_Socket::deletePreviewList($thread_message_map_list);

		return $this->ok();
	}

	/**
	 * Спрятать превью в чате
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 */
	public function hidePreviewList():array {

		$thread_message_map_list = $this->post(\Formatter::TYPE_ARRAY, "thread_message_map_list");

		Domain_Conversation_Scenario_Socket::hidePreviewList($this->user_id, $thread_message_map_list);

		return $this->ok();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * проверяем что пришел валидный client_message_id
	 *
	 * @throws \paramException
	 */
	protected function _throwIfIncorrectClientMessageId(string $client_message_id):void {

		// проверяем message_id на коррекнтность
		if (strlen($client_message_id) < 1) {
			throw new ParamException("got incorrect client_message_id in request");
		}
	}

	/**
	 * проверяем что пришел валидный $message_list
	 *
	 * @throws \paramException
	 */
	protected function _throwIfIncorrectMessageList(array $message_list):void {

		// проверяем массив пришедших событий
		if (count($message_list) < 1) {
			throw new ParamException("passed empty message_list");
		}
	}

	/**
	 * функция проверяет, что message_map принадлежит диалогу
	 *
	 * @throws \parseException
	 */
	protected function _throwIfMessageMapNotFromConversation(string $message_map):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParseFatalException("passed not conversation message map");
		}
	}

	/**
	 * возвращаем код ошибки в случае вместе с allow_status, если allow_status single-диалога отличен от ALLOW_STATUS_OK
	 *
	 * @throws \parseException
	 */
	protected function _returnErrorCodeWithAllowStatusByCustomException(\Exception $e):array {

		// в зависимости от класса исключения
		$exception_class = get_class($e);
		$allow_status    = match ($exception_class) {

			cs_Conversation_MemberIsDisabled::class                    => Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED,
			Domain_Conversation_Exception_User_IsAccountDeleted::class => Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DELETED,
			cs_Conversation_UserbotIsDisabled::class                   => Type_Conversation_Utils::ALLOW_STATUS_USERBOT_IS_DISABLED,
			cs_Conversation_UserbotIsDeleted::class                    => Type_Conversation_Utils::ALLOW_STATUS_USERBOT_IS_DELETED,
			default                                                    => throw new ParseFatalException(__METHOD__ . ": passed unhandled exception class"),
		};

		return $this->error(10010, "single conversations is blocked", [
			"allow_status" => (int) $allow_status,
		]);
	}

	/**
	 * получаем информацию о пользователе
	 *
	 * @param int $user_id
	 *
	 * @return \CompassApp\Domain\Member\Struct\Short
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _getUserInfo(int $user_id):\CompassApp\Domain\Member\Struct\Short {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new ParamException(__METHOD__ . ": user not found");
		}
		return $user_info_list[$user_id];
	}

	/**
	 * форматируем message типа thread_message
	 *
	 * @throws \parseException
	 */
	protected function _formatThreadMessage(string $text, string $client_message_id, array $reposted_message_list, array $parent_message, string $platform):array {

		// для каждого сообщения из reposted_message_list
		$prepared_reposted_message_list = $this->_prepareBeforeRepost($reposted_message_list);

		// создаем сообщение типа тред-репост
		return Type_Conversation_Message_Main::getLastVersionHandler()
			::makeThreadRepost($this->user_id, $text, $client_message_id, $prepared_reposted_message_list, $parent_message, $platform);
	}

	/**
	 * подготавливаем сообщения перед репостом
	 *
	 * @throws \parseException
	 */
	protected function _prepareBeforeRepost(array $message_list):array {

		$message_index = 1;

		// для каждого сообщения из reposted_message_list
		$prepared_message_list = [];
		foreach ($message_list as $k => $v) {

			// формируем стандартную структуру сообщения
			$reposted_message = Type_Conversation_Message_Main::getLastVersionHandler()::createStandardMessageStructureV2($v, $message_index);

			// инкрементим индекс
			$message_index++;

			// подготавливаем сообщение к записи и записываем в сообщение в массив
			$prepared_message_list[$k] = Type_Conversation_Message_Main::getLastVersionHandler()::prepareForInsert($reposted_message, $v["message_map"]);
		}

		return $prepared_message_list;
	}

	// бросам исключение, если диалог не подходит для этого действия
	protected function _throwIfConversationTypeIsNotValidForAction(int $conversation_type, string $action, string $namespace = "", string $row = ""):void {

		// проверяем, что действия валидно
		if (!Type_Conversation_Action::isValidForAction($conversation_type, $action)) {

			// при необходимости инкрементим статистику
			if ($namespace != "" && $row != "") {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			$error_code = Type_Conversation_Action::getIsValidForActionErrorCode($action);
			throw new cs_ConversationTypeIsNotValidForAction("$error_code");
		}
	}

	// валидируем параметры
	protected function _checkParamsForDoRead(string $message_map, string $conversation_map):void {

		// если передали оба параметра или не передали ни одного
		if ((mb_strlen($message_map) > 0 && mb_strlen($conversation_map) > 0) || (mb_strlen($message_map) < 1 && mb_strlen($conversation_map) < 1)) {
			throw new ParamException("bad params");
		}

		// если передали только message_map
		if (mb_strlen($message_map) > 0 && mb_strlen($conversation_map) < 1) {

			// если сообщение не из диалога выбрасываем exception
			$this->_throwIfMessageMapIsNotFromConversation($message_map, "conversations", "row244");
		}
	}

	// проверяем, что message_map из диалога
	protected function _throwIfMessageMapIsNotFromConversation(string $message_map, string $namespace, string $row):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {

			Gateway_Bus_Statholder::inc($namespace, $row);
			throw new ParamException("the message is not from conversation");
		}
	}
}
