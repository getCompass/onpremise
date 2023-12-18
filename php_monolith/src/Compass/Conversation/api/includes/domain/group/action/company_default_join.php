<?php

namespace Compass\Conversation;

/**
 * Action для добавления пользователя в дефолтные группы компании
 */
class Domain_Group_Action_CompanyDefaultJoin {

	/**
	 * добавляем пользователя в дефолтные группы компании
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(int $user_id, int $role, bool $is_owner = false):void {

		// получаем список дефолт групп для пользователя
		$default_group_list = Domain_Group_Entity_Company::getDefaultGroupKeyList($is_owner);

		// получаем специальные настройки для вступления
		$is_add_to_general_chat = Domain_Company_Action_Config_GetAddToGeneralChatOnHiring::do() === 1;

		// добавляем пользователя в каждую группу из списка
		foreach ($default_group_list as $group_key_name) {

			// пропускаем, если это Главный чат и выключена настройка добавления пользователя в Главный чат при вступлении
			if ($group_key_name === Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME && $is_add_to_general_chat === false) {
				continue;
			}

			// если группа нужна в избранном
			$is_need_favorite = Domain_Group_Entity_Company::isNeedToFavorite($group_key_name);

			try {
				self::_try($user_id, $role, $group_key_name, $is_need_favorite);
			} catch (\cs_RowIsEmpty) {
				return;
			}
		}
	}

	/**
	 * пробуем добавить в указанную дефолтную группу
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _try(int $user_id, int $role, string $group_key_name, bool $is_need_favorite):void {

		// пробуем получить ключ дефолтной группы
		$conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey($group_key_name);
		if ($conversation_map === "") {
			return;
		}

		Helper_Groups::doJoin($conversation_map, $user_id, role: $role, is_favorite: $is_need_favorite);
	}
}
