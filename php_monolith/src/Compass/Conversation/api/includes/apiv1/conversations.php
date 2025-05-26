<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\PaymentRequiredException;
use CompassApp\Domain\Member\Entity\Permission;
use JetBrains\PhpStorm\ArrayShape;

/**
 * контроллер, отвечающий за взаимодействие пользователя с диалогами и сообщениями в них
 * @property Type_Api_Action action
 */
class Apiv1_Conversations extends \BaseFrame\Controller\Api {

	public const    MAX_SELECTED_MESSAGE_COUNT        = 100;      // максимальное количество сообщений, с которыми можно что-то делать за раз (репостнуть, удалить, скрыть и т.п.)
	public const    MAX_USER_ID_LIST_FOR_EXACTINGNESS = 30;       // максимальное количество пользователей для Требовательности
	protected const _MAX_GET_ALLOWED_COUNT            = 50;       // максимальное количество пользователей в поиске getAllowed
	protected const _MAX_LEFT_MENU_COUNT              = 200;      // максимальный count в getLeftMenu
	protected const _MAX_FAVORITE_COUNT               = 100;      // максимальное количество диалогов в избранном
	protected const _MAX_GET_MESSAGES_BLOCK_COUNT     = 3;        // максимальное количество блоков в getMessages
	protected const _MAX_REPOSTED_MESSAGES_COUNT      = 15;       // максимальное количество репостнутых сообщений за раз
	protected const _MAX_MESSAGES_COUNT_IN_CHUNK      = 15;       // максимальное количество дочерних сообщений при перессылке
	protected const _MAX_BATCHING_COUNT               = 50;       // максимальное количество сущностей, о которых можно получить информацию batching методами за раз
	protected const _MAX_GET_MESSAGE_BATCHING_COUNT   = 100;      // максимальное количество сообщений, которые вернет в /conversations/getMessageBatching/
	protected const _MAX_WORKED_HOURS                 = 48;       // максимальное число рабочих часов

	public const ALLOW_METHODS = [
		"addSingle",
		"addToFavorites",
		"doRemoveFromFavorites",
		"doMute",
		"doUnmute",
		"doRead",
		"setAsUnread",
		"doClearMessages",
		"doRemoveSingle",
		"get",
		"getByOpponentId",
		"getAllowed",
		"getExtra",
		"getLeftMenu",
		"getLeftMenuMeta",
		"getLeftMenuDifference",
		"addMessage",
		"tryEditMessage",
		"tryDeleteMessageList",
		"tryHideMessageList",
		"addMessageReaction",
		"tryRemoveMessageReaction",
		"addQuote",
		"addRepost",
		"doReportMessage",
		"getMessage",
		"doLiftUp",
		"getMyReactions",
		"getMessageReactions",
		"getMessagesRemind",
		"getReactionUsers",
		"getReactionsUsersBatching",
		"setMessageAsLast",
		"getBatching",
		"getAllowedConversationsForAddMessage",
		"doCommitWorkedHours",
		"tryExacting",
		"getMessageBatching",
		"doReadMessage",
		"shareMember",
	];

	protected const _ALLOWED_FILE_SOURCES = [
		FILE_SOURCE_MESSAGE_DEFAULT, FILE_SOURCE_MESSAGE_IMAGE, FILE_SOURCE_MESSAGE_VIDEO, FILE_SOURCE_MESSAGE_VOICE,
		FILE_SOURCE_MESSAGE_AUDIO, FILE_SOURCE_MESSAGE_DOCUMENT, FILE_SOURCE_MESSAGE_ARCHIVE,
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"addSingle",
		"addToFavorites",
		"doRemoveFromFavorites",
		"doMute",
		"doUnmute",
		"doRead",
		"setAsUnread",
		"doClearMessages",
		"doRemoveSingle",
		"addMessage",
		"tryEditMessage",
		"tryDeleteMessageList",
		"tryHideMessageList",
		"addMessageReaction",
		"tryRemoveMessageReaction",
		"addQuote",
		"addRepost",
		"doReportMessage",
		"doLiftUp",
		"doCommitWorkedHours",
		"tryExacting",
		"shareMember",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [];

	protected const _MAX_REACTION_COUNT = 20; // максимальное количество реакций в запросе

	##########################################################
	# region диалоги
	##########################################################

	/**
	 * Создать single диалог с пользователем
	 *
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addSingle():array {

		$opponent_user_id = $this->post(\Formatter::TYPE_INT, "user_id"); // id пользователя, с которым нужно создать диалог

		// валидируем user_id
		if ($opponent_user_id < 1) {
			throw new ParamException(__METHOD__ . ": malformed user_id");
		}

		// проверяем на создание диалога с самим собой
		if ($opponent_user_id == $this->user_id) {
			throw new ParamException("create single with yourself");
		}

		// создаем диалог
		try {

			$meta_row = Helper_Single::createIfNotExist(
				$this->user_id, $opponent_user_id, false, true, true, $this->method_version);
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation $e) {
			return $this->error($e->getApiErrorCode(), "guest attempt initial conversation");
		}

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $meta_row["conversation_map"]);

		// подготавливаем диалог под формат api
		$prepared_conversation = Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);

		// прикрепляем оппонента к action
		$this->action->users([$opponent_user_id]);

		return $this->ok([
			"conversation" => (object) Apiv1_Format::conversation($prepared_conversation),
		]);
	}

	/**
	 * добавить переписку в избранное
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function addToFavorites():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row160");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_SETFAVORITE, "conversations", "row162");

		// получаем запись из левого менюи проверяем что она есть и что пользователь не покинул диалог
		$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $conversation_map);
		$this->_throwIfRowIsNotExistInLeftMenu($left_menu_row);
		$this->_throwIfUserIsLeavedFromConversation($left_menu_row);

		// если это "Служба поддержки", то добавить в избранные нельзя
		if (Type_Conversation_Meta::isGroupSupportConversationType($left_menu_row["type"])) {
			throw new ParamException("Trying to add to favorites support conversation");
		}

		// диалог уже в избранном
		if ($left_menu_row["is_favorite"] == 1) {

			// подготавливаем левое меню
			$prepared_left_menu_row = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);

			Gateway_Bus_Statholder::inc("conversations", "row164");
			return $this->ok([
				"left_menu" => (object) Apiv1_Format::leftMenu($prepared_left_menu_row),
			]);
		}

		// проверяем, превышено ли количество избранных диалогов
		$favorite_count = Type_Conversation_LeftMenu::getCountOfFavorite($this->user_id);
		if ($favorite_count >= self::_MAX_FAVORITE_COUNT) {

			Gateway_Bus_Statholder::inc("conversations", "row165");
			return $this->error(560, "Exceeded the maximum number of favorite conversations");
		}

		// добавляем диалог в избранные
		$formatted_left_menu_row = Helper_Conversations::addToFavorite($this->user_id, $left_menu_row);

		Gateway_Bus_Statholder::inc("conversations", "row161");

		return $this->ok([
			"left_menu" => (object) $formatted_left_menu_row,
		]);
	}

	/**
	 * удалить переписку из избранных
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doRemoveFromFavorites():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row180");

		// поднимаем блокировку по числу вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_REMOVEFAVORITE, "conversations", "row181");

		// получаем запись из левого менюи проверяем что она есть и что пользователь не покинул диалог
		$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $conversation_map);
		$this->_throwIfRowIsNotExistInLeftMenu($left_menu_row);
		$this->_throwIfUserIsLeavedFromConversation($left_menu_row);

		// если это "Служба поддержки", то удалить из избранных нельзя
		if (Type_Conversation_Meta::isGroupSupportConversationType($left_menu_row["type"])) {
			throw new ParamException("Trying to remove from favorites support conversation");
		}

		// диалог не избранный
		if ($left_menu_row["is_favorite"] == 0) {

			$prepared_left_menu = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
			return $this->ok([
				"left_menu" => (object) Apiv1_Format::leftMenu($prepared_left_menu),
			]);
		}

		// проверяем, что диалог подобного типа может быть удален из избранного
		Type_Conversation_Action::assertAction((int) $left_menu_row["type"], Type_Conversation_Action::REMOVE_FROM_FAVORITES);

		// убираем диалог из избранного
		$formatted_left_menu = Helper_Conversations::removeFromFavorite($this->user_id, $left_menu_row);

		Gateway_Bus_Statholder::inc("conversations", "row183");

		return $this->ok([
			"left_menu" => (object) ($formatted_left_menu),
		]);
	}

	// метод для выброса экзепшена если пользователь не является участником диалога
	protected function _throwIfRowIsNotExistInLeftMenu(array $left_menu_row):void {

		// запись не найдена
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Bus_Statholder::inc("conversations", "row163");
			throw new ParamException("row in left menu is not found");
		}
	}

	// метод для выброса экзепшена если пользователь покинул диалога
	protected function _throwIfUserIsLeavedFromConversation(array $left_menu_row):void {

		// проверяем, покинул ли пользователь диалог
		if ($left_menu_row["is_leaved"] == 1) {

			Gateway_Bus_Statholder::inc("conversations", "row166");
			throw new ParamException("User is not a member of this conversation");
		}
	}

	/**
	 * выключить уведомления в диалоге
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doMute():array {

		// получаем параметры из post_data
		$interval_minutes = $this->post("?i", "interval_minutes", 0);
		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		// проверяет что присланный interval_minutes - корректный
		if ($interval_minutes < 0) {
			throw new ParamException("passed invalid interval_minutes");
		}

		// если не был передан период мута, то мьют перманентный
		$is_muted = ($interval_minutes == 0) ? 1 : 0;

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_MUTE, "conversations", "row201");

		return $this->_doMute($conversation_map, $is_muted, $interval_minutes);
	}

	// мьютим диалог
	protected function _doMute(string $conversation_map, int $is_muted, int $interval_minutes):array {

		try {

			$new_muted_until = Helper_Conversations::doMute(
				$this->user_id,
				$conversation_map,
				$is_muted,
				$interval_minutes
			);
		} catch (cs_Conversation_NotificationsDisableTimeLimited) {

			Gateway_Bus_Statholder::inc("conversations", "row205");
			return $this->error(351, "Notification shutdown limit exceeded", [
				"muted_until" => (int) (time() + DAY1 * 99 + (DAY1 - 1)),
			]);
		}

		Gateway_Bus_Statholder::inc("conversations", "row204");
		return $this->ok([
			"muted_until" => (int) $new_muted_until,
			"is_muted"    => (int) $is_muted,
		]);
	}

	/**
	 * включить уведомления в диалоге
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doUnmute():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		// поднимаем статистику по числу вызовов
		Gateway_Bus_Statholder::inc("conversations", "row220");

		// инкрементим блокировку по числу вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_UNMUTE, "conversations", "row221");

		// размьютим диалог
		Helper_Conversations::doUnmute($this->user_id, $conversation_map);

		// поднимаем статистику по числу успешных вызовов
		Gateway_Bus_Statholder::inc("conversations", "row224");

		return $this->ok();
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

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key", "");
		$message_map = "";
		if (!isEmptyString($message_key)) {
			$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		}

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key", "");
		$conversation_map = "";
		if (!isEmptyString($conversation_key)) {
			$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		}

		$this->_checkParamsForDoRead($message_map, $conversation_map);

		// получаем conversation_map если передали сообщение
		if (isEmptyString($conversation_key)) {
			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		}

		// читаем диалог
		try {

			[$local_date, $local_time, $_] = getLocalClientTime();
			Domain_Conversation_Scenario_Api::doRead($this->user_id, $this->role, $this->permissions, $conversation_map, $message_map, $local_date, $local_time);
		} catch (cs_LeftMenuRowIsNotExist) {
			throw new ParamException("action is not allowed");
		}

		return $this->ok();
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
			$this->_throwIfMessageMapIsNotFromConversation($message_map);
		}
	}

	/**
	 * отметить диалог непрочитанным
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws BlockException
	 */
	public function setAsUnread():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row620");

		// инкрементим блокировку по числу вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_SET_AS_UNREAD, "conversations", "row623");

		// помечаем диалог не прочитанным
		try {
			$left_menu_version = Domain_Conversation_Scenario_Api::setAsUnread($this->user_id, $conversation_map);
		} catch (cs_LeftMenuRowIsNotExist) {
			throw new ParamException("user is not a member of this conversation");
		}

		return $this->ok([
			"left_menu_version" => (int) $left_menu_version,
		]);
	}

	/**
	 * очистить сообщения в диалоге для данного пользователя
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doClearMessages():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row280");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DOCLEARMESSAGES, "conversations", "row281");

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $conversation_map);
		$this->_throwIfConversationIsNotExistInLeftMenu($left_menu_row, "conversations", "row163");

		// чистим сообщения
		Helper_Conversations::clearMessages($this->user_id, $conversation_map, $left_menu_row, true, time());

		Gateway_Bus_Statholder::inc("conversations", "row283");

		return $this->ok();
	}

	/**
	 * убрать диалог из левого меню пользователя
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doRemoveSingle():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row260");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DOREMOVESINGLE, "conversations", "row262");

		// получаем запись из левого меню, проверяем что у нас есть такой диалог
		$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $conversation_map);
		$this->_throwIfConversationIsNotExistInLeftMenu($left_menu_row, "conversations", "row263");

		// выбрасываем ошибку, если попытались удалить не сингл диалог
		$this->_throwIfConversationIsNotSingle($left_menu_row, "conversations", "row264");

		// выбрасываем ошибку, если диалог не подходит для этого действия
		$this->_throwIfConversationTypeIsNotValidForAction($left_menu_row["type"], Type_Conversation_Action::REMOVE_CONVERSATION);

		// удаляем диалог
		Helper_Single::remove($this->user_id, $conversation_map, $left_menu_row["opponent_user_id"], true);

		return $this->ok();
	}

	// выбрасываем ошибку, если диалог не является single
	protected function _throwIfConversationIsNotSingle(array $left_menu_row, string $namespace = null, string $row = null):void {

		if (!Type_Conversation_Meta::isSubtypeOfSingle($left_menu_row["type"])) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("Tried remove conversation, which type is not single");
		}
	}

	/**
	 * вернуть информацию о диалоге
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \paramException
	 */
	public function get():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row300");

		// получаем meta о диалоге
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// если это не публичный диалог, то проверяем к нему доступ и получаем актуальную запись left_menu
		$left_menu_row = [];
		if (!Type_Conversation_Meta::isSubtypeOfPublicGroup($meta_row["type"])) {

			//  проверяем, имеет ли пользователь доступ к диалогу
			$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "conversations", "row302");

			// получаем диалог из левого меню, если это не публичный диалог
			$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $conversation_map);
			$this->_throwIfConversationIsNotExistInLeftMenu($left_menu_row, "conversations", "row303");
		}

		// получаем dynamic-данные диалога
		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		// добавляем пользователей к ответу
		$this->action->users(array_keys($meta_row["users"]));

		$temp                   = Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
		$formatted_conversation = Apiv1_Format::conversation($temp);

		Gateway_Bus_Statholder::inc("conversations", "row304");
		return $this->ok([
			"conversation"                 => (object) $formatted_conversation,
			"conversation_updated_version" => (object) Apiv1_Format::conversationUpdatedVersion($dynamic),
		]);
	}

	/**
	 * вернуть информацию о диалоге по id оппонента
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \paramException
	 */
	public function getByOpponentId():array {

		$opponent_user_id = $this->post("?i", "user_id");
		Gateway_Bus_Statholder::inc("conversations", "row600");

		if ($opponent_user_id < 1) {
			throw new ParamException(__METHOD__ . ": malformed user_id");
		}

		// получаем диалог из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::getByOpponentId($this->user_id, $opponent_user_id);
		if (!isset($left_menu_row["user_id"])) {
			return $this->error(912, "Single conversation between users not found");
		}

		// получаем meta о диалоге
		$meta_row = Type_Conversation_Meta::get($left_menu_row["conversation_map"]);

		// добавляем пользователей к ответу
		$this->action->users(array_keys($meta_row["users"]));

		$temp                   = Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
		$formatted_conversation = Apiv1_Format::conversation($temp);

		Gateway_Bus_Statholder::inc("conversations", "row601");
		return $this->ok([
			"conversation" => (object) $formatted_conversation,
		]);
	}

	/**
	 * метод для получения информации о диалогах
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \paramException
	 */
	public function getBatching():array {

		$conversation_key_list = $this->post("?a", "conversation_key_list");
		Gateway_Bus_Statholder::inc("conversations", "row490");

		$conversation_key_list = array_unique($conversation_key_list);
		$this->_throwIfConversationKeyListIsIncorrect($conversation_key_list);

		// преобразуем все key в map
		$conversation_map_list = $this->_tryDecryptConversationKeyList($conversation_key_list);

		$conversation_meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// разбиваем на массив доступных и недоступных
		$allowed_conversation_list         = [];
		$not_allowed_conversation_map_list = [];
		foreach ($conversation_meta_list as $item) {

			if (!Type_Conversation_Meta::isSubtypeOfPublicGroup($item["type"]) && !Type_Conversation_Meta_Users::isMember($this->user_id, $item["users"])) {

				$not_allowed_conversation_map_list[] = $item["conversation_map"];
				continue;
			}
			$allowed_conversation_list[$item["conversation_map"]]["meta_row"] = $item;
		}

		$this->_incStatByNotAllowedConversationsIfNeed($not_allowed_conversation_map_list);
		$allowed_conversation_map_list = $this->_makeAllowedConversationMapList($allowed_conversation_list);

		// получаем информацию о диалогах из левого меню
		$left_menu_list = Type_Conversation_LeftMenu::getList($this->user_id, $allowed_conversation_map_list);
		foreach ($left_menu_list as $item) {
			$allowed_conversation_list[$item["conversation_map"]]["left_menu_row"] = $item;
		}

		$output = $this->_makeGetBatchingOutput($allowed_conversation_list, $not_allowed_conversation_map_list);
		Gateway_Bus_Statholder::inc("conversations", "row495");
		return $this->ok($output);
	}

	// выбрасываем ошибку, если пришел некорректный массив диалогов
	protected function _throwIfConversationKeyListIsIncorrect(array $conversation_key_list):void {

		// если пришел пустой массив диалогов
		if (count($conversation_key_list) < 1) {

			Gateway_Bus_Statholder::inc("conversations", "row491");
			throw new ParamException("passed empty conversation_key_list");
		}

		// если пришел слишком большой массив
		if (count($conversation_key_list) > self::_MAX_BATCHING_COUNT) {

			Gateway_Bus_Statholder::inc("conversations", "row492");
			throw new ParamException("passed conversation_key_list biggest than max");
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _tryDecryptConversationKeyList(array $conversation_key_list):array {

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

	// инкрементим статистику в зависимости от количества недоступных диалогов
	protected function _incStatByNotAllowedConversationsIfNeed(array $not_allowed_conversation_map_list):void {

		// если есть недоступные диалоги
		if (count($not_allowed_conversation_map_list) > 0) {
			Gateway_Bus_Statholder::inc("conversations", "row494", count($not_allowed_conversation_map_list));
		}
	}

	// формируем массив с ключами доступных диалогов
	protected function _makeAllowedConversationMapList(array $allowed_conversation_list):array {

		$allowed_conversation_map_list = [];
		foreach ($allowed_conversation_list as $item) {
			$allowed_conversation_map_list[] = $item["meta_row"]["conversation_map"];
		}

		return $allowed_conversation_map_list;
	}

	/**
	 * Формируем массив ответа для метода conversations.getBatching
	 *
	 * @return array[]
	 *
	 */
	#[ArrayShape([
		"conversation_list"                 => "array",
		"not_allowed_conversation_key_list" => "array",
	])]
	protected function _makeGetBatchingOutput(array $allowed_conversation_list, array $not_allowed_conversation_map_list):array {

		$not_allowed_conversation_key_list = [];

		$output = [];
		foreach ($allowed_conversation_list as $item) {

			// добавляем пользователей к ответу
			$this->action->users(array_keys($item["meta_row"]["users"]));

			// если это публичная группа, то запись в левом меню для нее не существует
			// если это группа, но отсутствует левое меню
			if (Type_Conversation_Meta::isSubtypeOfPublicGroup($item["meta_row"]["type"])
				|| Type_Conversation_Meta::isSubtypeOfGroup($item["meta_row"]["type"]) && !isset($item["left_menu_row"])) {
				$item["left_menu_row"] = [];
			}

			$temp     = Type_Conversation_Utils::prepareConversationForFormat($item["meta_row"], $item["left_menu_row"]);
			$output[] = Apiv1_Format::conversation($temp);
		}

		if (count($not_allowed_conversation_map_list) > 0) {

			foreach ($not_allowed_conversation_map_list as $item) {
				$not_allowed_conversation_key_list[] = \CompassApp\Pack\Conversation::doEncrypt($item);
			}
		}

		return [
			"conversation_list"                 => (array) $output,
			"not_allowed_conversation_key_list" => (array) $not_allowed_conversation_key_list,
		];
	}

	/**
	 * вернуть пользователей, с которыми есть диалог
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getAllowed():array {

		$offset                 = $this->post("?i", "offset", 0);
		$count                  = $this->post("?i", "count", self::_MAX_GET_ALLOWED_COUNT);
		$is_need_return_blocked = $this->post("?i", "is_need_return_blocked", 0) == 1;

		Gateway_Bus_Statholder::inc("conversations", "row140");

		// если не возращаем пользователей
		if ($offset < 0 || $count < 1) {
			return $this->_makeAllowedUsersOutput();
		}

		// ограничиваем указанное возвращаемое число пользователей до максимального
		$count = limit($count, 0, self::_MAX_GET_ALLOWED_COUNT);

		// получаем пользователей доступных для приглашения в группу
		$left_menu_list = Type_Conversation_LeftMenu::getOpponents($this->user_id, $offset, $count, $is_need_return_blocked);
		$has_next       = count($left_menu_list) == $count ? 1 : 0;

		// получаем список пользователей
		$user_id_list = $this->_getUserIdList($left_menu_list);

		// фильтруем диалоги
		$left_menu_list = $this->_filterAllowedConversationList($left_menu_list, $user_id_list);

		Gateway_Bus_Statholder::inc("conversations", "row141");
		return $this->_makeAllowedUsersOutput($left_menu_list, $has_next);
	}

	/**
	 * вернуть дополнительную информацию о диалоге, не попавшую в основную сущность
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \paramException
	 */
	public function getExtra():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row120");

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationIsSubtypeOfPublic($meta_row["type"]);
		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "conversations", "row123");

		// получаем dynamic диалога
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// устанавливаем значения количества файлов, изображений, видео в диалоге
		$hidden_count = Domain_Conversation_Entity_Dynamic::getHiddenCountFromUserFileClearInfo(
			$dynamic_row["user_file_clear_info"],
			$dynamic_row["user_clear_info"],
			$dynamic_row["conversation_clear_info"],
			$this->user_id
		);

		$total_file_count  = $dynamic_row["file_count"] - $hidden_count["hidden_file_count"];
		$total_image_count = $dynamic_row["image_count"] - $hidden_count["hidden_image_count"];
		$total_video_count = $dynamic_row["video_count"] - $hidden_count["hidden_video_count"];

		Gateway_Bus_Statholder::inc("conversations", "row122");

		return $this->ok([
			"files_count"  => (int) $total_file_count < 0 ? 0 : $total_file_count,
			"images_count" => (int) $total_image_count < 0 ? 0 : $total_image_count,
			"videos_count" => (int) $total_video_count < 0 ? 0 : $total_video_count,
		]);
	}

	/**
	 * Вернуть информацию о диалогах
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws ParamException
	 * @long
	 */
	public function getLeftMenu():array {

		// получаем параметры из post_data
		$search_query        = $this->post(\Formatter::TYPE_STRING, "query", ""); // запрос
		$offset              = $this->post(\Formatter::TYPE_INT, "offset", 0); // смещение
		$limit               = $this->post(\Formatter::TYPE_INT, "limit", self::_MAX_LEFT_MENU_COUNT); // число результатов в ответе
		$filter_favorite     = $this->post(\Formatter::TYPE_INT, "filter_favorites", 0); // требуется ли возвращать избранные в ответе
		$filter_unread       = $this->post(\Formatter::TYPE_INT, "filter_unread", 0);
		$filter_single       = $this->post(\Formatter::TYPE_INT, "filter_single", 0);
		$filter_unblocked    = $this->post(\Formatter::TYPE_INT, "filter_unblocked", 0);
		$filter_owner        = $this->post(\Formatter::TYPE_INT, "filter_owner", 0);
		$filter_system       = $this->post(\Formatter::TYPE_INT, "filter_system", 0);
		$filter_npc_type     = $this->post(\Formatter::TYPE_ARRAY, "filter_npc_type", []);
		$is_mentioned_first  = $this->post(\Formatter::TYPE_INT, "is_mentioned_first", 0);
		$filter_blocked_time = $this->post(\Formatter::TYPE_INT, "filter_blocked_time", 0);
		$filter_support      = $this->post(\Formatter::TYPE_INT, "filter_support", 0);

		// если пришел некорректный параметр count или offset
		if ($limit < 1 || $offset < 0) {
			throw new ParamException("incorrect limit or offset");
		}

		// если count превышает максимальное значение, то устанавливаем максимум
		if ($limit > self::_MAX_LEFT_MENU_COUNT) {
			$limit = self::_MAX_LEFT_MENU_COUNT;
		}

		try {

			// получаем диалоги
			[$left_menu_list, $has_next, $dynamic_list] = Domain_Conversation_Scenario_Api::getLeftMenu(
				$this->user_id,
				$limit,
				$offset,
				$search_query,
				$filter_favorite,
				$filter_unread,
				$filter_single,
				$filter_unblocked,
				$filter_owner,
				$filter_system,
				$is_mentioned_first,
				$filter_blocked_time,
				$filter_support,
				$filter_npc_type
			);
		} catch (\CompassApp\Domain\User\Exception\NotAllowedType) {
			throw new ParamException("incorrect filter_npc_type");
		}

		// форматируем результаты
		$formatted_left_menu_list = $this->_doFormatLeftMenuList($left_menu_list, $filter_npc_type);

		return $this->ok([
			"left_menu_list"                    => (array) $formatted_left_menu_list,
			"conversation_updated_version_list" => (array) Apiv1_Format::conversationUpdatedVersionList($dynamic_list),
			"has_next"                          => (int) $has_next,
		]);
	}

	/**
	 * Получить список изменений в левом меню
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \parseException
	 * @throws ParamException
	 */
	public function getLeftMenuDifference():array {

		$from_version = $this->post(\Formatter::TYPE_INT, "from_version"); // версия кэша у клиента

		// получаем избранные диалоги
		[$left_menu_list, $dynamic_list] = Domain_User_Scenario_Conversation_Api::getLeftMenuDifference($this->user_id, $from_version);

		// форматируем результаты
		$formatted_left_menu_list = $this->_doFormatLeftMenuList($left_menu_list);

		Gateway_Bus_Statholder::inc("conversations", "row101");
		return $this->ok([
			"left_menu_list"                    => (array) $formatted_left_menu_list,
			"conversation_updated_version_list" => (array) Apiv1_Format::conversationUpdatedVersionList($dynamic_list),
		]);
	}

	/**
	 * Получить список изменений в левом меню
	 *
	 */
	public function getLeftMenuMeta():array {

		// получаем избранные диалоги
		$meta = Domain_User_Scenario_Conversation_Api::getLeftMenuMeta($this->user_id);

		return $this->ok([
			"messages_unread_count"      => (int) $meta["messages_unread_count"],
			"conversations_unread_count" => (int) $meta["conversations_unread_count"],
			"left_menu_version"          => (int) $meta["left_menu_version"],
		]);
	}

	/**
	 * поднимает диалог в левом меню
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doLiftUp():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Gateway_Bus_Statholder::inc("conversations", "row341");

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DOLIFTUP, "conversations", "row344");

		// получаем запись из левого меню, проверяем что у нас есть такой диалог
		$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Bus_Statholder::inc("conversations", "row343");
			throw new ParamException(__METHOD__ . ": conversation is not exists in user left_menu");
		}

		// присваиваю время в переменную, чтобы гарантировано запись в БД не отличалась от ws-события
		$current_time = time();

		// поднимаем диалог
		Type_Conversation_LeftMenu::doLiftUp($this->user_id, $conversation_map, $current_time);

		// отправляем событие участнику
		Gateway_Bus_Sender::conversationLiftedUp($this->user_id, $conversation_map, $current_time);
		Gateway_Bus_Statholder::inc("conversations", "row342");

		return $this->ok();
	}

	# endregion
	##########################################################

	##########################################################
	# region сообщения
	##########################################################

	/**
	 * Отправить сообщение в диалог
	 * Версия метода 3
	 *
	 * @return array
	 * @throws ParamException
	 * @throws PaymentRequiredException
	 */
	public function addMessage():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		$client_message_list = $this->post("?a", "client_message_list");

		return $this->_processAddMessage($conversation_map, $client_message_list);
	}

	// проверяем возможность добавить сообщение в диалог и подготавливает данные для сообщений
	// @long обертка для логики паблик метода
	protected function _processAddMessage(string $conversation_map, array $client_message_list):array {

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_ADDMESSAGE, "messages", "row3");

		// готовим к работе список сообщений
		try {

			$raw_message_list = $this->_parseRawMessageList($client_message_list);
			Domain_Member_Entity_Permission::checkVoice($this->user_id, $this->method_version, Permission::IS_VOICE_MESSAGE_ENABLED, $raw_message_list);
		} catch (cs_Message_IsTooLong) {

			Gateway_Bus_Statholder::inc("messages", "row1");
			return $this->error(540, "Message is too long");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// получаем информацию о диалоге и проверяем что пользователь его участник
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// если пытаемся добавить сообщение не в чат поддержки - возвращаем ошибку
		if ($this->extra["space"]["is_restricted_access"] && !Type_Conversation_Meta::isGroupSupportConversationType($meta_row["type"])) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		}

		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::ADD_MESSAGE_FROM_CONVERSATION);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("messages", "row5");
			return $this->error(501, "User is not conversation member");
		}

		try {
			// проверяем опции группы если версия метода >= 3
			if ($this->method_version >= 3) {
				Domain_Group_Entity_Options::checkChannelRestrictionByMetaRow($this->user_id, $meta_row);
			}
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2118002, "not enough right");
		}

		return $this->_addMessageListIfAllowed($meta_row, $raw_message_list);
	}

	// строим список данных сообщений и располагаем в нем элементы по порядку
	protected function _parseRawMessageList(array $client_messages_raw):array {

		$this->_throwOnInvalidMessageChunkFormat($client_messages_raw);

		// считаем количество сообщений и каким сообщениям нужен trim
		$count_message     = count($client_messages_raw);
		$key_message_ltrim = 0;
		$key_message_rtrim = $count_message - 1;

		$output = [];
		foreach ($client_messages_raw as $k => $v) {

			// сообщение должно быть массивом а необходимые поля должны быть объявлены
			$this->_throwIfInvalidMessageChunkHasCorrectFields($v);
			$order = (int) $v["order"];

			// номер должен быть уникален для каждого сообщения - иначе выбрасываем исключение
			if (isset($output[$order])) {
				$this->_throwOnInvalidMessageOrder();
			}

			// собираем output
			$output = $this->_makeOutputForRawMessageList($k, $key_message_ltrim, $key_message_rtrim, $order, $v, $output);

			if (isset($v["file_key"]) && $v["file_key"] !== false) {
				$output[$order]["file_map"] = $this->_tryGetFileMapFromKey($v["file_key"]);
			}
			if ($output[$order]["file_map"] === false && mb_strlen($output[$order]["text"]) < 1) {

				Gateway_Bus_Statholder::inc("messages", "row2");
				throw new ParamException(__CLASS__ . ": empty text and file_map");
			}
		}

		// сортируем массив по ключам для восстановления порядка сообщений по order
		ksort($output);
		return $output;
	}

	// проверяет, что количесвто кусков сообщения и их формат
	protected function _throwOnInvalidMessageChunkFormat(array $client_messages_raw):void {

		// количесвто кусков не должно превышать половину емкости блока
		if (count($client_messages_raw) > Type_Conversation_Message_Block::MESSAGE_PER_BLOCK_LIMIT / 2) {
			throw new cs_Message_IsTooLong(__CLASS__ . ": too many message blocks");
		}

		// ожидаем как минимум одно сообщение
		if (count($client_messages_raw) == 0) {

			Gateway_Bus_Statholder::inc("conversations", "row12");
			throw new ParamException(__CLASS__ . ": empty client message list");
		}
	}

	/**
	 * Проверяет наличие в куске сообщения необходимых полей и их типы
	 *
	 * @throws \paramException
	 */
	protected function _throwIfInvalidMessageChunkHasCorrectFields(mixed $message_chunk):void {

		if (is_array($message_chunk) && isset($message_chunk["client_message_id"]) && is_string($message_chunk["client_message_id"])
			&& isset($message_chunk["text"]) && is_string($message_chunk["text"])
			&& isset($message_chunk["order"]) && is_numeric($message_chunk["order"])) {
			return;
		}
		throw new ParamException(__CLASS__ . ": invalid client message list format");
	}

	// выбрасывает исключение при ошибке значения порядка сообщения
	protected function _throwOnInvalidMessageOrder():void {

		Gateway_Bus_Statholder::inc("conversations", "row11");
		throw new ParamException(__CLASS__ . ": invalid message order");
	}

	// собираем output для raw_message_list
	protected function _makeOutputForRawMessageList(string $key, int $key_message_ltrim, int $key_message_rtrim, int $order, array $message_chunk, array $output):array {

		$is_ltrim = false;
		if ($key == $key_message_ltrim) {
			$is_ltrim = true;
		}

		$is_rtrim = false;
		if ($key == $key_message_rtrim) {
			$is_rtrim = true;
		}

		$output[$order] = [
			"client_message_id" => $this->_prepareRawClientMessageId($message_chunk["client_message_id"]),
			"text"              => $this->_prepareRawMessageText($message_chunk["text"], $is_ltrim, $is_rtrim),
			"file_map"          => false,
			"file_name"         => isset($message_chunk["file_name"]) ? Type_Api_Filter::sanitizeFileName($message_chunk["file_name"]) : "",
		];
		return $output;
	}

	// подготавливаем $client_message_id и $message_text для сообщения
	protected function _prepareRawClientMessageId(string $client_message_id):string {

		// валидируем client_message_id
		$client_message_id = Type_Api_Filter::sanitizeClientMessageId($client_message_id);
		if (mb_strlen($client_message_id) <= 0) {
			throw new ParamException(__CLASS__ . ": incorrect client_message_id");
		}

		return $client_message_id;
	}

	// подготавливаем $client_message_id и $message_text для сообщения
	protected function _prepareRawMessageText(string $message_text, bool $is_ltrim, bool $is_rtrim):string {

		// валидируем и преобразуем текст сообщения
		$text = Type_Api_Filter::replaceEmojiWithShortName($message_text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			throw new cs_Message_IsTooLong(__CLASS__ . ": message is too long");
		}

		return Type_Api_Filter::sanitizeMessageText($text, $is_ltrim, $is_rtrim);
	}

	// пробуем получить file_map из ключа
	protected function _tryGetFileMapFromKey(string $file_key):string {

		$file_map = \CompassApp\Pack\File::tryDecrypt($file_key);

		// если file_source не входит в список разрешенных для отправки в сообщении
		$file_source = \CompassApp\Pack\File::getFileSource($file_map);
		if (!in_array($file_source, self::_ALLOWED_FILE_SOURCES)) {
			throw new ParamException("Incorrect file source");
		}
		return $file_map;
	}

	// если диалог доступен для отправки, то отправляем список сообщений
	// @long
	protected function _addMessageListIfAllowed(array $meta_row, array $client_message_list):array {

		// проверяем может ли пользователь писать в диалог
		try {
			$this->_throwIfNotAllowedForNewMessage($meta_row);
		} catch (cs_Conversation_IsNotAllowedForNewMessage $e) {
			return $this->error($e->getCode(), $e->getMessage());
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// создаем и отправляем сообщение
		try {
			$output = $this->_addMessageList($client_message_list, $meta_row);
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		}

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CONVERSATION_MESSAGE, $this->user_id, count($output));
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_CONVERSATION_MESSAGE);

		return $this->ok([
			"message_list" => (array) $output,
		]);
	}

	// проверяем возможность писать в диалог
	protected function _throwIfNotAllowedForNewMessage(array $meta_row):void {

		try {
			Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $this->user_id);
		} catch (cs_Conversation_MemberIsDisabled) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because your opponent is blocked in our system", 532);
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because your opponent delete account", 2118001);
		} catch (cs_Conversation_UserbotIsDisabled) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because userbot is disabled", 2134001);
		} catch (cs_Conversation_UserbotIsDeleted) {
			throw new cs_Conversation_IsNotAllowedForNewMessage("You can't write to this conversation because userbot is deleted", 2134002);
		}
	}

	// создаем и отправляем список сообщений
	protected function _addMessageList(array $client_message_list, array $meta_row):array {

		$raw_message_list = $this->_generateRawMessageList($client_message_list, $meta_row);

		// отправляем сообщение
		try {

			$message_list = Helper_Conversations::addMessageList(
				$meta_row["conversation_map"],
				$raw_message_list,
				$meta_row["users"],
				$meta_row["type"],
				$meta_row["conversation_name"],
				$meta_row["extra"],
			);
		} catch (cs_ConversationIsLocked) {
			throw new BlockException(__METHOD__ . ": conversation is locked");
		}

		return $this->_prepareMessageListForResponse($message_list);
	}

	// создаем массив сообщений-заготовок перед созданием записей в базу
	protected function _generateRawMessageList(array $client_message_list, array $meta_row):array {

		$raw_message_list = [];
		$platform         = Type_Api_Platform::getPlatform();

		foreach ($client_message_list as $v) {

			$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $v["text"]);

			if ($v["file_map"] !== false) {

				$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeFile(
					$this->user_id, $v["text"], $v["client_message_id"], $v["file_map"], $v["file_name"], $platform
				);
			} else {
				$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($this->user_id, $v["text"], $v["client_message_id"], $platform);
			}
			$raw_message_list[] = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}

		return $raw_message_list;
	}

	/**
	 * Пытаемся отредактировать сообщение
	 * версия метода 2
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function tryEditMessage():array {

		$message_key = $this->post("?s", "message_key");
		$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$text        = $this->post("?s", "text");

		$this->_throwIfNotConversationMessage($message_map);

		// заменяем emoji на :short_name: и проверяем, что полученный текст не слишком длинный
		$text = Type_Api_Filter::replaceEmojiWithShortName($text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {

			Gateway_Bus_Statholder::inc("messages", "row103");
			return $this->error(540, "Message is too long");
		}

		// очищаем текст от лишнего и инкрементим блокировку
		$text = Type_Api_Filter::sanitizeMessageText($text);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_EDITMESSAGE, "messages", "row105");

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::EDIT_MESSAGE_FROM_CONVERSATION);

		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "messages", "row106");
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		try {
			Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $this->user_id);

			if ($this->method_version >= 2) {
				Domain_Group_Entity_Options::checkChannelRestrictionByMetaRow($this->user_id, $meta_row);
			}
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {

			$error = Helper_Conversations::getCheckIsAllowedError($e);
			return $this->error($error["error_code"], $error["message"]);
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2118002, "not enough right");
		}

		return $this->_editMessageText($conversation_map, $message_map, $text, $mention_user_id_list, $meta_row);
	}

	// редактируем сообщение
	protected function _editMessageText(string $conversation_map, string $message_map, string $text, array $mention_user_id_list, array $meta_row):array {

		try {

			$prepared_message = Helper_Conversations::editMessageText(
				$conversation_map, $message_map, $this->user_id, $text, $meta_row, $mention_user_id_list
			);
		} catch (cs_Message_IsEmptyText) {

			Gateway_Bus_Statholder::inc("messages", "row104");
			throw new ParamException("Empty text");
		} catch (cs_Message_IsDeleted) {

			Gateway_Bus_Statholder::inc("messages", "row109");
			return $this->error(549, "Message is deleted");
		} catch (cs_Message_UserHaveNotPermission) {

			Gateway_Bus_Statholder::inc("messages", "row107");
			throw new ParamException("you are have not permission for this action");
		} catch (cs_Message_IsNotAllowForEdit) {
			return $this->_returnErrorIfNotAllowMessageForEdit();
		} catch (cs_Message_TimeIsOver) {
			return $this->error(917, "Timed out for edit message");
		}

		Gateway_Bus_Statholder::inc("messages", "row111");

		return $this->ok([
			"message" => (object) Apiv1_Format::conversationMessage($prepared_message),
		]);
	}

	// возращаем ошибку если нету сообщения для редактирования
	protected function _returnErrorIfNotAllowMessageForEdit():array {

		throw new ParamException("you have not permissions to edit this message");
	}

	/**
	 * Пытаемся удалить сразу несколько сообщений
	 * версия метода 2
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \returnException
	 */
	public function tryDeleteMessageList():array {

		$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);

		$this->_throwIfIncorrectMessageMapList($message_map_list, "row965", "row967", "row968");
		if (count($message_map_list) > self::MAX_SELECTED_MESSAGE_COUNT) {
			throw new ParamException("exceeded the limit on the number of messages");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DELETEMESSAGE);

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::DELETE_MESSAGE_FROM_CONVERSATION);

		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"]);

		// проверяем allow_status пользователей
		try {
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);

			if ($this->method_version >= 2) {
				Domain_Group_Entity_Options::checkChannelRestrictionByMetaRow($this->user_id, $meta_row);
			}
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {

			$error = Helper_Conversations::getCheckIsAllowedError($e, true);
			return $this->error($error["error_code"], $error["message"]);
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2118002, "not enough right");
		}

		return $this->_doDeleteMessageList($conversation_map, $meta_row["type"], $message_map_list, $meta_row);
	}

	// удаляем список сообщений
	protected function _doDeleteMessageList(string $conversation_map, int $conversation_type, array $message_map_list, array $meta_row):array {

		// по умолчанию считаем, что для администратора с правами удаления сообщений разрешено удалять сообщение
		$is_force_delete = Permission::canDeleteMessage($this->role, $this->permissions);

		try {
			Helper_Conversations::deleteMessageList($this->user_id, $conversation_map, $conversation_type, $message_map_list, $meta_row, $is_force_delete);
		} catch (cs_Message_UserHaveNotPermission) {
			return $this->error(514, "You are not allowed to do this action");
		} catch (cs_Message_IsNotAllowForDelete) {
			throw new ParamException("you have not permissions to delete this message");
		} catch (cs_Message_IsTimeNotAllowToDelete) {
			return $this->error(917, "Message deletion timed out");
		} catch (cs_ConversationIsLocked) {
			throw new BlockException(__METHOD__ . " conversation is locked");
		}

		return $this->ok();
	}

	/**
	 * метод для скрытия списка сообщений
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function tryHideMessageList():array {

		$message_key_list     = $this->post("?j", "message_key_list");
		$message_map_list     = $this->_tryGetMessageMapList($message_key_list);
		$previous_message_key = $this->post("?s", "previous_message_key", "");

		Gateway_Bus_Statholder::inc("messages", "row923");
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_HIDEMESSAGE, "messages", "row921");

		$this->_throwIfIncorrectMessageMapList($message_map_list, "row925", "row927", "row928");
		if (count($message_map_list) > self::MAX_SELECTED_MESSAGE_COUNT) {

			Gateway_Bus_Statholder::inc("messages", "row926");
			throw new ParamException("exceeded the limit on the number of messages");
		}

		// в message_map_list гарантированно лежат сообщения из одного диалога, так что берем первое
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);

		$previous_message_map = "";
		if ($previous_message_key != "") {

			$previous_message_map = $this->_tryGetMessageMap($previous_message_key);

			$conversation_map_from_previous_message_map = \CompassApp\Pack\Message\Conversation::getConversationMap($previous_message_map);
			if ($conversation_map != $conversation_map_from_previous_message_map) {
				throw new ParamException("previous message from other conversation");
			}
		}

		// получаем мету диалога и проверяем что пользователь не состоит в чате
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::HIDE_MESSAGE_FROM_CONVERSATION);
		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "messages", "row922");

		Domain_Conversation_Action_Message_HideList::do($this->user_id, $message_map_list, $conversation_map, $previous_message_map);

		return $this->ok();
	}

	/**
	 * Поставить реакцию на сообщение
	 * версия метода 2
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws PaymentRequiredException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function addMessageReaction():array {

		$message_key   = $this->post("?s", "message_key");
		$message_map   = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$reaction_name = $this->post("?s", "reaction_name");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_SETREACTION);

		try {

			if ($this->method_version >= 2) {
				Domain_Group_Entity_Options::checkReactionRestrictionByConversationMessageMap($this->user_id, $message_map);
			}
			Domain_Conversation_Scenario_Api::addReaction($message_map, $reaction_name, $this->user_id, $this->extra["space"]["is_restricted_access"]);
		} catch (cs_Conversation_MemberIsDisabled) {
			return $this->error(532, "You can't add reaction to this conversation because your opponent is blocked in our system");
		} catch (cs_Conversation_UserbotIsDisabled) {
			return $this->error(2134001, "You can't write to this conversation because userbot is disabled");
		} catch (cs_Conversation_UserbotIsDeleted) {
			return $this->error(2134002, "You can't write to this conversation because userbot is deleted");
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			return $this->error(2118001, "You can't write to this conversation because your opponent delete account");
		} catch (cs_UserIsNotMember) {
			return $this->error(501, "User is not conversation member");
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "message is deleted");
		} catch (cs_Message_IsNotAllowedForReaction) {
			throw new ParamException("Message not is allowed for reaction");
		} catch (cs_Message_ReactionLimit) {
			return $this->error(545, "message has max count reactions");
		} catch (cs_Message_ReactionIsExist) {
			// ничего не делаем если есть значит ok
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2129003, "not enough right");
		}

		return $this->ok();
	}

	/**
	 * Убрать реакцию сообщения
	 * Версия метода 2
	 *
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws PaymentRequiredException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function tryRemoveMessageReaction():array {

		$message_key   = $this->post("?s", "message_key");
		$message_map   = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$reaction_name = $this->post("?s", "reaction_name");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_SETREACTION, "messages", "row62");

		try {

			if ($this->method_version >= 2) {
				Domain_Group_Entity_Options::checkReactionRestrictionByConversationMessageMap($this->user_id, $message_map);
			}
			Domain_Conversation_Scenario_Api::removeReaction($message_map, $reaction_name, $this->user_id, $this->extra["space"]["is_restricted_access"]);
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "message is deleted");
		} catch (cs_Message_IsNotAllowedForReaction) {
			throw new ParamException("Message not is allowed for reaction");
		} catch (cs_Conversation_MemberIsDisabled) {
			return $this->error(532, "You can't write to this conversation because your opponent is blocked in our system");
		} catch (cs_Conversation_UserbotIsDisabled) {
			return $this->error(2134001, "You can't write to this conversation because userbot is disabled");
		} catch (cs_Conversation_UserbotIsDeleted) {
			return $this->error(2134002, "You can't write to this conversation because userbot is deleted");
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			return $this->error(2118001, "You can't write to this conversation because your opponent delete account");
		} catch (cs_UserIsNotMember) {
			throw new ParamException("User is not conversation member");
		} catch (Domain_Conversation_Exception_TariffUnpaid) {
			throw new PaymentRequiredException(PaymentRequiredException::RESTRICTED_ERROR_CODE, "need to pay tariff");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2129003, "not enough rights");
		}

		return $this->ok();
	}

	/**
	 * Процитировать сообщение(я)
	 * Версия метода 3
	 *
	 * @throws ParamException
	 */
	public function addQuote():array {

		$text              = $this->post("?s", "text", "");
		$client_message_id = $this->post("?s", "client_message_id");

		return $this->_addQuoteNew($client_message_id, $text);
	}

	// процитировать несколько сообщений
	// @long - изза legacy-кода
	protected function _addQuoteNew(string $client_message_id, string $text):array {

		$message_key_list = $this->post("?j", "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_ADDREPOST, "messages", "row142");

		$text = Type_Api_Filter::replaceEmojiWithShortName($text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(540, "Message is too long");
		}
		$text = Type_Api_Filter::sanitizeMessageText($text);

		// фильтруем client_message_id и проверяем присланный список сообщений
		$client_message_id = $this->_tryGetClientMessageId($client_message_id, "messages", "row141");
		$this->_throwIfIncorrectMessageMapList($message_map_list, "row154", "row155", "row157");
		if (count($message_map_list) > self::MAX_SELECTED_MESSAGE_COUNT) {

			Gateway_Bus_Statholder::inc("messages", "row156");
			return $this->error(552, "exceeded the limit on the number of quoted messages");
		}

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::ADD_QUOTE_FROM_CONVERSATION);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("messages", "row143");
			return $this->error(501, "User is not conversation member");
		}
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		try {
			Helper_Conversations::checkIsAllowed($conversation_map, $meta_row, $this->user_id);

			if ($this->method_version >= 3) {
				Domain_Group_Entity_Options::checkChannelRestrictionByMetaRow($this->user_id, $meta_row);
			}
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {

			$error = Helper_Conversations::getCheckIsAllowedError($e, true);
			return $this->error($error["error_code"], $error["message"]);
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2118002, "not enough right");
		}

		// сортируем message_map_list по порядку в диалоге
		$message_map_list = self::_doSortMessageMapListByMessageIndex($message_map_list);

		return $this->_doQuoteMessageV2($text, $client_message_id, $message_map_list, $conversation_map, $meta_row, $mention_user_id_list);
	}

	// создаем цитату v2
	// @long
	protected function _doQuoteMessageV2(string $text, string $client_message_id, array $message_map_list, string $conversation_map, array $meta_row, array $mention_user_id_list):array {

		try {

			$quote_message_list = Helper_Conversations::addQuoteV2(
				$this->user_id,
				$text,
				$client_message_id,
				$message_map_list,
				$conversation_map,
				$meta_row,
				$mention_user_id_list,
				Type_Api_Platform::getPlatform()
			);
		} catch (cs_ConversationIsLocked) {
			throw new BlockException(__METHOD__ . " conversation is locked");
		} catch (cs_Message_Limit) {
			return $this->error(552, "exceeded the limit on the number of quoted messages");
		} catch (cs_MessageList_IsEmpty) {
			return $this->error(551, "message list for quote is empty");
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		}

		$prepared_message_list = $this->_doPrepareMessageListV2($quote_message_list);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CONVERSATION_MESSAGE, $this->user_id, count($prepared_message_list));
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_CONVERSATION_MESSAGE);
		Gateway_Bus_Statholder::inc("messages", "row148");

		return $this->ok([
			"message_list" => (array) $prepared_message_list,
		]);
	}

	/**
	 * Позволяет переслать сообщение
	 * Версия метода 3
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function addRepost():array {

		$conversation_key          = $this->post("?s", "conversation_key");
		$receiver_conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		$client_message_id         = $this->post("?s", "client_message_id");
		$text                      = $this->post("?s", "text", "");

		$post_type = \Formatter::TYPE_JSON;

		$message_key_list = $this->post($post_type, "message_key_list");
		return $this->_addRepostNew($message_key_list, $receiver_conversation_map, $client_message_id, $text);
	}

	// зарепостить сообщения (новая версия, позволяющая репостить репосты)
	// @long - временно long из-за legacy
	protected function _addRepostNew(array $message_key_list, string $receiver_conversation_map, string $client_message_id, string $text):array {

		$message_map_list = $this->_tryGetMessageMapList($message_key_list);
		Gateway_Bus_Statholder::inc("messages", "row941");

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_ADDREPOST, "messages", "row167");

		// фильтруем текст
		$text = Type_Api_Filter::replaceEmojiWithShortName($text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			return $this->error(540, "Message to long");
		}

		try {

			if ($this->method_version >= 2) {
				Domain_Member_Entity_Permission::check($this->user_id, Permission::IS_REPOST_MESSAGE_ENABLED);
			}
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		$text = Type_Api_Filter::sanitizeMessageText($text);

		// фильтруем client_message_id
		$client_message_id = $this->_tryGetClientMessageId($client_message_id, "messages", "row168");

		// проверяем присланный список сообщений
		$this->_throwIfIncorrectMessageMapList($message_map_list, "row163", "row169", "row980");

		// сортируем message_map_list по порядку в диалоге
		$message_map_list = self::_doSortMessageMapListByMessageIndex($message_map_list);

		// если превысили лимит выбранных сообщений
		$max_selected_messages = self::MAX_SELECTED_MESSAGE_COUNT;
		if (count($message_map_list) > $max_selected_messages) {

			Gateway_Bus_Statholder::inc("messages", "row164");
			return $this->error(552, "exceeded the limit on the number of reposted messages");
		}

		// репостим сообщение
		return $this->_addRepostToReceiverConversation(
			$receiver_conversation_map,
			$message_map_list,
			$client_message_id,
			$text
		);
	}

	// добавляем репост в диалог получатель
	// @long - временно long из-за legacy
	protected function _addRepostToReceiverConversation(string $conversation_map, array $message_map_list, string $client_message_id, string $text):array {

		// проверяем что есть доступ к диалогу отправителю
		// в message_map_list гарантированно лежат сообщения из одного диалога, так что берем первое
		$donor_conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
		$donor_meta_row         = Type_Conversation_Meta::get($donor_conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($donor_meta_row["type"], Type_Conversation_Action::ADD_REPOST_FROM_CONVERSATION);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $donor_meta_row["users"])) {

			Gateway_Bus_Statholder::inc("messages", "row170");
			return $this->error(501, "User is not donor-conversation member");
		}

		// проверяем что есть доступ к диалогу получателю
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::ADD_REPOST_FROM_CONVERSATION);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("messages", "row171");
			return $this->error(501, "User is not receiver-conversation member");
		}
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $text);

		// проверяем, может ли пользователь писать в диалог
		try {
			Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $this->user_id);

			if ($this->method_version >= 3) {
				Domain_Group_Entity_Options::checkChannelRestrictionByMetaRow($this->user_id, $meta_row);
			}
		} catch (cs_Conversation_MemberIsDisabled) {
			return $this->error(532, "You can't write to this conversation because your opponent is blocked in our system");
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			return $this->error(2118001, "You can't write to this conversation because your opponent delete account");
		} catch (cs_Conversation_UserbotIsDisabled) {
			return $this->error(2134001, "You can't write to this conversation because userbot is disabled");
		} catch (cs_Conversation_UserbotIsDeleted) {
			return $this->error(2134002, "You can't write to this conversation because userbot is deleted");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2118002, "not enough right");
		}

		return $this->_addRepostV2($conversation_map, $message_map_list, $text, $client_message_id, $meta_row, $mention_user_id_list);
	}

	// репостим сообщения через addRepostV2
	// @long
	protected function _addRepostV2(string $conversation_map, array $message_map_list, string $text, string $client_message_id, array $meta_row, array $mention_user_id_list):array {

		try {

			$repost_message_list = Helper_Conversations::addRepostV2(
				$this->user_id,
				$text,
				$client_message_id,
				$message_map_list,
				$conversation_map,
				$meta_row,
				$mention_user_id_list,
				Type_Api_Platform::getPlatform()
			);
		} catch (cs_Message_Limit) {

			Gateway_Bus_Statholder::inc("messages", "row164");
			return $this->error(552, "exceeded the limit on the number of reposted messages");
		} catch (cs_MessageList_IsEmpty) {
			return $this->error(551, "message list for repost is empty");
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(541, "duplicate client_message_id");
			}
			throw new ParamException("client_message_id is duplicated");
		}

		$prepared_message_list = $this->_doPrepareMessageListV2($repost_message_list);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CONVERSATION_MESSAGE, $this->user_id, count($prepared_message_list));
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_CONVERSATION_MESSAGE);

		return $this->ok([
			"message_list" => (array) $prepared_message_list,
		]);
	}

	/**
	 * пожаловаться на сообщение пользователя
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doReportMessage():array {

		$message_key          = $this->post("?s", "message_key");
		$message_map          = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$reason               = $this->post("?s", "reason", "");
		$previous_message_key = $this->post("?s", "previous_message_key", "");
		$previous_message_map = "";
		if ($previous_message_key != "") {
			$previous_message_map = $this->_tryGetMessageMap($previous_message_key);
		}
		Gateway_Bus_Statholder::inc("messages", "row220");

		// проверяем диалог и сообщения
		$this->_throwIfNotConversationMessage($message_map);
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$this->_throwIfIncorrectPreviousMessageMap($previous_message_map, $conversation_map, $message_map);

		// инкрементим блокиковку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DOREPORTMESSAGE, "messages", "row223");

		$reason = $this->_doFormatReason($reason);

		// получаем мету диалога и проверяем, что пользователь участник диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::REPORT_MESSAGE_FROM_CONVERSATION);
		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "messages", "row225");

		Domain_Conversation_Action_Message_HideList::do($this->user_id, [$message_map], $conversation_map, $previous_message_map);

		// заносим репорт в очередь
		Gateway_Db_CompanyConversation_MessageReportHistory::insert($message_map, $this->user_id, $reason);

		return $this->ok();
	}

	// форматируем причину репорта сообщения
	protected function _doFormatReason(string $reason):string {

		// фильтруем
		$reason = Type_Api_Filter::sanitizeReason($reason);

		// обрабатываем эмодзи
		$reason = Type_Api_Filter::replaceEmojiWithShortName($reason);

		// удаляем лишние символы
		return Type_Api_Filter::sanitizeReason($reason);
	}

	/**
	 * получает определенное сообщение из диалога
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getMessage():array {

		$message_key = $this->post("?s", "message_key");
		$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$this->_throwIfNotConversationMessage($message_map);

		// проверяем, что пользователь — участник диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// если это диалог не типа public, то проводим проверку
		if (!Type_Conversation_Meta::isSubtypeOfPublicGroup($meta_row["type"])) {

			$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "messages", "row843");
		}

		// получаем message_data (объект сообщения и список реакций)
		try {

			$message_data = Helper_Conversations::getMessageData($this->user_id, $message_map, true);
			$message      = $message_data["message"];
		} catch (cs_Message_UserHaveNotPermission) {
			throw new ParamException(__METHOD__ . ": user have not permission to this message");
		} catch (cs_Message_IsNotExist) {
			throw new ParamException((__METHOD__ . ": message is not exist"));
		}

		// добавляем пользователей в action
		$action_user_list = Type_Conversation_Message_Main::getHandler($message)::getUsers($message);
		$this->action->users($action_user_list);

		// собираем ответ
		return $this->_getMessageOutput($message_data, $message_data["thread_rel"]);
	}

	// собираем ответ к методу getMessage
	protected function _getMessageOutput(array $message_data, array $thread_rel_list):array {

		$message = $message_data["message"];

		$prepared_message = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy(
			$message,
			$this->user_id,
			$thread_rel_list,
			$message_data["reaction_user_list"],
			$message_data["last_reaction_edited"]
		);

		$output["message"] = Apiv1_Format::conversationMessage($prepared_message);

		return $this->ok($output);
	}

	/**
	 * @return array
	 * @throws ParamException
	 * @throws \paramException
	 */
	public function getMyReactions():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		// получаем список реакций
		$output = Type_Conversation_Reaction_Main::getMyReactions($conversation_map, $this->user_id);

		return $this->ok([
			"message_reaction_list" => (array) $output,
		]);
	}

	/**
	 * получаем список реакции для сообщения
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getMessageReactions():array {

		$message_key = $this->post("?s", "message_key");
		$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);

		$this->_throwIfNotConversationMessage($message_map);

		// получаем мету диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::REACTION_TO_MESSAGE_FROM_CONVERSATION);
		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "messages", "row984");

		// получаем список реакции и их количество
		try {
			$message_reaction_list = Helper_Conversations::getMessageReactionCountList($message_map);
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		}

		return $this->ok([
			"message_reaction_list" => (array) $this->_formatReactionCountList($message_reaction_list),
		]);
	}

	// форматирует reaction_count_list для отдачи на frontend
	protected function _formatReactionCountList(array $message_reaction_list):array {

		$output = [];
		foreach ($message_reaction_list as $k => $v) {

			$output[] = [
				"reaction_name"  => $k,
				"count"          => count($v),
				"is_my_reaction" => isset($v[$this->user_id]) ? 1 : 0,
			];
		}

		return $output;
	}

	/**
	 * получаем Напоминание для сообщения
	 *
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws Domain_Conversation_Exception_Message_NotAllowForRemind
	 */
	public function getMessagesRemind():array {

		$message_key_list = $this->post(\Formatter::TYPE_ARRAY, "message_key_list");

		$message_map_list = [];
		foreach ($message_key_list as $key) {

			$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($key);
			$this->_throwIfNotConversationMessage($message_map);

			$message_map_list[] = $message_map;

			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

			// проверяем что сообщения присланы из одного диалога
			$conversation_map_list[$conversation_map] = 1;
			if (count($conversation_map_list) != 1) {
				throw new \paramException("one of the messages does not belong to the donor-dialogue");
			}
		}

		if (count($message_map_list) < 1) {
			throw new \paramException("message_key_list is empty");
		}

		// получаем мету диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"]);

		// получаем данные Напоминание у выбранного сообщения
		try {
			$remind_list = $this->_getMessageRemindData($message_map_list, $conversation_map);
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		} catch (\cs_RowIsEmpty) {
			return $this->error(2135002, "Not exist remind on message");
		}

		return $this->ok([
			"remind_list" => (array) $remind_list,
		]);
	}

	/**
	 * получаем данные Напоминания у выбранного сообщения
	 *
	 * @throws Domain_Conversation_Exception_Message_NotAllowForRemind
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_Message_IsDeleted
	 * @long
	 */
	protected function _getMessageRemindData(array $message_map_list, string $conversation_map):array {

		// получаем блок сообщения
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		[$block_row_list] = Domain_Conversation_Entity_Message_Block_Get::getBlockListRowByMessageMapList($conversation_map, $dynamic_row, $message_map_list);

		$message_remind_list = [];
		foreach ($message_map_list as $message_map) {

			$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

			if (!isset($block_row_list[$block_id])) {
				continue;
			}

			$block_row = $block_row_list[$block_id];

			// получаем сообщение из блока
			$message = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

			// проверяем, что у сообщения можно получить Напоминание
			Domain_Remind_Action_CheckMessageAllowedForRemind::do($message, $this->user_id);

			$remind = Type_Conversation_Message_Main::getHandler($message)::getRemind($message);
			if (count($remind) < 1) {
				continue;
			}

			$message_remind_list[] = Apiv2_Format::remind(
				Type_Conversation_Message_Main::getHandler($message)::getRemindId($message),
				Type_Conversation_Message_Main::getHandler($message)::getRemindAt($message),
				Type_Conversation_Message_Main::getHandler($message)::getRemindCreator($message),
				Type_Conversation_Message_Main::getHandler($message)::getRemindComment($message),
			);
		}

		return $message_remind_list;
	}

	/**
	 * получаем список пользователей поставивших реакцию на сообщение
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getReactionUsers():array {

		$message_key   = $this->post("?s", "message_key");
		$message_map   = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$reaction_name = $this->post("?s", "reaction_name");

		$this->_throwIfNotConversationMessage($message_map);

		// проверяем существование реакции
		$reaction_name = $this->_getReactionAliasIfExist($reaction_name, "messages", "row862");

		// получаем информацию о диалоге
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::REACTION_TO_MESSAGE_FROM_CONVERSATION);

		// пользователь - участник диалога?
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			throw new ParamException(__CLASS__ . ": user is not conversation member");
		}

		// получаем запись со списком пользователей и проверяем ее существование
		$user_list = Type_Conversation_Reaction_Main::getUserListForReaction($conversation_map, $message_map, $reaction_name);

		return $this->ok([
			"user_list" => (array) $user_list,
		]);
	}

	/**
	 * Получаем список реакций и пользователей поставивших ее на сообщение
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getReactionsUsersBatching():array {

		$message_key        = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map        = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$reaction_name_list = $this->post(\Formatter::TYPE_ARRAY, "reaction_name_list");

		$this->_throwIfReactionNameListIsIncorrect($reaction_name_list);
		$this->_throwIfNotConversationMessage($message_map);

		// проверяем существование реакций
		foreach ($reaction_name_list as $item) {
			$this->_getReactionAliasIfExist($item);
		}

		// проверяем, что пользователь состоит в диалоге
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::REACTION_TO_MESSAGE_FROM_CONVERSATION);
		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"]);

		try {

			if ($this->method_version >= 2) {
				Domain_Member_Entity_Permission::check($this->user_id, Permission::IS_GET_REACTION_LIST_ENABLED);
			}
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// получаем список реакций и пользователей ее поставивших
		$message_reaction_uniq_user_list = Type_Conversation_Reaction_Main::getUserListForReactionList($conversation_map, $message_map);

		// форматируем под формат ответа
		$message_reaction_list = $this->_makeReactionList($message_reaction_uniq_user_list);

		return $this->ok([
			"reaction_list" => (array) $message_reaction_list,
		]);
	}

	// выбрасываем ошибку, если список реакций некорректный
	protected function _throwIfReactionNameListIsIncorrect(array $reaction_name_list):void {

		// если пришел пустой массив реакций
		if (count($reaction_name_list) < 1) {
			throw new ParamException("passed empty reaction_name_list");
		}

		// если пришел слишком большой массив
		if (count($reaction_name_list) > self::_MAX_REACTION_COUNT + 1) {
			throw new ParamException("passed reaction_name_list biggest than max");
		}
	}

	// формируем reaction_list для ответа
	protected function _makeReactionList(array $message_reaction_uniq_user_list):array {

		$message_reaction_list = [];
		foreach ($message_reaction_uniq_user_list as $reaction_name => $user_list) {

			$message_reaction_list[] = [
				"reaction_name" => (string) $reaction_name,
				"user_list"     => (array) $user_list,
			];

			// добавляем пользователей в action
			$this->action->users($user_list);
		}

		return $message_reaction_list;
	}

	/**
	 * устанавливаем сообщение последним в левом меню
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function setMessageAsLast():array {

		$message_key = $this->post("?s", "message_key");
		$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);

		$this->_throwIfNotConversationMessage($message_map);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_SETMESSAGEASLAST, "messages", "row242");

		// проверяем, что пользователь является участником диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($meta_row["type"], Type_Conversation_Action::SET_MESSAGE_AS_LAST_FROM_CONVERSATION);
		$this->_throwIfUserNotConversationMember($this->user_id, $meta_row["users"], "messages", "row243");

		// получаем сообщение
		try {
			$message_data = Helper_Conversations::getMessageData($this->user_id, $message_map);
		} catch (cs_Message_UserHaveNotPermission) {

			Gateway_Bus_Statholder::inc("messages", "row244");
			throw new ParamException("User have not permission to this message");
		} catch (cs_Message_IsNotExist) {

			Gateway_Bus_Statholder::inc("messages", "row245");
			throw new ParamException("Message is not exist");
		}

		// проверяем что можем поставить сообщение последним в левом меню
		$this->_throwIfMessageNoNeedUpdateLeftMenu($message_data["message"], "messages", "row246");

		// отправляем задачу на актуализацию left_menu в phphooker
		Type_Phphooker_Main::updateLastMessageOnSetMessageAsLast(
			$conversation_map,
			$message_data["message"],
			$this->user_id
		);

		Gateway_Bus_Statholder::inc("messages", "row247");
		return $this->ok();
	}

	// проверяем что можем поставить сообщение последним в левом меню
	protected function _throwIfMessageNoNeedUpdateLeftMenu(array $message, string $namespace, string $row):void {

		if (!Type_Conversation_Message_Main::getHandler($message)::isNeedUpdateLeftMenu($message)) {

			Gateway_Bus_Statholder::inc($namespace, $row);
			throw new ParamException("Message no need update left menu");
		}
	}

	/**
	 * метод для получения списка диалогов в которые можем отправлять сообщение
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getAllowedConversationsForAddMessage():array {

		$conversation_key_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_key_list");

		$output = Domain_Conversation_Scenario_Api::getAllowedConversationsForAddMessage($conversation_key_list, $this->user_id);

		return $this->ok($output);
	}

	/**
	 * фиксируем затраченное время сотрудника
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doCommitWorkedHours():array {

		$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);
		$worked_hours     = $this->post(\Formatter::TYPE_FLOAT, "worked_hours");

		// проверяем, что передали корректный worked_hours и message_map_list
		$this->_throwIfIncorrectWorkedHours($worked_hours);
		$this->_throwIfIncorrectMessageMapList($message_map_list, "row1003", "row1004", "row1005");

		// если превысили лимит выбранных сообщений
		if (count($message_map_list) > self::MAX_SELECTED_MESSAGE_COUNT) {

			Gateway_Bus_Statholder::inc("messages", "row1006");
			return $this->error(552, "exceeded the limit on the number of reposted messages");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_DOCOMMITWORKEDHOURS, "messages", "row1007");

		// получаем donor_conversation_map и проверяем, что из него можно фиксировать время
		$donor_conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
		$donor_meta_row         = Type_Conversation_Meta::get($donor_conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($donor_meta_row["type"], Type_Conversation_Action::COMMIT_WORKED_HOURS_MESSAGE_FROM_CONVERSATION);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $donor_meta_row["users"])) {
			return $this->error(501, "User is not conversation member");
		}
		if (!Type_Conversation_Meta_Extra::isCanCommitWorkedHours($donor_meta_row["extra"])) {
			return $this->error(610, "Conversation option for commit worked hours is disabled");
		}

		return $this->_doCommitMessageWorkedHours($this->user_id, $worked_hours, $message_map_list);
	}

	// выбрасываем \paramException, если передали некорректное значение worked_hours
	protected function _throwIfIncorrectWorkedHours(float $worked_hours):void {

		if ($worked_hours < 0 || $worked_hours > self::_MAX_WORKED_HOURS) {

			Gateway_Bus_Statholder::inc("messages", "row1002");
			throw new ParamException(__METHOD__ . ": passed incorrect worked hours value");
		}
	}

	/**
	 * коммитим рабочии часы
	 *
	 * @throws \parseException
	 */
	protected function _doCommitMessageWorkedHours(int $user_id, float $worked_hours, array $message_map_list):array {

		// создаем объект worked_hours
		$worked_hours_data = Type_Conversation_Public_WorkedHours::doCommit($user_id, $worked_hours);

		// получаем все прикрепленные сообщения
		try {
			$forwarding_message_list = Helper_Conversations::getMessagesForForwarding($user_id, $message_map_list, false, true);
		} catch (cs_Message_Limit) {

			Gateway_Bus_Statholder::inc("messages", "row1006");
			return $this->error(552, "exceeded the limit on the number of reposted messages");
		} catch (cs_MessageList_IsEmpty) {

			Gateway_Bus_Statholder::inc("messages", "row1003");
			return $this->error(551, "message list for repost is empty");
		}

		// достаем ключ личного чата Heroes пользователя
		try {
			$user_conversation_rel_obj = Type_UserConversation_UserConversationRel::get($this->user_id, CONVERSATION_TYPE_PUBLIC_DEFAULT);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("row with public conversation not found");
		}

		// фиксируем сообщения с отработанными часами в личном чате Heroes пользователя
		$message_list = Helper_Conversations::doForwardMessageList($this->user_id, $user_conversation_rel_obj->conversation_map, $forwarding_message_list,
			$worked_hours_data["worked_hours_id"], $worked_hours_data["day_start_at_iso"], $worked_hours_data["worked_hours_created_at"]
		);

		return $this->ok([
			"message_list" => (array) $this->_prepareMessageListForResponse($message_list),
		]);
	}

	/**
	 * Пытаемся проявить требовательность
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @long
	 */
	public function tryExacting():array {

		$message_key_list = $this->post(\Formatter::TYPE_JSON, "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);
		$user_id_list     = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		Gateway_Bus_Statholder::inc("messages", "row1020");

		// проверяем параметры на корректность
		try {

			$this->_throwIfIncorrectParamsForExacting($user_id_list, $message_map_list);
		} catch (Domain_Conversation_Exception_User_IsAccountDeleted) {
			return $this->error(2118001, "You can't write to this conversation because your opponent delete account");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_TRYEXACTING, "messages", "row1027");

		// отдаём ошибку, если пользователь покинул диалог откуда выбранные сообщения
		$donor_conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
		$donor_meta_row         = Type_Conversation_Meta::get($donor_conversation_map);
		$this->_throwIfConversationTypeIsNotValidForAction($donor_meta_row["type"], Type_Conversation_Action::EXACTING_MESSAGE_FROM_CONVERSATION);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $donor_meta_row["users"])) {

			Gateway_Bus_Statholder::inc("messages", "row1028");
			return $this->error(501, "user is not donor-conversation member");
		}

		// получаем map группы Требовательность
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMap(Type_Company_Default::EXACTINGNESS);

		// отдаём ошибку, если пользователь НЕ участник группы Требовательность
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("messages", "row1029");
			return $this->error(507, "user is not receiver-conversation member");
		}

		// получаем выбранные сообщения
		try {
			$forwarding_message_list = Helper_Conversations::getMessagesForForwarding($this->user_id, $message_map_list, true, true);
		} catch (cs_Message_Limit) {

			Gateway_Bus_Statholder::inc("messages", "row1030");
			return $this->error(552, "exceeded the limit on the number of selected messages");
		} catch (cs_MessageList_IsEmpty) {

			Gateway_Bus_Statholder::inc("messages", "row1031");
			return $this->error(551, "message list for send is empty");
		}

		// проявляем Требовательность
		$repost_list = $this->_tryExacting($this->user_id, $forwarding_message_list, $user_id_list, $conversation_map, $meta_row, $donor_conversation_map);

		// приводим сообщения из группы к формату
		$formatted_repost_list = [];
		foreach ($repost_list as $v) {

			$prepared_message        = Type_Conversation_Message_Main::getHandler($v)::prepareForFormatLegacy($v, $this->user_id);
			$formatted_repost_list[] = Apiv1_Format::conversationMessage($prepared_message);
		}

		Gateway_Bus_Statholder::inc("messages", "row1032");

		return $this->ok([
			"message_list"     => (array) $formatted_repost_list,
			"conversation_map" => (string) $conversation_map,
		]);
	}

	/**
	 * Выбрасываем исключение, если передали некорректные параметры
	 *
	 * @long много проверок
	 * @return void
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws ParamException
	 */
	protected function _throwIfIncorrectParamsForExacting(array $user_id_list, array $message_map_list):void {

		// если какой-то из списков пуст
		if (count($user_id_list) < 1 || count($message_map_list) < 1) {
			throw new ParamException("incorrect params");
		}

		// если в списках имеются повторяющиеся значения
		if (count($user_id_list) != count(array_unique($user_id_list)) || count($message_map_list) != count(array_unique($message_map_list))) {
			throw new ParamException("there are duplicate values");
		}

		// если текущий пользователь оказался в списке пользователей для требовательности
		if (in_array($this->user_id, $user_id_list)) {
			throw new ParamException("user try exacting for himself");
		}

		// если превышен лимит выбранных для требовательности пользователей
		if (count($user_id_list) > self::MAX_USER_ID_LIST_FOR_EXACTINGNESS) {
			throw new ParamException("user_id_list limit exceeded");
		}

		// если превышен лимит выбранных для требовательности сообщений
		if (count($message_map_list) > self::MAX_SELECTED_MESSAGE_COUNT) {
			throw new ParamException("selected messages limit exceeded");
		}

		$user_info_list = Gateway_Bus_CompanyCache::getMemberList($user_id_list);
		if (count($user_info_list) < count($user_id_list)) {
			throw new ParamException("dont found user in company cache");
		}

		// проверяем что можем выписать требовательность
		foreach ($user_info_list as $member) {

			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
				throw new Domain_Conversation_Exception_User_IsAccountDeleted("user delete his account");
			}
		}

		// если сообщения не из одного диалога
		self::_checkConversationMapOnlyFromOneDialog($message_map_list, "row1026");
	}

	// пробуем проявить Требовательность
	protected function _tryExacting(int $sender_user_id, array $forwarding_message_list, array $user_id_list, string $conversation_map, array $meta_row, string $donor_conversation_map):array {

		// добавляем Требовательность в карточку пользователя
		[$exactingness_id_list_by_user_id, $week_count, $month_count] = Gateway_Socket_Company::addExactingnessToEmployeeCard($this->user_id, $user_id_list);

		// для каждого выбранного для Требовательности пользователя
		$message_list = [];
		foreach ($exactingness_id_list_by_user_id as $receiver_user_id => $exactingness_id) {

			// подготавливаем сообщения типа репост
			$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeRepost($this->user_id, "", generateUUID(), $forwarding_message_list);

			// добавляем additional-поля для Требовательности, прикрепляя пользователя, которому предъявляем и id требовательности
			$message_list[] = Type_Conversation_Message_Main::getHandler($message)
				::attachExactingnessData($message, $receiver_user_id, $exactingness_id);
		}

		// выдаем Требовательность - добавляем сообщения-требовательность в группу Требовательность
		$repost_list = Helper_Conversations::tryExacting($sender_user_id, $message_list, $conversation_map, $meta_row, $week_count, $month_count);

		// действия после репоста
		Helper_Conversations::doAfterRepost($this->user_id, $repost_list, $donor_conversation_map, $meta_row);

		return $repost_list;
	}

	// получаем данные для формирования сущности Требовательности
	// @long - switch..case по типу файла
	public static function getDataForExactingnessEntity(array $message, array $message_data_list):array {

		$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			// если это обычный текст - достаем text
			case CONVERSATION_MESSAGE_TYPE_TEXT:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_TEXT:
			case CONVERSATION_MESSAGE_TYPE_RESPECT:

				$message_data_list[] = [
					"type" => "text",
					"text" => Type_Conversation_Message_Main::getHandler($message)::getText($message),
				];
				break;

			// если это файл - достаем file_map
			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE:

				$message_data_list[] = [
					"type"     => "file",
					"file_map" => Type_Conversation_Message_Main::getHandler($message)::getFileMap($message),
				];
				break;

			// если это звонок - достаем call_map
			case CONVERSATION_MESSAGE_TYPE_CALL:

				$message_data_list[] = [
					"type"     => "call",
					"call_map" => Type_Conversation_Message_Main::getHandler($message)::getCallMap($message),
				];
				break;

			// если это репост - достаем text & данные репостнутых сообщений
			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				// достаем сообщения из репоста
				$reposted_message_list = Type_Conversation_Message_Main::getHandler($message)::getRepostedMessageList($message);

				// для каждого сообщения в репосте
				$reposted_message_data = [];
				foreach ($reposted_message_list as $v) {
					$reposted_message_data = self::getDataForExactingnessEntity($v, $reposted_message_data);
				}

				$message_data_list[] = [
					"type"                  => "repost",
					"text"                  => Type_Conversation_Message_Main::getHandler($message)::getText($message),
					"reposted_message_data" => $reposted_message_data,
				];
				break;

			// если это цитата/старая версия цитаты - достаем text & quoted_message_list
			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_QUOTE:

				// достаем сообщения из цитаты
				$quoted_message_list = Type_Conversation_Message_Main::getHandler($message)::getQuotedMessageList($message);

				// для каждого сообщения в цитате
				$quoted_message_data = [];
				foreach ($quoted_message_list as $v) {
					$quoted_message_data = self::getDataForExactingnessEntity($v, $quoted_message_data);
				}

				$message_data_list[] = [
					"type"                => "quote",
					"text"                => Type_Conversation_Message_Main::getHandler($message)::getText($message),
					"quoted_message_data" => $quoted_message_data,
				];
		}

		return $message_data_list;
	}

	/**
	 * метод для получения информации о ряде сообщений
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getMessageBatching():array {

		$message_key_list = $this->post("?j", "message_key_list");
		$message_map_list = $this->_tryGetMessageMapList($message_key_list);
		$signature        = $this->post("?s", "signature", "");
		Gateway_Bus_Statholder::inc("messages", "row1040");

		$this->_throwIfIncorrectSignature($message_map_list, $signature);
		$this->_throwIfIncorrectBatchingMessageMapList($message_map_list, "row1041", "row1042", "row1043");

		// получаем все необходимые данные о сообщениях
		$is_need_check_permission = mb_strlen($signature) == 0;

		try {

			[$message_data_list, $not_access_message_map_list] = Helper_Messages::getMessageDataListFromAnotherConversations(
				$this->user_id,
				$message_map_list,
				$is_need_check_permission
			);
		} catch (\cs_UnpackHasFailed|\cs_RowIsEmpty) {
			throw new ParamException(__METHOD__ . ": passed incorrect message_key_list");
		}
		// получаем список сообщений, к которым оказался доступ и приводим их к формату
		$message_list = array_column($message_data_list, "message");

		// зашифровываем message_map_list в message_key_list
		$no_access_message_key_list = $this->_doEncryptMessageMapList($not_access_message_map_list);

		Gateway_Bus_Statholder::inc("messages", "row1044");
		return $this->ok([
			"message_list"               => (array) $this->_prepareMessageListForResponse($message_list),
			"no_access_message_key_list" => (array) $no_access_message_key_list,
		]);
	}

	/**
	 * проверяем, что прислана корректная signature для получения сообщений
	 *
	 * @throws \paramException
	 */
	protected function _throwIfIncorrectSignature(array $message_map_list, string $signature):void {

		if ($signature == "") {
			return;
		}

		// получаем подпись и проверяем, что корректная
		$true_signature = $this->_makeSignatureForGetMessageBatching($message_map_list);
		if ($true_signature != $signature) {
			throw new ParamException(__METHOD__ . ": passed incorrect signature");
		}
	}

	/**
	 * формируем подпись, с помощью который пользователь сможет получить доступ к сообщениям
	 */
	protected function _makeSignatureForGetMessageBatching(array $message_map_list):string {

		// сортируем message_map_list
		sort($message_map_list);

		// формируем строку для хэширования
		$temp = implode(".", $message_map_list);

		// добавляем user_id в конец
		$temp .= $this->user_id;

		return sha1($temp);
	}

	// выбрасываем exception, если передали некорректный массив с ключами сообщений диалога
	protected function _throwIfIncorrectBatchingMessageMapList(array $message_map_list, string $row_is_empty_array, string $row_is_limit_exceeded, string $row_is_duplicate):void {

		if (count($message_map_list) < 1) {

			Gateway_Bus_Statholder::inc("messages", $row_is_empty_array);
			throw new ParamException("messages array are empty");
		}

		// проверяем, что не привысили лимит
		if (count($message_map_list) > self::_MAX_GET_MESSAGE_BATCHING_COUNT) {

			Gateway_Bus_Statholder::inc("messages", $row_is_limit_exceeded);
			throw new ParamException("messages array are empty");
		}

		// проверяем что список сообщений уникален
		$message_map_list_uniq = array_unique($message_map_list);
		if (count($message_map_list_uniq) != count($message_map_list)) {

			Gateway_Bus_Statholder::inc("messages", $row_is_duplicate);
			throw new ParamException("messages can't be duplicated");
		}
	}

	/**
	 * Зашифровываем message_map_list в message_key_list
	 */
	protected function _doEncryptMessageMapList(array $message_map_list):array {

		$output = [];
		foreach ($message_map_list as $item) {
			$output[] = \CompassApp\Pack\Message\Conversation::doEncrypt($item);
		}

		return $output;
	}

	/**
	 * отправить собеседнику контакты на пользователей
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_PlatformNotFound
	 */
	public function shareMember():array {

		$conversation_key   = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$share_user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "share_user_id_list");
		$conversation_map   = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CONVERSATIONS_SHAREMEMBER);

		try {
			$prepared_message = Domain_Conversation_Scenario_Api::shareMember($this->user_id, $conversation_map, $share_user_id_list);
		} catch (cs_ConversationIsLocked) {
			throw new ParamException("conversation is locked");
		} catch (cs_IncorrectUserIdList) {
			throw new ParamException("passed invalid share_user_id_list");
		} catch (cs_UserIdListIsNotCompanyMember $e) {
			return $this->error(1013, "Сan not share user contacts", Apiv1_Format::makeErrorCode1013($e->getUserIdListIsNotCompanyMember()));
		} catch (cs_UserIsNotMember) {
			return $this->error(501, "User is not conversation member");
		} catch (cs_UserIsNotCompanyMember) {
			return $this->error(1002, "User is not company member");
		}

		// добавляем пользователей к ответу
		$this->action->users($share_user_id_list);

		return $this->ok([
			"message" => (array) Apiv1_Format::conversationMessage($prepared_message),
		]);
	}

	# endregion
	##########################################################

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// форматируем и приводим под нужный формат каждое сообщение в массиве
	protected function _doPrepareMessageListV2(array $message_list):array {

		// подводим под формат и отдаем
		$prepared_message_list = [];
		foreach ($message_list as $message) {

			$prepared_message        = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy($message, $this->user_id);
			$prepared_message_list[] = Apiv1_Format::conversationMessage($prepared_message);
		}

		return $prepared_message_list;
	}

	// получить алиас реакции, если существует, иначе возвращает незименное название реакции обратно
	protected function _getReactionAliasIfExist(string $reaction_name, string $namespace = null, string $row = null):string {

		$reaction_name = Type_Conversation_Reaction_Main::getReactionNameIfExist($reaction_name);
		if (mb_strlen($reaction_name) < 1) {

			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}
			throw new ParamException(__CLASS__ . ": reaction does not exist");
		}

		return $reaction_name;
	}

	// отбираем диалоги с нормальными пользователями
	protected function _filterAllowedConversationList(array $left_menu_list, array $user_id_list):array {

		// получаем информацию о пользователях
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($user_id_list, false);

		$output = [];

		// отбираем только валидных пользователей
		foreach ($left_menu_list as $v) {

			$user_info = $user_info_list[$v["opponent_user_id"]];

			// если пользователь заблокирован, то пропускаем его
			if (\CompassApp\Domain\Member\Entity\Member::isDisabledProfile($user_info->role)) {
				continue;
			}

			// если пользователь бот, то пропускаем
			if (!Type_User_Main::isHuman($user_info->npc_type)) {
				continue;
			}

			$output[] = $v;
		}

		return $output;
	}

	// получаем список пользователей
	protected function _getUserIdList(array $conversation_list):array {

		$user_id_list = [];

		// проходимся по списку юзеров и получаем список id пользователей
		foreach ($conversation_list as $item) {
			$user_id_list[] = $item["opponent_user_id"];
		}

		return $user_id_list;
	}

	// метод для вывода найденных пользователей
	protected function _makeAllowedUsersOutput(array $left_menu_list = [], int $has_next = 0):array {

		return $this->_makeAllowedUsersOutputNew($left_menu_list, $has_next);
	}

	// метод для вывода найденных пользователей с новым полным left_menu
	protected function _makeAllowedUsersOutputNew(array $left_menu_list, int $has_next = 0):array {

		$user_id_list = [];

		// форматируем левое меню для клиента
		$formatted_left_menu_list = [];
		foreach ($left_menu_list as $item) {

			// подготавливаем и форматируем сущность left_menu
			$prepared_left_menu         = Type_Conversation_Utils::prepareLeftMenuForFormat($item);
			$formatted_left_menu_list[] = Apiv1_Format::leftMenu($prepared_left_menu);
			$user_id_list[]             = (int) $item["opponent_user_id"];
		}
		$this->action->users($user_id_list);

		return $this->ok([
			"left_menu_list" => (array) $formatted_left_menu_list,
			"has_next"       => (int) $has_next,
		]);
	}

	// выбрасываем exception если ид сообщения не валиден иначе отдаем его
	protected function _tryGetClientMessageId(string $client_message_id, string $namespace, string $row):string {

		$client_message_id = Type_Api_Filter::sanitizeClientMessageId($client_message_id);

		if (mb_strlen($client_message_id) < 1) {

			Gateway_Bus_Statholder::inc($namespace, $row);
			throw new ParamException("incorrect client_message_id");
		}

		return $client_message_id;
	}

	// получаем message_map_list из массива ключей
	protected function _tryGetMessageMapList(array $message_key_list):array {

		$message_map_list = [];
		foreach ($message_key_list as $v) {

			// если передали левак вместо строки в ключе
			if (!is_string($v)) {
				throw new ParamException("message key is not string");
			}

			// получаем message_map  и conversation_map
			$message_map = $this->_tryGetMessageMap($v, "row162");

			// дополняем сообщения
			$message_map_list[] = $message_map;
		}

		return $message_map_list;
	}

	// получаем message_map из message_key
	protected function _tryGetMessageMap(string $message_key, string $row_is_decrypt_failed = null):string {

		try {
			$message_map = \CompassApp\Pack\Message\Conversation::doDecrypt($message_key);
		} catch (\cs_DecryptHasFailed) {

			if (!is_null($row_is_decrypt_failed)) {
				Gateway_Bus_Statholder::inc("messages", $row_is_decrypt_failed);
			}
			throw new ParamException("wrong message key");
		}

		return $message_map;
	}

	/**
	 * форматируем список сущностей левого меню
	 *
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @long - множество условий
	 */
	protected function _doFormatLeftMenuList(array $left_menu_list, array $filter_client_npc_type = [], array $meta_list = []):array {

		$opponent_list   = [];
		$filter_npc_type = [];
		if (count($filter_client_npc_type) > 0) {

			// получаем список пользователей
			$user_id_list    = $this->_getUserIdList($left_menu_list);
			$opponent_list   = Gateway_Bus_CompanyCache::getShortMemberList($user_id_list, false);
			$filter_npc_type = $this->_getFilterNpcTypeList($filter_client_npc_type);
		}

		$formatted_left_menu_list = [];
		$user_list                = [];
		foreach ($left_menu_list as $v) {

			// если нужно фильтровать по npc, и npc собеседника не находится в списке тех, которых нужно вернуть
			$opponent_user_id = $v["opponent_user_id"];
			if (count($filter_npc_type) > 0 && $opponent_user_id != 0 && (!isset($opponent_list[$opponent_user_id]) || !in_array($opponent_list[$opponent_user_id]->npc_type, $filter_npc_type))) {
				continue;
			}

			// если тип диалога в левом меню - сингл
			if (Type_Conversation_Meta::isSubtypeOfSingle($v["type"])) {
				$user_list[] = formatInt($v["opponent_user_id"]);
			}

			// если имеется last_message, то достаем отправителя и получателя сообщения (в кейсах спасибо/требовательность/достижение)
			if (Type_Conversation_Utils::isExistLastMessage($v)) {
				$user_list = array_merge($user_list, Type_Conversation_Utils::getLastMessageUsers($v["last_message"]));
			}

			// подготавливаем и форматируем сущность left_menu
			$prepared_left_menu         = Type_Conversation_Utils::prepareLeftMenuForFormat($v);
			$formatted_left_menu_list[] = Apiv1_Format::leftMenu($prepared_left_menu);
		}

		$this->action->users(array_unique($user_list));

		return $formatted_left_menu_list;
	}

	/**
	 * получаем список npc_type для фильтра
	 *
	 * @throws \parseException
	 */
	protected function _getFilterNpcTypeList(array $filter_client_npc_type):array {

		$filter_npc_type = [];
		foreach ($filter_client_npc_type as $user_type) {
			$filter_npc_type[] = Type_User_Main::getNpcTypeByUserType($user_type);
		}

		return $filter_npc_type;
	}

	/**
	 * Выполняет действие чтение сообщений.
	 * Это не то же самое, что прочтение диалога — это точечное чтение.
	 *
	 * @post message_key
	 */
	public function doReadMessage():array {

		$message_key = $this->post("?s", "message_key");
		$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что пользователь участник диалога
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("messages", "row106");
			return $this->error(501, "User is not a conversation member");
		}

		try {

			[$local_date, $local_time, $_] = getLocalClientTime();
			$prepared_message = Helper_Conversations::markMessageAsRead($conversation_map, $message_map, $this->user_id, $meta_row, $local_date, $local_time);
		} catch (cs_Message_IsNotAllowToMarkAsRead) {
			return $this->error(550, "Message is not allowed to be read");
		} catch (cs_Message_IsDeleted) {

			Gateway_Bus_Statholder::inc("messages", "row109");
			return $this->error(549, "Message is deleted");
		}

		// формируем ответ
		return $this->ok([
			"message" => (array) Apiv1_Format::conversationMessage($prepared_message),
		]);
	}

	// -------------------------------------------------------
	// PROTECTED THROW METHODS
	// -------------------------------------------------------

	/*
	 * protected методы, которые выполняют атомарные проверки как правило входящих параметров
	 * и выбрасывают \paramException в случае неудовлетворительного результата
	 */

	// выбрасываем exception если map предыдущего сообщения не корректен
	protected function _throwIfIncorrectPreviousMessageMap(string $previous_message_map, string $conversation_map, string $message_map):void {

		if ($previous_message_map == "") {
			return;
		}

		$conversation_map_from_previous_message_map = \CompassApp\Pack\Message\Conversation::getConversationMap($previous_message_map);
		if ($conversation_map != $conversation_map_from_previous_message_map) {
			throw new ParamException("previous message from other conversation");
		}

		$previous_message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($previous_message_map);
		$message_index          = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);
		if ($message_index <= $previous_message_index) {
			throw new ParamException("message index less then previous message index");
		}
	}

	// проверяем, что message_map из диалога
	protected function _throwIfMessageMapIsNotFromConversation(string $message_map):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("the message is not from conversation");
		}
	}

	// проверяем, что сообщение из диалога
	protected function _throwIfNotConversationMessage(string $message_map):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("The message is not from conversation");
		}
	}

	// проверяем, что пользователь является участником диалога
	protected function _throwIfUserNotConversationMember(int $user_id, array $users, string $namespace = null, string $row = null):void {

		if (!Type_Conversation_Meta_Users::isMember($user_id, $users)) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("User is not conversation member");
		}
	}

	// выбрасываем ошибку, если у пользователя нет такого диалога в левом меню
	protected function _throwIfConversationIsNotExistInLeftMenu(array $left_menu_row, string $namespace = null, string $row = null):void {

		if (!isset($left_menu_row["user_id"])) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("action is not allowed");
		}
	}

	// выбрасываем исключение если передан некорректный список сообщений
	protected function _throwIfIncorrectMessageMapList(array $message_map_list, string $row_is_empty_array, string $row_is_duplicate, string $row_is_not_from_one_conversation):void {

		if (count($message_map_list) < 1) {

			Gateway_Bus_Statholder::inc("messages", $row_is_empty_array);
			throw new ParamException("messages array are empty");
		}

		// проверяем что список сообщений уникален
		$message_map_list_uniq = array_unique($message_map_list);
		if (count($message_map_list_uniq) != count($message_map_list)) {

			Gateway_Bus_Statholder::inc("messages", $row_is_duplicate);
			throw new ParamException("messages can't be duplicated");
		}

		// проверяем что сообщения присланы из одного диалога
		self::_checkConversationMapOnlyFromOneDialog($message_map_list, $row_is_not_from_one_conversation);
	}

	/**
	 * Проверка, что все полученные сообщения находятся в одном диалоге
	 *
	 * @param array  $message_map_list
	 * @param string $row_is_not_from_one_conversation
	 *
	 * @return void
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _checkConversationMapOnlyFromOneDialog(array $message_map_list, string $row_is_not_from_one_conversation):void {

		$conversation_map_list = [];
		foreach ($message_map_list as $v) {

			if (!\CompassApp\Pack\Message::isFromConversation($v)) {
				throw new ParamException("message not from conversation");
			}

			// проверяем, что сообщения из одного диалога
			$conversation_map                         = \CompassApp\Pack\Message\Conversation::getConversationMap($v);
			$conversation_map_list[$conversation_map] = 1;
			if (count($conversation_map_list) != 1) {

				Gateway_Bus_Statholder::inc("messages", $row_is_not_from_one_conversation);
				throw new ParamException("one of the messages does not belong to the donor-dialogue");
			}
		}
	}

	// сортируем полученые сообщения по порядку нахождения их в диалоге
	protected static function _doSortMessageMapListByMessageIndex(array $message_map_list):array {

		$grouped_message_map_list = [];
		foreach ($message_map_list as $message_map) {

			$message_index                            = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);
			$grouped_message_map_list[$message_index] = $message_map;
		}

		ksort($grouped_message_map_list);
		return array_values($grouped_message_map_list);
	}

	// бросам исключение, если диалог не подходит для этого действия
	protected function _throwIfConversationTypeIsNotValidForAction(int $conversation_type, string $action):void {

		Type_Conversation_Action::assertAction((int) $conversation_type, $action);
	}

	// выбрасываем исключение, если пытаются выполнить метод неприменимый к диалогам типа public
	protected function _throwIfConversationIsSubtypeOfPublic(int $conversation_type, string $namespace = null, string $row = null):void {

		if (Type_Conversation_Meta::isSubtypeOfPublicGroup($conversation_type)) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("conversation is subtype of public");
		}
	}

	// подготавливаем список сообщений к ответу
	protected function _prepareMessageListForResponse(array $message_list):array {

		$output    = [];
		$user_list = [];
		foreach ($message_list as $message) {

			// получаем пользователей из сообщения
			$users     = Type_Conversation_Message_Main::getHandler($message)::getUsers($message);
			$user_list = array_merge($user_list, $users);

			// форматируем сообщения и добавлем их к ответу
			$prepared_message = Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy($message, $this->user_id);
			$output[]         = (object) Apiv1_Format::conversationMessage($prepared_message);
		}

		// добавляем пользователей к ответу
		$this->action->users($user_list);

		return $output;
	}
}
