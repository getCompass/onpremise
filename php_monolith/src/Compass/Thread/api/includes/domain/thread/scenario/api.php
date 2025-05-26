<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Сценарии треда для API
 */
class Domain_Thread_Scenario_Api {

	protected const _MAX_FAVORITE_COUNT = 100; // максимальное тредов в избранном

	/**
	 * Сценарий создания треда
	 *
	 * @param array $client_message_list
	 * @param mixed $parent_entity_id
	 * @param int   $parent_entity_type
	 * @param int   $user_id
	 * @param bool  $is_quote
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BlockException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_HiringRequestIsNotAllowedForAddThread
	 * @throws cs_MessageList_IsEmpty
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ParentMessage_IsRespect
	 * @throws cs_PlatformNotFound
	 * @throws cs_ThreadIsReadOnly
	 * @throws cs_Thread_ParentEntityNotFound
	 */
	public static function add(array $client_message_list, mixed $parent_entity_id, int $parent_entity_type, int $user_id, bool $is_quote):array {

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::THREADS_ADD, "threads", "row2");

		// проверяем и готовим к работе список сообщений
		$client_message_list = Type_Thread_Utils::parseRawMessageList($client_message_list, $is_quote);

		// проверяем наличие, если это заявки
		self::_checkIfExist($user_id, $parent_entity_type, $parent_entity_id);

		// закрепляем тред за родительской сущностью
		$meta_row = self::_addThreadToEntity($parent_entity_id, $parent_entity_type, $user_id);

		// если шлем цитату
		if ($is_quote) {

			// получаем упомянутых пользователей и прикрепляем их всех к треду
			$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $client_message_list[0]["text"]);
			Helper_Threads::attachUsersToThread($meta_row, $mention_user_id_list);

			// цитируем
			return self::_addQuoteMessage(
				$meta_row,
				[],
				$client_message_list[0]["client_message_id"],
				$client_message_list[0]["text"],
				$mention_user_id_list,
				$user_id,
				true
			);
		}

		// добавляем сообщения
		return self::_addMessageList($meta_row["thread_map"], $meta_row, $client_message_list, $user_id);
	}

	/**
	 * выполняем цитирование массива сообощений
	 *
	 * @param array  $meta_row
	 * @param array  $message_map_list
	 * @param string $client_message_id
	 * @param string $text
	 * @param array  $mention_user_id_list
	 * @param int    $user_id
	 * @param bool   $is_attach_parent
	 *
	 * @return array
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_MessageList_IsEmpty
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ParentMessage_IsRespect
	 * @throws cs_PlatformNotFound
	 * @throws cs_ThreadIsReadOnly
	 */
	protected static function _addQuoteMessage(array $meta_row, array $message_map_list, string $client_message_id, string $text, array $mention_user_id_list, int $user_id, bool $is_attach_parent):array {

		$parent_message = Type_Thread_Rel_Parent::getParentMessageIfNeed($user_id, $meta_row, $is_attach_parent);

		$data_list = Helper_Threads::addQuoteV2(
			$meta_row["thread_map"],
			$meta_row,
			$message_map_list,
			$client_message_id,
			$user_id,
			$text,
			$mention_user_id_list,
			$parent_message,
			Type_Api_Platform::getPlatform()
		);

		// подводим под формат и отдаем
		$prepared_message_list = [];
		foreach ($data_list["message_list"] as $message) {

			$prepared_message        = Type_Thread_Message_Main::getHandler($message)::prepareForFormat($message);
			$prepared_message_list[] = Apiv1_Format::threadMessage($prepared_message);
		}

		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($data_list["meta_row"], $user_id);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::THREAD_MESSAGE, $user_id, count($prepared_message_list));
		Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_THREAD_MESSAGE);

		return [
			$prepared_message_list,
			Apiv1_Format::threadMeta($prepared_thread_meta),
		];
	}

	/**
	 * если тред доступен для отправки, то отправляем список сообщений
	 *
	 * @param string $thread_map
	 * @param array  $meta_row
	 * @param array  $client_message_list
	 * @param int    $user_id
	 *
	 * @return array
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_PlatformNotFound
	 * @throws cs_ThreadIsReadOnly
	 */
	protected static function _addMessageList(string $thread_map, array $meta_row, array $client_message_list, int $user_id):array {

		$raw_message_list = self::_generateRawMessageList($client_message_list, $meta_row, $user_id);

		// создаем сообщения и проверяем, может ли пользователь писать в тред
		$data = Domain_Thread_Action_Message_AddList::do($thread_map, $meta_row, $raw_message_list);

		// готовим для формата мету треда
		$prepared_thread_meta  = Type_Thread_Utils::prepareThreadMetaForFormat($data["meta_row"], $user_id);
		$prepared_message_list = [];

		// приводим под формат список сообщений треда
		foreach ($data["message_list"] as $v) {

			$prepared_message        = Type_Thread_Message_Main::getHandler($v)::prepareForFormat($v);
			$prepared_message_list[] = (object) Apiv1_Format::threadMessage($prepared_message);
		}

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::THREAD_MESSAGE, $user_id, count($prepared_message_list));

		return [
			$prepared_message_list,
			Apiv1_Format::threadMeta($prepared_thread_meta),
		];
	}

	/**
	 * создаем массив сообщений-заготовок перед созданием записей в базу
	 *
	 * @param array $client_message_list
	 * @param array $meta_row
	 * @param int   $user_id
	 *
	 * @return array
	 * @throws \parseException
	 * @throws cs_PlatformNotFound
	 */
	protected static function _generateRawMessageList(array $client_message_list, array $meta_row, int $user_id):array {

		$raw_message_list = [];
		$mentioned_users  = [];

		// оставшиеся сообщения имеют тип текст
		foreach ($client_message_list as $v) {

			// получаем упомянутых пользователей
			$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $v["text"]);
			$mentioned_users[]    = $mention_user_id_list;

			$handler_class = Type_Thread_Message_Main::getLastVersionHandler();
			$platform      = Type_Api_Platform::getPlatform();

			// если передали файл, то создаем сообщение типа "файл", иначе - тип "текст"
			if ($v["file_map"] !== false) {
				$message = $handler_class::makeFile($user_id, $v["text"], $v["client_message_id"], $v["file_map"], $v["file_name"], $platform);
			} else {
				$message = $handler_class::makeText($user_id, $v["text"], $v["client_message_id"], [], $platform);
			}

			// добавляем список упомянутых к сообщению
			$raw_message_list[] = Type_Thread_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}

		// сделано так чтобы разом подписать всех :)
		$mentioned_users = array_merge(...$mentioned_users);
		if ($mentioned_users) {
			Helper_Threads::attachUsersToThread($meta_row, $mentioned_users);
		}

		return $raw_message_list;
	}

	/**
	 * Сценарий получения меню все комментарии
	 *
	 * @param int $offset
	 * @param int $count
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \paramException
	 */
	public static function getMenu(int $offset, int $count, int $user_id):array {

		Domain_Thread_Entity_Validator::assertOffset($offset);
		$count          = Domain_Thread_Entity_Validator::getMaxThreadCount($count);
		$favorite_count = Type_Thread_Menu::getFavoriteCount($user_id);

		// проверяем доступность полученных итемов меню тредов для пользователя
		[$allowed_thread_menu_list, $has_next] = Domain_Thread_Entity_ThreadMenu::getMenu($user_id, $count, $offset);

		[$thread_menu, $thread_list_data] = self::_prepareThreadMenu($allowed_thread_menu_list);
		return [$thread_menu, $thread_list_data, $favorite_count, $has_next];
	}

	/**
	 * Сценарий получения меню все комментарии
	 *
	 * @param int $user_id
	 * @param int $count
	 * @param int $offset
	 * @param int $filter_favorite
	 * @param int $filter_unread
	 *
	 * @return array
	 * @throws \paramException
	 */
	public static function getMenuV2(int $user_id, int $count, int $offset, int $filter_favorite, int $filter_unread):array {

		Domain_Thread_Entity_Validator::assertOffset($offset);
		Domain_Thread_Entity_Validator::assertFilter($filter_favorite);
		Domain_Thread_Entity_Validator::assertFilter($filter_unread);
		$count = Domain_Thread_Entity_Validator::getMaxThreadCount($count);

		$allowed_thread_menu_list = [];
		$has_next                 = [];
		$favorite_count           = Type_Thread_Menu::getFavoriteCount($user_id);

		// отдаем непрочитанные
		if ($filter_favorite == 0 && $filter_unread == 1) {
			[$allowed_thread_menu_list, $has_next] = Domain_Thread_Entity_ThreadMenu::getUnread($user_id, $count, $offset);
		}

		// отдаем просто все треды
		if ($filter_favorite == 0 && $filter_unread == 0) {
			[$allowed_thread_menu_list, $has_next] = Domain_Thread_Entity_ThreadMenu::getMenu($user_id, $count, $offset);
		}

		// отдаем треды в избранном, не важно прочитанные или нет
		if ($filter_favorite == 1) {
			[$allowed_thread_menu_list, $has_next] = Domain_Thread_Entity_ThreadMenu::getFavorite($user_id, $count, $offset, $filter_favorite);
		}

		[$thread_menu, $thread_list_data] = self::_prepareThreadMenu($allowed_thread_menu_list);
		return [$thread_menu, $thread_list_data, $favorite_count, $has_next];
	}

	/**
	 * Сценарий получения непрочитанных ответов
	 *
	 * @param int $offset
	 * @param int $count
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \paramException
	 */
	public static function getUnreadMenu(int $offset, int $count, int $user_id):array {

		Domain_Thread_Entity_Validator::assertOffset($offset);
		$count = Domain_Thread_Entity_Validator::getMaxThreadCount($count);

		// проверяем доступность полученных итемов меню тредов для пользователя
		[$allowed_thread_menu_list, $has_next] = Domain_Thread_Entity_ThreadMenu::getUnread($user_id, $count, $offset);

		[$thread_menu, $thread_list_data] = self::_prepareThreadMenu($allowed_thread_menu_list);
		return [$thread_menu, $thread_list_data, $has_next];
	}

	/**
	 * подготоваливаем тред меню к ответу
	 *
	 * @param array $allowed_thread_menu_list
	 *
	 * @return array
	 */
	protected static function _prepareThreadMenu(array $allowed_thread_menu_list):array {

		// формируем массивы thread_menu и thread_key_list
		$thread_menu     = [];
		$thread_key_list = [];
		foreach ($allowed_thread_menu_list as $item) {

			// форматируем сущность thread_menu
			$prepared_thread_menu = Type_Thread_Utils::prepareThreadMenuForFormat($item);
			$thread_menu[]        = Apiv1_Format::threadMenu($prepared_thread_menu);
			$thread_key_list[]    = \CompassApp\Pack\Thread::doEncrypt($item["thread_map"]);
		}

		// собираем отдельно ключи и сигнатуру, требуется для клиентов
		$thread_list_data = [
			"thread_key_list" => (array) $thread_key_list,
			"signature"       => (string) Type_Thread_Utils::getSignatureWithCustomSalt($thread_key_list, time(), SALT_THREAD_LIST),
		];

		return [$thread_menu, $thread_list_data];
	}

	/**
	 * метод для получения thread_meta и thread_menu запрошенных тредов
	 *
	 * @param int   $user_id
	 * @param array $thread_map_list
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function getMetaAndMenuBatching(int $user_id, array $thread_map_list):array {

		// пробуем получить данные о метах тредов
		$data = Helper_Threads::getMetaListIfUserMember($thread_map_list, $user_id);

		$meta_list                  = $data["allowed_meta_list"];
		$not_access_thread_map_list = $data["not_allowed_thread_map_list"];

		// отправляем задачу на отписывание от тредов
		Type_Phphooker_Main::doUnfollowThreadList($not_access_thread_map_list, $user_id);

		// получаем только доступные треды
		$allowed_thread_map_list = array_column($meta_list, "thread_map");

		$dynamic_list = Type_Thread_Dynamic::getList($allowed_thread_map_list);

		// получаем конкретные записи из меню, игнорируя скрытые
		$menu_list = Type_Thread_Menu::getMenuItems($user_id, $allowed_thread_map_list);

		// формируем ответ
		[$frontend_thread_meta_list, $action_user_id_list] = self::_makeGetMetaBatchingOutput($user_id, $meta_list, $dynamic_list);

		$frontend_thread_menu_list = [];
		foreach ($menu_list as $item) {

			// форматируем сущность thread_menu
			$prepared_thread_menu        = Type_Thread_Utils::prepareThreadMenuForFormat($item);
			$frontend_thread_menu_list[] = Apiv1_Format::threadMenu($prepared_thread_menu);
		}

		return [$frontend_thread_meta_list, $frontend_thread_menu_list, $action_user_id_list];
	}

	/**
	 * метод для формироывния ответа для списка мет тредов
	 *
	 * @param int                                     $user_id
	 * @param array                                   $thread_meta_list
	 * @param Struct_Db_CompanyThread_ThreadDynamic[] $dynamic_list
	 *
	 * @return array
	 * @throws \parseException
	 */
	protected static function _makeGetMetaBatchingOutput(int $user_id, array $thread_meta_list):array {

		$prepared_meta_row_list = [];
		$action_user_id_list    = [];
		foreach ($thread_meta_list as $item) {

			// приводим сущность threads под формат frontend
			$prepared_meta_row        = Type_Thread_Utils::prepareThreadMetaForFormat($item, $user_id);
			$prepared_meta_row_list[] = Apiv1_Format::threadMeta($prepared_meta_row);

			// добавляем пользователей в actions users
			$action_user_id_list = array_merge($action_user_id_list, Type_Thread_Meta::getActionUsersList($item));
		}

		return [$prepared_meta_row_list, $action_user_id_list];
	}

	/**
	 * Пометить thread непрочитанным
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param string $previous_message_map
	 *
	 * @long
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 */
	public static function setAsUnread(int $user_id, string $thread_map, string $previous_message_map):void {

		// получаем запись из follower_list
		$follower_row = Type_Thread_Followers::get($thread_map);

		// если пользователь НЕ участник треда, то подписываем
		if (!Type_Thread_Followers::isFollowUser($user_id, $follower_row)) {

			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
			Domain_Thread_Action_Follower_Follow::do([$user_id], $thread_map, $meta_row["parent_rel"]);
		} else {
			$meta_row = Type_Thread_Meta::getOne($thread_map);
		}

		$parent_entity_type = Type_Thread_ParentRel::getType($meta_row["parent_rel"]);
		if (in_array($parent_entity_type, [PARENT_ENTITY_TYPE_HIRING_REQUEST, PARENT_ENTITY_TYPE_DISMISSAL_REQUEST])) {

			// получаем количество непрочитанных
			$total_unread_count      = Domain_Thread_Action_GetTotalUnreadCount::do($user_id);
			$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
			$threads_updated_version = Gateway_Socket_Conversation::updateThreadsUpdatedData($parent_conversation_map);

			// отправляем ивент
			Gateway_Bus_Sender::threadMarkedAsUnread($user_id, $thread_map, $parent_conversation_map, $total_unread_count, $threads_updated_version);

			return;
		}

		try {
			Domain_Thread_Action_SetAsUnread::do($user_id, $thread_map, $previous_message_map);
		} catch (\cs_RowIsEmpty) { // если вдруг не нашли запись меню тредов для пользователя, то повторяем

			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
			Domain_Thread_Action_Follower_Follow::do([$user_id], $thread_map, $meta_row["parent_rel"]);
			Domain_Thread_Action_SetAsUnread::do($user_id, $thread_map, $previous_message_map);
		}

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// получаем количество непрочитанных
		$total_unread_count = Domain_Thread_Action_GetTotalUnreadCount::do($user_id);

		// если до этого так и не получили мету
		if (count($meta_row) < 1) {
			$meta_row = Type_Thread_Meta::getOne($thread_map);
		}
		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
		$threads_updated_version = Gateway_Socket_Conversation::updateThreadsUpdatedData($parent_conversation_map);

		// отправляем ивент
		Gateway_Bus_Sender::threadMarkedAsUnread($user_id, $thread_map, $parent_conversation_map, $total_unread_count, $threads_updated_version);
	}

	/**
	 * Прочитать тред
	 *
	 * @param int    $user_id
	 * @param int    $member_role
	 * @param int    $member_permissions
	 * @param string $message_map
	 * @param string $local_date
	 * @param string $local_time
	 *
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function doRead(int $user_id, int $member_role, int $member_permissions, string $message_map, string $local_date, string $local_time):void {

		// распаковываем message_map и получаем thread_map
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

		// помечаем тред прочтенным
		$was_unread = Domain_Thread_Action_DoRead::do($user_id, $member_role, $member_permissions, $thread_map, $message_map);

		// если пользователь не существует - отдаем количество непрочитанных = 0
		$total_unread_count = Domain_Thread_Action_GetTotalUnreadCount::do($user_id);

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$thread_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// обновляем threads_updated_at & threads_updated_version
		$meta_row                = Type_Thread_Meta::getOne($thread_map);
		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);

		// добавляем пользователю экранное время
		Domain_User_Action_AddScreenTime::do($user_id, $local_date, $local_time);

		// инкрементим статистику если был непрочитанным
		if ($was_unread) {
			$threads_updated_version = Gateway_Socket_Conversation::updateThreadsUpdatedData($parent_conversation_map);
		} else {
			$threads_updated_version = Gateway_Socket_Conversation::getThreadsUpdatedVersion($parent_conversation_map);
		}

		// отправляем ивент
		Gateway_Bus_Sender::threadRead($user_id, $thread_map, $message_map, $parent_conversation_map, $total_unread_count, $threads_updated_version);

	}

	/**
	 * Метод для общего количества непрочитанных сообщений
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	#[ArrayShape(["threads_unread_count" => "int", "messages_unread_count" => "int"])]
	public static function getTotalUnreadCount(int $user_id):array {

		// получаем количество непрочитанных для пользователя
		$user_dynamic_row = Domain_Thread_Action_GetTotalUnreadCount::do($user_id);

		return [
			"threads_unread_count"  => (int) ($user_dynamic_row["threads_unread_count"] < 0 ? 0 : $user_dynamic_row["threads_unread_count"]),
			"messages_unread_count" => (int) ($user_dynamic_row["messages_unread_count"] < 0 ? 0 : $user_dynamic_row["messages_unread_count"]),
		];
	}

	/**
	 * Добавляем реакцию к сообщению
	 *
	 * @throws CaseException
	 * @throws Domain_Group_Exception_NotEnoughRights
	 * @throws ParamException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 */
	public static function addReaction(string $message_map, string $reaction_name, int $user_id, int $method_version):void {

		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new ParamException("the message is not from thread");
		}

		$reaction_name = Type_Thread_Reaction_Main::getReactionNameIfExist($reaction_name);
		if (mb_strlen($reaction_name) < 1) {
			throw new ParamException(__CLASS__ . ": reaction does not exist");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$meta_row   = Helper_Threads::getMetaIfUserMember($thread_map, $user_id, false);

		if ($method_version >= 2) {
			Domain_Group_Entity_Options::checkReactionRestrictionByThreadMeta($user_id, $meta_row);
		}
		Domain_Thread_Action_Message_AddReaction::do($message_map, $meta_row["thread_map"], $meta_row, $reaction_name, $user_id);
	}

	/**
	 * Удаляем реакцию с сообщения
	 *
	 * @throws CaseException
	 * @throws Domain_Group_Exception_NotEnoughRights
	 * @throws ParamException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 */
	public static function removeReaction(string $message_map, string $reaction_name, int $user_id, int $method_version):void {

		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new ParamException("the message is not from thread");
		}
		$reaction_name = Type_Thread_Reaction_Main::getReactionNameIfExist($reaction_name);
		if (mb_strlen($reaction_name) < 1) {
			throw new ParamException(__CLASS__ . ": reaction does not exist");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$meta_row   = Helper_Threads::getMetaIfUserMember($thread_map, $user_id, false);

		if ($method_version >= 2) {
			Domain_Group_Entity_Options::checkReactionRestrictionByThreadMeta($user_id, $meta_row);
		}
		Domain_Thread_Action_Message_RemoveReaction::do($message_map, $meta_row["thread_map"], $reaction_name, $user_id, $meta_row["users"]);
	}

	/**
	 * Добавляем тред в избранное
	 *
	 * @param string $thread_map
	 * @param int    $user_id
	 *
	 * @throws ParamException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_ToManyInFavorite
	 * @throws cs_Thread_UserNotMember
	 */
	public static function addToFavorite(string $thread_map, int $user_id):void {

		// проверяем, что пользователь участник треда
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		// проверяем, превышено ли количество избранных диалогов
		$favorite_count = Type_Thread_Menu::getFavoriteCount($user_id);
		if ($favorite_count >= self::_MAX_FAVORITE_COUNT) {
			throw new cs_Thread_ToManyInFavorite();
		}

		// подписываем пользователя на тред если необходимо
		$follower_row = Type_Thread_Followers::get($thread_map);
		if (!Type_Thread_Followers::isFollowUser($user_id, $follower_row)) {

			Type_Thread_Followers::doFollowUserList([$user_id], $thread_map);
			Type_Thread_Menu::setFollowUserList([$user_id], $thread_map, $meta_row["parent_rel"]);

			Gateway_Bus_Sender::threadFollow($user_id, $thread_map);
		}

		// добавляем тред в избранное
		Domain_Thread_Action_AddToFavorite::do($user_id, $thread_map);
	}

	/**
	 * Убираем тред из избранного
	 *
	 * @param string $thread_map
	 * @param int    $user_id
	 *
	 * @throws ParamException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 */
	public static function removeFromFavorite(string $thread_map, int $user_id):void {

		// проверяем, что пользователь участник треда
		try {
			Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
		} catch (cs_Conversation_IsBlockedOrDisabled) {
			// пропускаем, это никак не мешает убрать тред из избранного
		}

		// убираем тред из избранного
		Domain_Thread_Action_RemoveFromFavorite::do($user_id, $thread_map);
	}

	/**
	 * Подписываемся на тред
	 *
	 * @param int              $user_id
	 * @param string|false     $thread_key
	 * @param int|false        $parent_entity_type
	 * @param int|string|false $parent_entity_id
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_HiringRequestIsNotAllowedForAddThread
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_ThreadIsReadOnly
	 * @throws cs_Thread_UserNotMember
	 */
	public static function follow(int $user_id, string|false $thread_key, int|false $parent_entity_type, int|string|false $parent_entity_id):void {

		// если треда не существует - создаем
		if ($thread_key === false) {

			// закрепляем тред за родительской сущностью
			$meta_row   = self::_addThreadToEntity($parent_entity_id, $parent_entity_type, $user_id, true);
			$thread_map = $meta_row["thread_map"];
		} else {
			$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);
		}

		// проверяем, что пользователь участник треда
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow();
		}

		// подписываем пользователя на тред
		Domain_Thread_Action_Follower_Follow::do([$user_id], $thread_map, $meta_row["parent_rel"]);

		// отправляем ws событие о подписке пользователя на тред
		Gateway_Bus_Sender::threadFollow($user_id, $thread_map);

		// не отправляем в тред системное сообщение, если не передан заголовок = 1 и передан thread_key
		if (!Type_System_Legacy::isFollowThreadWithSystemMessage() && $thread_key !== false) {
			return;
		}

		$message = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemMessageFollowThread($user_id);
		Domain_Thread_Action_Message_AddList::do($thread_map, $meta_row, [$message]);
	}

	/**
	 * Отписываемся от треда
	 *
	 * @param int              $user_id
	 * @param string|false     $thread_key
	 * @param int|false        $parent_entity_type
	 * @param int|string|false $parent_entity_id
	 *
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 */
	public static function unfollow(int $user_id, string|false $thread_key, int|false $parent_entity_type, int|string|false $parent_entity_id):void {

		// если треда не существует - создаем
		$is_need_hide = false;
		if ($thread_key === false) {

			// закрепляем тред за родительской сущностью
			$meta_row     = self::_addThreadToEntity($parent_entity_id, $parent_entity_type, $user_id, true);
			$thread_map   = $meta_row["thread_map"];
			$is_need_hide = true;
		} else {
			$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);
		}

		// проверяем, что пользователь участник треда
		try {
			Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
		} catch (cs_Conversation_IsBlockedOrDisabled) {
			// не мешаем пользователю отписаться от треда
		}

		// отписываем пользователя от треда
		Domain_Thread_Action_Follower_Unfollow::do($user_id, $thread_map, $is_need_hide);
	}

	/**
	 * помечаем прочитанными все треды пользователя
	 *
	 * @param int    $user_id
	 * @param string $local_date
	 * @param string $local_time
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function doReadAll(int $user_id, string $local_date, string $local_time):void {

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::ALL_THREADS_MESSAGES_READ);

		// получаем треды, где имеются непрочитанные сообщения
		$thread_menu_list = Type_Thread_Menu::getAllUnreadList($user_id);

		// собираем ключи непрочитанных тредов
		$thread_map_list = array_column($thread_menu_list, "thread_map");

		// прочитываем треды
		Type_Thread_Menu::setThreadsAsRead($user_id, $thread_map_list);

		// обнуляем total_unread_count непрочитанных сообщений
		Type_Thread_Menu::nullifyTotalUnread($user_id);

		// отправляем запрос для обновления badge_count и удаления пушей прочитанных сообщений
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, $thread_map_list, true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra, 0);

		// отправляем пользователю ws-событие о прочтении всех сообщений в компании
		Gateway_Bus_Sender::threadsMessagesReadAll($user_id);

		// добавляем пользователю экранное время
		Domain_User_Action_AddScreenTime::do($user_id, $local_date, $local_time);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * прикрепляем тред нужным способом к сущности
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_HiringRequestIsNotAllowedForAddThread
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Thread_ParentEntityNotFound
	 */
	protected static function _addThreadToEntity(mixed $parent_entity_id, int $parent_entity_type, int $user_id, bool $is_thread_hidden_for_all_users = false):array {

		try {

			$result = match ($parent_entity_type) {

				// создаем тред у сообщения диалога
				PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE => Domain_Thread_Action_AddToConversationMessage::do($user_id, $parent_entity_id, $is_thread_hidden_for_all_users),

				// создаем тред у заявки найма/увольнения
				PARENT_ENTITY_TYPE_HIRING_REQUEST       => Domain_Thread_Action_AddToHiringRequest::do($user_id, $parent_entity_id, true),
				PARENT_ENTITY_TYPE_DISMISSAL_REQUEST    => Domain_Thread_Action_AddToDismissalRequest::do($user_id, $parent_entity_id, true),

				default                                 => throw new ParamException(__METHOD__ . " Type not found"),
			};
		} catch (Domain_Thread_Exception_Guest_AttemptInitialThread) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "guest attempt to initial thread");
		}

		return $result;
	}

	/**
	 * проверяем сущность
	 *
	 * @param int   $user_id
	 * @param int   $parent_entity_type
	 * @param mixed $parent_entity_id
	 *
	 * @throws ParamException
	 */
	protected static function _checkIfExist(int $user_id, int $parent_entity_type, mixed $parent_entity_id):void {

		switch ($parent_entity_type) {

			case PARENT_ENTITY_TYPE_HIRING_REQUEST:
			case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:

				self::_tryCheckIfExistHiringOrDismissalRequest($user_id, $parent_entity_type, $parent_entity_id);
				break;

			default:
				break;
		}
	}

	/**
	 * проверяем заявку найма или увольнения на существование
	 *
	 * @param int   $user_id
	 * @param int   $parent_entity_type
	 * @param mixed $parent_entity_id
	 *
	 * @throws ParamException
	 */
	protected static function _tryCheckIfExistHiringOrDismissalRequest(int $user_id, int $parent_entity_type, mixed $parent_entity_id):void {

		$parent_name_type = Apiv1_Format::parentType($parent_entity_type);

		$ar_post = [
			"request_type" => $parent_name_type,
			"request_id"   => $parent_entity_id,
		];

		[$status] = Gateway_Socket_Company::doCall("hiring.hiringrequest.getRequestData", $ar_post, $user_id);

		if ($status != "ok") {
			throw new ParamException (__METHOD__ . ": request not exist");
		}
	}
}