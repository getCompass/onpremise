<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс для работы с сущностью left_menu - информация о персональных настройках/состояниях пользователя, как участника диалога
 */
class Type_Conversation_LeftMenu {

	// по какой причине юзер покинул диалог
	public const LEAVE_REASON_KICKED = 1;
	public const LEAVE_REASON_LEAVED = 2;

	protected const LEAVE_REASON_TITLE = [
		self::LEAVE_REASON_KICKED => "kicked",
		self::LEAVE_REASON_LEAVED => "leaved",
	];

	// возвращает сущность left_menu по user_id & conversation_map
	public static function get(int $user_id, string $conversation_map):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);
	}

	// возвращает количество диалогов левого меню пользователя
	public static function getUserLeftMenuCount(int $user_id):int {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getCountLeftMenu($user_id);
	}

	// возвращает диалоги left_menu по offset
	public static function getByOffset(int $user_id, int $limit, int $offset):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getByOffset($user_id, $limit, $offset);
	}

	// возвращает сущности left_menu по списку идентификаторов
	public static function getList(int $user_id, array $conversation_map_list, bool $is_assoc = false):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getList($user_id, $conversation_map_list, $is_assoc);
	}

	// возвращает сущности left_menu по списку идентификаторов, которые доступны в левом меню
	public static function getListFromLeftMenu(int $user_id, array $conversation_map_list):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getListFromLeftMenu($user_id, $conversation_map_list);
	}

	// возвращает запись из левого меню по id оппонента
	public static function getByOpponentId(int $user_id, int $opponent_user_id):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getByOpponentId($user_id, $opponent_user_id);
	}

	// возвращает избранные диалоги из left_menu
	public static function getLeftMenuFavorites(int $user_id, int $limit):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getLeftMenuFavorites($user_id, $limit);
	}

	// пересчитываем общее количество непрочитанных сообщений
	public static function recountTotalUnread(int $user_id):void {

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// получаем сумму непрочитанных сообщений
		$total_unread_count_row = Gateway_Db_CompanyConversation_UserLeftMenu::getTotalUnreadCounters($user_id);

		// обновляем значение total_unread_count
		Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
			"conversation_unread_count" => $total_unread_count_row["conversation_unread_count"] ?? 0,
			"message_unread_count"      => $total_unread_count_row["message_unread_count"] ?? 0,
			"updated_at"                => time(),
		]);

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	/**
	 * помечаем диалоги прочитанными
	 *
	 * @throws \parseException
	 */
	public static function setConversationsAsRead(int $user_id, array $conversation_map_list, array $left_menu_list):int {

		// генерируем version
		$max_previous_version = 0;
		if (count($left_menu_list) > 0) {

			$version_list         = array_column($left_menu_list, "version");
			$max_previous_version = max($version_list);
		}
		$left_menu_version = Domain_User_Entity_Conversation_LeftMenu::generateVersion($max_previous_version);

		// если нечего обновлять
		if (count($conversation_map_list) < 1) {
			return $left_menu_version;
		}

		Gateway_Db_CompanyConversation_UserLeftMenu::setList($user_id, $conversation_map_list, [
			"last_read_message_map" => "",
			"unread_count"          => 0,
			"is_have_notice"        => 0,
			"is_mentioned"          => 0,
			"mention_count"         => 0,
			"version"               => $left_menu_version,
		]);

		return $left_menu_version;
	}

	/**
	 * обнуляем количество непрочитанных сообщений
	 *
	 */
	public static function nullifyTotalUnread(int $user_id):void {

		Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
			"conversation_unread_count" => 0,
			"message_unread_count"      => 0,
			"updated_at"                => time(),
		]);
	}

	// получаем количество избранных диалогов
	public static function getCountOfFavorite(int $user_id):int {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getFavoritesCount($user_id);
	}

	// устанавливает новый статус is_favorite для сущности left_menu
	public static function setIsFavorite(bool $is_favorite, array $left_menu_row):array {

		$set = [
			"is_favorite" => $is_favorite === true ? 1 : 0,
			"updated_at"  => time(),
		];

		// обновляем данные в левом меню
		Domain_User_Action_Conversation_UpdateLeftMenu::do($left_menu_row["user_id"], $left_menu_row["conversation_map"], $set);

		// обновляем параметр левое меню
		$left_menu_row = array_merge($left_menu_row, $set);

		return self::_formatLeftMenu($left_menu_row);
	}

	// получаем форматированное левое меню с использованием $conversation_map
	protected static function _formatLeftMenu(array $left_menu):array {

		$prepared_left_menu_row = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu);
		return Apiv1_Format::leftMenu($prepared_left_menu_row);
	}

	// устанавливаем новый статус is_muted и muted_until для сущности left_menu
	public static function doMute(int $user_id, string $conversation_map, bool $is_muted, int $muted_until):void {

		$set = [
			"is_muted"    => $is_muted ? 1 : 0,
			"muted_until" => $muted_until,
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
	}

	// обнуляем is_muted и muted_until для сущности left_menu
	public static function doUnmute(int $user_id, string $conversation_map):void {

		$set = [
			"is_muted"    => 0,
			"muted_until" => 0,
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
	}

	// устанавливаем новый статус is_muted для сущности left_menu
	public static function setIsMuted(int $user_id, string $conversation_map, bool $is_muted):void {

		$set = [
			"is_muted" => $is_muted ? 1 : 0,
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
	}

	// помечаем диалог прочтенным
	public static function setAsRead(int $user_id, string $conversation_map, string $need_read_message_map):array {

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// если чат пустой
		if (mb_strlen($need_read_message_map) < 1) {

			$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
			self::_rollbackAndThrowIfNotExistRowInLeftMenu($left_menu_row);

			// читаем сообщение
			$left_menu_row = self::_setConversationAsRead($user_id, $need_read_message_map, $left_menu_row);
			Gateway_Db_CompanyConversation_Main::commitTransaction();
			return $left_menu_row;
		}

		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		self::_rollbackAndThrowIfNotExistRowInLeftMenu($left_menu_row);

		$need_read_message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($need_read_message_map);
		$last_read_message_index = self::_getLastReadMessageIndex($left_menu_row);

		// если индекс пришедшего сообщения меньше текущего прочитанного то выходим
		if ($need_read_message_index < $last_read_message_index) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return $left_menu_row;
		}

		// читаем сообщение
		$left_menu_row = self::_setConversationAsRead($user_id, $need_read_message_map, $left_menu_row);

		Gateway_Db_CompanyConversation_Main::commitTransaction();
		return $left_menu_row;
	}

	// откатываем транзакцию и выбрасываем экзепшен если нет записи в левом меню
	protected static function _rollbackAndThrowIfNotExistRowInLeftMenu(array $left_menu_row):void {

		// запись в левом меню не существует
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new cs_LeftMenuRowIsNotExist();
		}
	}

	// получаем индекс последнего прочитанного сообщения
	protected static function _getLastReadMessageIndex(array $left_menu_row):int {

		$message_index = 0;
		if (mb_strlen($left_menu_row["last_read_message_map"]) > 0) {
			$message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($left_menu_row["last_read_message_map"]);
		}

		return $message_index;
	}

	/**
	 * обновляем последнее прочитанного сообщение
	 *
	 * @long
	 */
	protected static function _setConversationAsRead(int $user_id, string $need_read_message_map, array $left_menu_row):array {

		$updated_at = time();

		// если есть непрочитанные
		if ($left_menu_row["unread_count"] > 0) {

			Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
				"total_unread_count" => "total_unread_count - " . $left_menu_row["unread_count"],
				"updated_at"         => $updated_at,
			]);
		}

		$set = [
			"last_read_message_map" => $need_read_message_map,
			"unread_count"          => 0,
			"is_have_notice"        => 0,
		];

		$left_menu_row["last_read_message_map"] = $need_read_message_map;
		$left_menu_row["unread_count"]          = 0;
		$left_menu_row["is_have_notice"]        = 0;

		if ($left_menu_row["is_mentioned"] == 1) {

			$set["is_mentioned"]  = 0;
			$set["mention_count"] = 0;
			$set["updated_at"]    = $updated_at;

			$left_menu_row["is_mentioned"]  = 0;
			$left_menu_row["mention_count"] = 0;
			$left_menu_row["updated_at"]    = $updated_at;
		}

		Gateway_Db_CompanyConversation_UserLeftMenu::set($user_id, $left_menu_row["conversation_map"], $set);

		return $left_menu_row;
	}

	// обнуляет unread_count, устанавливает время очистки диалога и очищает last_read_message_map и last_message при очистке диалога
	public static function setCleared(int $user_id, string $conversation_map, int $clear_until):void {

		$set = [
			"clear_until"           => $clear_until,
			"last_read_message_map" => "",
			"unread_count"          => 0,
			"is_have_notice"        => 0,
			"is_mentioned"          => 0,
			"mention_count"         => 0,
			"last_message"          => [],
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
	}

	// обнуляет unread_count, устанавливает время очистки диалога и очищает last_read_message_map и last_message при очистке диалога для списка пользователей
	public static function setClearedForUserIdList(array $user_id_list, string $conversation_map, int $clear_until):void {

		// генерируем version (получаем previous_version из текущего времени, так как это выгоднее чем запросить по каждому пользователю запись левого меню)
		$previous_version = Domain_User_Entity_Conversation_LeftMenu::getVersionByCurrentTime();
		$version          = Domain_User_Entity_Conversation_LeftMenu::generateVersion($previous_version);

		Gateway_Db_CompanyConversation_UserLeftMenu::setForUserIdList($user_id_list, $conversation_map, [
			"clear_until"           => $clear_until,
			"last_read_message_map" => "",
			"unread_count"          => 0,
			"is_have_notice"        => 0,
			"is_mentioned"          => 0,
			"mention_count"         => 0,
			"last_message"          => [],
			"version"               => $version,
		]);
	}

	// обнуляет unread_count и is_have_notice, устанавливает время очистки диалога и очищает last_read_message_map и last_message + скрывает диалог в левом меню
	public static function hideAndClearForUser(int $user_id, string $conversation_map, int $clear_until):void {

		$set = [
			"clear_until"           => $clear_until,
			"is_have_notice"        => 0,
			"is_mentioned"          => 0,
			"mention_count"         => 0,
			"is_hidden"             => 1,
			"is_favorite"           => 0,
			"last_read_message_map" => "",
			"unread_count"          => 0,
			"last_message"          => [],
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
	}

	// получить список групповых диалогов, в которых мы имеем административные права
	public static function getManaged(int $user_id, int $count, int $offset):array {

		$roles = Type_Conversation_Meta_Users::MANAGED_ROLES;

		return Gateway_Db_CompanyConversation_UserLeftMenu::getListWhereRole($user_id, $count, $offset, $roles);
	}

	// получить группу службы поддержки пользователя
	public static function getSupportGroupByUser(int $user_id):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getSupportGroupByUser($user_id);
	}

	/**
	 * получить список групповых диалогов, в которых мы являемся участниками
	 *
	 */
	public static function getGroupDefaultListByDefaultRole(int $user_id, int $count, int $offset):array {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getListWhereRoleAndType(
			$user_id, $count, $offset, [Type_Conversation_Meta_Users::ROLE_DEFAULT], [CONVERSATION_TYPE_GROUP_DEFAULT]
		);
	}

	// событие при смене conversation_name группового диалога
	public static function onChangeName(int $user_id, string $conversation_map, string $conversation_name):void {

		// обновляем запись в left_menu участника
		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, [
			"conversation_name" => $conversation_name,
		]);
	}

	// событие при смене аватара группового диалога
	public static function onChangeAvatar(int $user_id, string $conversation_map, string $avatar_file_map):void {

		// обновляем запись в left_menu участника
		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, [
			"avatar_file_map" => $avatar_file_map,
		]);
	}

	/**
	 * обновляем основную информацию о группе
	 *
	 * @throws \parseException
	 */
	public static function onChangeGroupBaseInfo(int $user_id, string $conversation_map, string|false $group_name, string|false $avatar_file_map):void {

		$set = [];

		if ($group_name !== false) {
			$set["conversation_name"] = $group_name;
		}

		if ($avatar_file_map !== false) {
			$set["avatar_file_map"] = $avatar_file_map;
		}

		if (count($set) == 0) {
			return;
		}

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
	}

	// обновляет updated_at диалога, поднимая его наверх
	public static function doLiftUp(int $user_id, string $conversation_map, int $current_time):void {

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, [
			"updated_at" => $current_time,
		]);
	}

	// получить причину покидания диалога
	public static function getLeaveReasonTitle(int $leave_reason):string {

		return self::LEAVE_REASON_TITLE[$leave_reason];
	}

	// обновляем последнее сообщение при скрытии сообщения
	// @long
	public static function updateLastMessageOnMessageHide(int $user_id, string $conversation_map, int $hidden_message_index, array $previous_message):array {

		Gateway_Db_CompanyConversation_Main::beginTransaction();
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new ReturnFatalException("Row in left menu not found");
		}

		// если скрыто не последнее сообщение то выходим
		$last_message_conversation_message_index = Gateway_Db_CompanyConversation_UserLeftMenu::getConversationMessageIndex($left_menu_row["last_message"]);
		if ($hidden_message_index < $last_message_conversation_message_index) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return $left_menu_row;
		}

		// если нет сообщения или оно скрыто пользователем отдаем пустоту
		if (count($previous_message) < 1 || Type_Conversation_Message_Main::getHandler($previous_message)::isMessageHiddenForUser($previous_message, $user_id)) {
			$last_message = Gateway_Db_CompanyConversation_UserLeftMenu::initHiddenLastMessage($hidden_message_index);
		} else {

			// формируем last_message
			$last_message = self::makeLastMessage($previous_message);
		}

		// обновляем последнее сообщение и последнее прочитанное в левом меню
		$set     = [
			"last_message"          => $last_message,
			"last_read_message_map" => $last_message["message_map"],
		];
		$version = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
		Gateway_Db_CompanyConversation_Main::commitTransaction();

		// возвращаем обновленную запись левого меню
		$left_menu_row["last_message"]          = $last_message;
		$left_menu_row["last_read_message_map"] = $last_message["message_map"];
		$left_menu_row["version"]               = $version;
		return $left_menu_row;
	}

	// формируем объект last_message
	// @long - большая структура сообщения
	public static function makeLastMessage(array $message):array {

		$current_conversation_message_index = self::_getCurrentMessageConversationIndex($message);
		$message_map                        = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);

		// формируем структуру сообщения
		$file_map        = self::_getLastMessageFileMap($message);
		$call_map        = self::_getLastMessageCallMap($message);
		$invite_map      = self::_getLastMessageInviteMap($message);
		$file_name       = self::_getLastMessageFileName($message);
		$message_count   = self::_getLastMessageMessageCountIfRepostOrQuote($message);
		$receiver_id     = self::_getLastMessageReceiverIdIfExistAdditional($message);
		$additional_type = self::_getLastMessageAdditionalType($message);

		$conference_id     = self::_getLastMessageConferenceId($message);
		$conference_accept_status = self::_getLastMessageConferenceAcceptStatus($message);

		return Gateway_Db_CompanyConversation_UserLeftMenu::initLastMessage(
			$message_map,
			Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message),
			$current_conversation_message_index,
			Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message),
			Type_Conversation_Message_Main::getHandler($message)::getType($message),
			Type_Conversation_Message_Main::getHandler($message)::getText($message),
			$file_map,
			$call_map,
			$invite_map,
			$file_name,
			$message_count,
			$receiver_id,
			$additional_type,
			$conference_id,
			$conference_accept_status
		);
	}

	// получаем conversation_message_index текущего сообщения
	protected static function _getCurrentMessageConversationIndex(array $message):int {

		$message_map = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);

		return \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);
	}

	// получаем file_map последнего сообщения
	protected static function _getLastMessageFileMap(array $message):string {

		$file_map = "";
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_FILE) {
			$file_map = Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);
		}
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_SYSTEM_BOT_FILE) {
			$file_map = Type_Conversation_Message_Main::getHandler($message)::getFileMap($message);
		}

		return $file_map;
	}

	// получаем call_map последнего сообщения
	protected static function _getLastMessageCallMap(array $message):string {

		$call_map = "";
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_CALL) {
			$call_map = Type_Conversation_Message_Main::getHandler($message)::getCallMap($message);
		}

		return $call_map;
	}

	// получаем conference_id последнего сообщения
	protected static function _getLastMessageConferenceId(array $message):string {

		$conference_id = "";
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE) {
			$conference_id = Type_Conversation_Message_Main::getHandler($message)::getConferenceId($message);
		}

		return $conference_id;
	}

	// получаем статус конференции последнего сообщения
	protected static function _getLastMessageConferenceAcceptStatus(array $message):string {

		$conference_accept_status = "";
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE) {
			$conference_accept_status = Type_Conversation_Message_Main::getHandler($message)::getConferenceAcceptStatus($message);
		}

		return $conference_accept_status;
	}

	/**
	 * Получаем invite_map для последнего сообщения.
	 *
	 * @throws \parseException
	 */
	protected static function _getLastMessageInviteMap(array $message):string {

		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_INVITE) {
			return Type_Conversation_Message_Main::getHandler($message)::getInviteMap($message);
		}

		return "";
	}

	// получаем file_name последнего сообщения
	protected static function _getLastMessageFileName(array $message):string {

		$file_name = "";
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_FILE) {
			$file_name = Type_Conversation_Message_Main::getHandler($message)::getFileName($message);
		}

		return $file_name;
	}

	// получаем количество репостнутых/процитированных сообщений последнего сообщения
	protected static function _getLastMessageMessageCountIfRepostOrQuote(array $message):int {

		$message_count = 0;

		if (Type_Conversation_Message_Main::getHandler($message)::isQuote($message) || Type_Conversation_Message_Main::getHandler($message)::isRepost($message)) {
			$message_count = Type_Conversation_Message_Main::getHandler($message)::getMessageCountIfRepostOrQuote($message);
		}

		return $message_count;
	}

	// получаем тип additional-полей в сообщении, если таковые имеются
	protected static function _getLastMessageAdditionalType(array $message):string {

		// если сообщение является респектом
		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalRespect($message)) {
			return Apiv1_Format::getAdditionalTypeName(Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_RESPECT);
		}

		// если сообщение является требовательностью
		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalExactingness($message)) {
			return Apiv1_Format::getAdditionalTypeName(Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_EXACTINGNESS);
		}

		// если сообщение является достижением
		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalAchievement($message)) {
			return Apiv1_Format::getAdditionalTypeName(Type_Conversation_Message_Handler_Default::ADDITIONAL_TYPE_ACHIEVEMENT);
		}

		return "";
	}

	// получаем id, если это сообщение с additional-полями
	protected static function _getLastMessageReceiverIdIfExistAdditional(array $message):int {

		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalRespect($message)) {
			return Type_Conversation_Message_Main::getHandler($message)::getAdditionalRespectReceiver($message);
		}

		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalExactingness($message)) {
			return Type_Conversation_Message_Main::getHandler($message)::getAdditionalExactingnessReceiver($message);
		}

		if (Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalAchievement($message)) {
			return Type_Conversation_Message_Main::getHandler($message)::getAdditionalAchievementReceiver($message);
		}

		return 0;
	}

	// получаем все записи left_menu пользователя
	public static function getAllLeftMenuList(int $user_id):array {

		// устанавливаем лимит для получений записей
		$limit = 50;

		// получаем количество диалогов левого меню
		$all_left_menu_count = Type_Conversation_LeftMenu::getUserLeftMenuCount($user_id);

		$left_menu_list = [];

		// получаем все записи left_menu пользователя
		$j = ceil((int) $all_left_menu_count / $limit);
		for ($i = 0; $i < $j; $i++) {

			$offset               = $i * $limit;
			$chunk_left_menu_list = Type_Conversation_LeftMenu::getByOffset($user_id, $limit, $offset);

			// мерджим полученную часть левого меню с тем, что получили ранее
			$left_menu_list = array_merge($left_menu_list, $chunk_left_menu_list);
		}

		return $left_menu_list;
	}

	/**
	 * получаем все диалоги левого меню, которые отмечены непрочитанными
	 *
	 */
	public static function getAllUnreadLeftMenuList(int $user_id, int $filter_favorites = 0):array {

		// устанавливаем лимит для получений записей
		$limit          = 100;
		$unread_filter  = 1;
		$filter_support = 1;

		$unread_left_menu_list = [];

		// получаем диалоги, где имеются непрочитанные
		$offset = 0;
		do {

			$chunk_left_menu_list = Gateway_Db_CompanyConversation_UserLeftMenu::getLeftMenuWithFlags(
				$user_id, $limit, $offset, "", $filter_favorites, $unread_filter, filter_support: $filter_support
			);

			$offset += $limit;

			// мерджим полученную часть левого меню с тем, что получили ранее
			$unread_left_menu_list = array_merge($unread_left_menu_list, $chunk_left_menu_list);
		} while (count($chunk_left_menu_list) > 0);

		return $unread_left_menu_list;
	}

	// метод возвращает одномерный массив с идентификаторами найденных собеседников
	public static function getOpponents(int $user_id, int $offset, int $limit, bool $is_need_return_blocked):array {

		if ($is_need_return_blocked) {
			return Gateway_Db_CompanyConversation_UserLeftMenu::getAllowAndBlockedOpponents($user_id, $offset, $limit);
		}
		return Gateway_Db_CompanyConversation_UserLeftMenu::getAllowOpponents($user_id, $offset, $limit);
	}

	// метод для поиска по имени собеседника
	// возвращает одномерный массив с идентификаторами найденных собеседников
	public static function getByOpponentUserName(array $conversation_map_list, int $user_id, bool $is_need_return_blocked):array {

		if ($is_need_return_blocked) {
			return Gateway_Db_CompanyConversation_UserLeftMenu::getAllowAndBlockedByConversationMapList($user_id, $conversation_map_list);
		}
		return Gateway_Db_CompanyConversation_UserLeftMenu::getAllowByConversationMapList($user_id, $conversation_map_list);
	}
}
