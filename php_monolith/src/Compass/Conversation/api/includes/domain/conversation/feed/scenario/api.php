<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * API-сценарии домена «диалоги».
 */
class Domain_Conversation_Feed_Scenario_Api {

	/**
	 * получаем список сообщений для отображения ленты диалога
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param array  $block_id_list
	 * @param bool   $is_restricted_access
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_UserIsNotMember
	 * @long
	 */
	public static function getMessages(int $user_id, string $conversation_map, array $block_id_list, bool $is_restricted_access):array {

		// проверяем, имеет ли пользователь доступ к диалогу
		$meta_row = Gateway_Db_CompanyConversation_ConversationMeta::getOne($conversation_map);

		// если пространство неоплачено и чат не имеет тип "чат поддержки", выкидываем исключение
		if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType($meta_row->type)) {
			throw new Domain_Conversation_Exception_TariffUnpaid("tariff unpaid");
		}

		// если это диалог "Личный Heroes", то разрешаем всем
		// для всех остальных диалогов история одна и та же
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row->users) && !Type_Conversation_Meta::isSubtypeOfPublicGroup($meta_row->type)) {
			throw new cs_UserIsNotMember();
		}

		$dynamic_row = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		// если не передали блоки сообщений
		if (count($block_id_list) < 1) {

			// берём последние блоки сообщений
			$block_id_list = Domain_Conversation_Feed_Action_GetLastBlockIdList::run(
				$dynamic_row, Domain_Conversation_Feed_Action_GetLastBlockIdList::MAX_GET_MESSAGES_BLOCK_COUNT_FOR_EMPTY_BLOCK_ID_LIST
			);
		}

		// фильтруем список блоков оставляем только актуальные
		$block_id_list = Domain_Conversation_Feed_Action_FilterBlockIdList::run($dynamic_row, $block_id_list);

		$users = array_keys($meta_row->users);
		if (count($block_id_list) < 1) {
			return [[], [], [], $users];
		}

		// получаем сообщения
		[$message_list, $message_users, $min_block_created_at] = Domain_Conversation_Feed_Action_GetMessages::run($user_id, $meta_row, $dynamic_row, $block_id_list);

		// получаем блоки вокруг наших
		[$next_block_id_list, $previous_block_id_list] = Domain_Conversation_Feed_Action_GetAroundBlockIdList::run(
			$user_id, $dynamic_row, $block_id_list, $min_block_created_at, Domain_Conversation_Feed_Action_GetLastBlockIdList::MAX_GET_MESSAGES_BLOCK_COUNT
		);

		// мерджим пользователей
		$users = array_merge($users, $message_users);

		return [$previous_block_id_list, $message_list, $next_block_id_list, $users];
	}

	/**
	 * получаем батчингом список сообщений для отображения ленты диалогов
	 *
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws ParseFatalException
	 * @long
	 */
	public static function getBatchingMessages(int $user_id, array $batch_message_list, bool $is_restricted_access):array {

		// получаем данные для батчинга
		[$block_id_list_by_conversation_map, $empty_conversation_map_list, $action_users, $meta_list, $dynamic_list] =
			Domain_Conversation_Feed_Action_GetDataForBatching::run($user_id, $batch_message_list, $is_restricted_access);

		$feed_message_list = [];
		foreach ($empty_conversation_map_list as $conversation_map) {
			$feed_message_list[] = (object) Apiv2_Format::feedMessages($conversation_map, [], [], [], []);
		}

		if (count($block_id_list_by_conversation_map) < 1) {
			return [$feed_message_list, []];
		}

		// получаем батчингом сообщения для выбранных диалогов
		[$message_list_by_conversation_map, $messages_users, $min_block_created_at_by_conversation_map, $hidden_message_map_list_by_conversation_map] =
			Domain_Conversation_Feed_Action_GetBatchingMessages::run(
				$user_id, $meta_list, $dynamic_list, $block_id_list_by_conversation_map
			);

		$feed_message_list = [];

		foreach ($block_id_list_by_conversation_map as $conversation_map => $block_id_list) {

			$min_block_created_at    = $min_block_created_at_by_conversation_map[$conversation_map];
			$dynamic                 = $dynamic_list[$conversation_map];
			$hidden_message_map_list = $hidden_message_map_list_by_conversation_map[$conversation_map] ?? [];
			$message_list            = $message_list_by_conversation_map[$conversation_map] ?? [];

			// получаем блоки вокруг наших
			[$next_block_id_list, $previous_block_id_list] = Domain_Conversation_Feed_Action_GetAroundBlockIdList::run(
				$user_id, $dynamic, $block_id_list, $min_block_created_at, Domain_Conversation_Feed_Action_GetLastBlockIdList::MAX_GET_MESSAGES_BLOCK_COUNT
			);

			$feed_message_list[] = (object) Apiv2_Format::feedMessages(
				$conversation_map, $message_list, $next_block_id_list, $previous_block_id_list, $hidden_message_map_list
			);
		}

		return [$feed_message_list, array_merge($action_users, $messages_users)];
	}

	/**
	 * Получаем список реакций для ленты подгрузки сообщений
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param array  $block_id_list
	 * @param bool   $is_restricted_access
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_UserIsNotMember
	 */
	public static function getReactions(int $user_id, string $conversation_map, array $block_id_list, bool $is_restricted_access):array {

		// проверяем, имеет ли пользователь доступ к диалогу
		$meta_row = Gateway_Db_CompanyConversation_ConversationMeta::getOne($conversation_map);

		if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType($meta_row->type)) {
			throw new Domain_Conversation_Exception_TariffUnpaid("tariff_unpaid");
		}

		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row->users)) {
			throw new cs_UserIsNotMember();
		}

		$dynamic_row = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		// если блоков не передали то берем последние
		if (count($block_id_list) < 1) {

			$block_id_list = Domain_Conversation_Feed_Action_GetLastBlockIdList::run(
				$dynamic_row, Domain_Conversation_Feed_Action_GetLastBlockIdList::MAX_GET_MESSAGES_BLOCK_COUNT
			);
		}

		// фильтруем список локов оставляем только актуальные
		$block_id_list = Domain_Conversation_Feed_Action_FilterBlockIdList::run($dynamic_row, $block_id_list);

		// получаем реакции
		$reaction_list = Domain_Conversation_Feed_Action_GetReactions::run($conversation_map, $block_id_list);

		$users = array_keys($meta_row->users);
		return [$reaction_list, $users];
	}

	/**
	 * Получаем батчингом список реакций для ленты подгрузки сообщений
	 *
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getBatchingReactions(int $user_id, array $batch_reaction_list, bool $is_restricted_access):array {

		// получаем данные для батчинга
		[$block_id_list_by_conversation_map, $empty_conversation_map_list, $action_users] = Domain_Conversation_Feed_Action_GetDataForBatching::run(
			$user_id, $batch_reaction_list, $is_restricted_access
		);

		$feed_reactions_list = [];
		foreach ($empty_conversation_map_list as $conversation_map) {
			$feed_reactions_list[] = Apiv2_Format::feedReactions($conversation_map, []);
		}

		if (count($block_id_list_by_conversation_map) < 1) {
			return [$feed_reactions_list, []];
		}

		// получаем батчингом реакции для выбранных диалогов
		$reaction_list_by_conversation_map = Domain_Conversation_Feed_Action_GetBatchingReactions::run($block_id_list_by_conversation_map);

		foreach ($reaction_list_by_conversation_map as $conversation_map => $reaction_list) {
			$feed_reactions_list[] = (object) Apiv2_Format::feedReactions($conversation_map, $reaction_list);
		}

		return [$feed_reactions_list, $action_users];
	}

	/**
	 * Получаем список тредов для ленты подгрузки сообщений
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param array  $block_id_list
	 * @param bool   $is_restricted_access
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_UserIsNotMember
	 */
	public static function getThreads(int $user_id, string $conversation_map, array $block_id_list, bool $is_restricted_access):array {

		// проверяем, имеет ли пользователь доступ к диалогу
		$meta_row = Gateway_Db_CompanyConversation_ConversationMeta::getOne($conversation_map);

		if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType($meta_row->type)) {
			throw new Domain_Conversation_Exception_TariffUnpaid("tariff_unpaid");
		}

		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row->users)) {
			throw new cs_UserIsNotMember();
		}

		$dynamic_row = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		// если блоков не передали то берем последние
		if (count($block_id_list) < 1) {

			$block_id_list = Domain_Conversation_Feed_Action_GetLastBlockIdList::run(
				$dynamic_row, Domain_Conversation_Feed_Action_GetLastBlockIdList::MAX_GET_MESSAGES_BLOCK_COUNT
			);
		}

		// фильтруем список локов оставляем только актуальные
		$block_id_list = Domain_Conversation_Feed_Action_FilterBlockIdList::run($dynamic_row, $block_id_list);

		// получаем треды
		[$thread_meta_list, $thread_menu_list, $thread_action_users] = Domain_Conversation_Feed_Action_GetThreads::run($user_id, $conversation_map, $block_id_list);

		// смержим action_users из meta_row с теми что вернулись из тредов
		$action_users = array_merge(array_keys($meta_row->users), $thread_action_users);

		return [$thread_meta_list, $thread_menu_list, $action_users];
	}

	/**
	 * Получаем батчингом список тредов для ленты подгрузки сообщений
	 *
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function getBatchingThreads(int $user_id, array $batch_thread_list, bool $is_restricted_access):array {

		// получаем данные для батчинга
		$action_users = [];
		[$block_id_list_by_conversation_map, $empty_conversation_map_list, $users, $meta_list, $dynamic_list] =
			Domain_Conversation_Feed_Action_GetDataForBatching::run($user_id, $batch_thread_list, $is_restricted_access);

		$feed_threads_list = [];
		foreach ($empty_conversation_map_list as $conversation_map) {
			$feed_threads_list[] = Apiv2_Format::feedThreads($conversation_map, [], []);
		}

		if (count($block_id_list_by_conversation_map) < 1) {
			return [$feed_threads_list, $action_users];
		}

		// получаем батчингом данные по тредам
		[$thread_meta_list_by_conversation_map, $thread_menu_list_by_conversation_map] = Domain_Conversation_Feed_Action_GetBatchingThreads::run(
			$user_id, $block_id_list_by_conversation_map, $dynamic_list, $meta_list
		);

		foreach ($thread_meta_list_by_conversation_map as $conversation_map => $thread_meta_list) {
			$feed_threads_list[] = Apiv2_Format::feedThreads($conversation_map, $thread_meta_list, $thread_menu_list_by_conversation_map[$conversation_map]);

			if (count($thread_meta_list) > 0) {
				$action_users = $users;
			}
		}

		return [$feed_threads_list, $action_users];
	}

	/**
	 * Читаем одно непрочитанное сообщение диалога
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @throws Domain_Conversation_Exception_Message_IsNotFromConversation
	 * @throws Domain_Conversation_Exception_Message_IsNotRead
	 * @throws Domain_Conversation_Exception_User_IsNotMember
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function readMessage(int $user_id, string $message_map):void {

		// проверяем что сообщение из диалога
		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new Domain_Conversation_Exception_Message_IsNotFromConversation("Message is not from conversation");
		}

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$left_menu_row    = Domain_Conversation_Feed_Action_ReadMessage::run($user_id, $conversation_map, $message_map);

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, (string) $user_id, [], $extra);

		// приводим левое меню к формату под клиентов
		$temp                   = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
		$prepared_left_menu_row = Apiv2_Format::leftMenu($temp);

		// получаем мету левого меню, откуда мы получаем количество непрочитанных чатов и сообщений
		$left_menu_meta = Domain_User_Action_Conversation_GetLeftMenuMeta::do($user_id);

		// отправляем ws ивент о прочтении
		Gateway_Bus_Sender::conversationRead($user_id, $message_map, $prepared_left_menu_row, $left_menu_meta);
	}
}
