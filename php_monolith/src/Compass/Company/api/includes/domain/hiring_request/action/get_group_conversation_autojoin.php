<?php

namespace Compass\Company;

/**
 * Базовый класс для получения списка групповых диалогов заявки для автоподключения
 */
class Domain_HiringRequest_Action_GetGroupConversationAutojoin {

	/**
	 * Выполняем action
	 *
	 * @throws \returnException
	 */
	public static function do(Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		$conversation_key_list_to_join = Domain_HiringRequest_Entity_Request::getConversationKeyListToJoin($hiring_request->extra);

		$conversation_key_list = [];
		foreach ($conversation_key_list_to_join as $v) {
			$conversation_key_list[] = $v["conversation_key"];
		}

		$join_list = Gateway_Socket_Conversation::getConversationInfoList($conversation_key_list);
		foreach ($join_list as $key => $join_group) {

			if ($join_group->member_count === 0) {
				unset($join_list[$key]);
			}
		}

		return $join_list;
	}

	/**
	 * получаем группы для вступления по ключам
	 *
	 * @throws \returnException
	 */
	public static function doByKeys(array $conversation_key_list):array {

		// фильтруем только те группы для вступления, у которых имеются участники
		$join_list = Gateway_Socket_Conversation::getConversationInfoList($conversation_key_list);
		foreach ($join_list as $key => $join_group) {

			if ($join_group->member_count === 0) {
				unset($join_list[$key]);
			}
		}

		return $join_list;
	}
}