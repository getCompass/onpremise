<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;

/**
 * Action для получения общих групп с пользователем
 */
class Domain_Conversation_Action_GetMeta {

	/**
	 * Поулчить общие группы с пользователем
	 *
	 * @param string $conversation_map
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \cs_DecryptHasFailed
	 */
	public static function do(string $conversation_map):array {

		try {
			return Gateway_Db_CompanyConversation_ConversationMetaLegacy::getOne($conversation_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParamException("try get meta from incorrect conversation_map");
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("conversation not found");
		}
	}
}