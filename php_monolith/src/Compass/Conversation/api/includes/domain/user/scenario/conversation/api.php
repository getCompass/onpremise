<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Request\ParamException;

/**
 * API-сценарии домена «диалоги».
 */
class Domain_User_Scenario_Conversation_Api {

	/**
	 * Ищет все по левому меню и потенциальных собеседников
	 *      — участники компании, с которыми он может общаться
	 *      — группы из левого меню пользователя
	 *
	 * @param int $user_id
	 * @param int $from_version
	 *
	 * @return array
	 *
	 * @throws ParamException
	 */
	public static function getLeftMenuDifference(int $user_id, int $from_version):array {

		// проверяем, что передали верную версию
		if ($from_version < 0) {
			throw new ParamException("passed incorrect version");
		}

		// запрашиваем измененные записи в левом меню
		$left_menu_list = Domain_User_Action_Conversation_GetVersionedLeftMenu::do($user_id, $from_version);

		// фильтруем левое меню от устарелых чатов
		$left_menu_list = Domain_Conversation_Entity_LegacyTypes::filterLeftMenu($left_menu_list);

		// получаем dynamic-данные диалогов
		$dynamic_list = Gateway_Db_CompanyConversation_ConversationDynamic::getAll(array_column($left_menu_list, "conversation_map"), true);

		return [$left_menu_list, $dynamic_list];
	}

	/**
	 * Получить мету левого меню
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	#[ArrayShape(["messages_unread_count" => "int", "conversations_unread_count" => "int", "left_menu_version" => "int"])]
	public static function getLeftMenuMeta(int $user_id):array {

		return Domain_User_Action_Conversation_GetLeftMenuMeta::do($user_id);
	}
}
