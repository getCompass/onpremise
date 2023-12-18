<?php

namespace Compass\Conversation;

/**
 * класс для получения данных для батчинга
 */
class Domain_Conversation_Feed_Action_GetDataForBatching {

	/**
	 * выполняем
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws \cs_DecryptHasFailed
	 * @long
	 */
	public static function run(int $user_id, array $batch_list, bool $is_restricted_access):array {

		$conversation_map_list = array_column($batch_list, "conversation_map");

		/** @var Struct_Db_CompanyConversation_ConversationMeta[] $meta_list */
		$meta_list = Gateway_Db_CompanyConversation_ConversationMeta::getAll($conversation_map_list, true);

		foreach ($meta_list as $index => $meta) {

			// если пространство неоплачено и чат не имеет тип "чат поддержки", выкидываем исключение
			if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType($meta->type)) {
				throw new Domain_Conversation_Exception_TariffUnpaid("tariff unpaid");
			}

			// если это диалог "Личный Heroes", то разрешаем всем
			// для всех остальных диалогов - если не участник, то является недоступным
			if (!Type_Conversation_Meta_Users::isMember($user_id, $meta->users) && !Type_Conversation_Meta::isSubtypeOfPublicGroup($meta->type)) {
				unset($meta_list[$index]);
			}
		}

		$dynamic_list = Gateway_Db_CompanyConversation_ConversationDynamic::getAll($conversation_map_list, true);

		$block_id_list_by_conversation_map = [];
		$action_users                      = [];
		$empty_conversation_map_list       = [];
		foreach ($batch_list as $v) {

			$conversation_map = $v["conversation_map"];
			$block_id_list    = $v["block_id_list"] ?? [];

			// если для чата не нашли ни мету, ни dynamic данные
			if (!isset($dynamic_list[$conversation_map]) || !isset($meta_list[$conversation_map])) {
				continue;
			}

			// если не передали блоки сообщений
			if (count($block_id_list) < 1) {

				// берём последние блоки сообщений
				$block_id_list = Domain_Conversation_Feed_Action_GetLastBlockIdList::run(
					$dynamic_list[$conversation_map], Domain_Conversation_Feed_Action_GetLastBlockIdList::MAX_GET_MESSAGES_BLOCK_COUNT_FOR_EMPTY_BLOCK_ID_LIST
				);
			}

			// фильтруем список блоков оставляем только актуальные
			$block_id_list = Domain_Conversation_Feed_Action_FilterBlockIdList::run($dynamic_list[$conversation_map], $block_id_list);

			// собираем пользователей для action users
			$action_users = array_merge($action_users, array_keys($meta_list[$conversation_map]->users));

			// если блоков сообщений нет - диалог пуст
			if (count($block_id_list) < 1) {

				$empty_conversation_map_list[] = $conversation_map;
				continue;
			}

			$block_id_list_by_conversation_map[$conversation_map] = $block_id_list;
		}

		return [$block_id_list_by_conversation_map, $empty_conversation_map_list, array_unique($action_users), $meta_list, $dynamic_list];
	}
}
