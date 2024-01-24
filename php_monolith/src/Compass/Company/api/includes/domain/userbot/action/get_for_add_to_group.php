<?php

namespace Compass\Company;

/**
 * Класс action получения ботов для добавления их в группу
 */
class Domain_Userbot_Action_GetForAddToGroup {

	/**
	 * выполняем действие
	 *
	 * @param Struct_Db_CloudCompany_Userbot[] $userbot_list
	 *
	 * @return array
	 * @throws Domain_Userbot_Exception_IncorrectStatus
	 * @throws \cs_DecryptHasFailed
	 * @long - длинный action
	 */
	public static function do(array $userbot_list, string $conversation_key):array {

		$userbot_id_list_by_user_id = [];
		$disabled_userbot_id_list   = [];
		$deleted_userbot_id_list    = [];
		foreach ($userbot_list as $userbot) {

			// если бот выключен
			if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DISABLE) {

				$disabled_userbot_id_list[] = $userbot->userbot_id;
				continue;
			}

			// если бот удалён
			if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {

				$deleted_userbot_id_list[] = $userbot->userbot_id;
				continue;
			}

			$userbot_id_list_by_user_id[$userbot->user_id] = $userbot->userbot_id;
		}

		// если имеются отключённые или удалённые боты
		if (count($disabled_userbot_id_list) > 0 || count($deleted_userbot_id_list) > 0) {

			throw new Domain_Userbot_Exception_IncorrectStatus("userbot have incorrect status", [
				"disabled_userbot_id_list" => $disabled_userbot_id_list,
				"deleted_userbot_id_list"  => $deleted_userbot_id_list,
			]);
		}

		// проверяем, можем кто-то из ботов уже содержится в группе
		$check_userbot_id_list         = array_values($userbot_id_list_by_user_id);
		$conversation_map              = Type_Pack_Conversation::doDecrypt($conversation_key);
		$userbot_conversation_rel_list = Gateway_Db_CompanyData_UserbotConversationRel::getListByConversationMap($check_userbot_id_list, $conversation_map);
		$already_userbot_id_list       = array_column($userbot_conversation_rel_list, "userbot_id");

		// получаем только тех ботов, кого нужно добавить в группу
		return array_diff($userbot_id_list_by_user_id, $already_userbot_id_list);
	}
}