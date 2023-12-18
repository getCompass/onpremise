<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Action прочтения диалогов
 */
class Domain_Conversation_Action_DoReadAll {

	/**
	 *  выполняем действие по прочтению всех диалогов
	 *
	 * @param int    $user_id
	 * @param string $local_date
	 * @param string $local_time
	 * @param int    $filter_favorites
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $local_date, string $local_time, int $filter_favorites = 0):void {

		// получаем диалоги, где имеются непрочитанные сообщения
		$left_menu_list = Type_Conversation_LeftMenu::getAllUnreadLeftMenuList($user_id, $filter_favorites);

		// собираем ключи непрочитанных диалогов
		$conversation_map_list = array_column($left_menu_list, "conversation_map");

		// прочитываем диалоги
		$left_menu_version = Type_Conversation_LeftMenu::setConversationsAsRead($user_id, $conversation_map_list, $left_menu_list);
		Type_Conversation_LeftMenu::recountTotalUnread($user_id);

		// отправляем запрос для обновления badge_count и удаления пушей прочитанных сообщений
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, $conversation_map_list, true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, (string) $user_id, [], $extra, 0);

		// отправляем пользователю ws-событие о прочтении всех сообщений в компании
		Gateway_Bus_Sender::conversationsMessagesReadAll($user_id, $left_menu_version, $filter_favorites);

		// добавляем пользователю экранное время
		Domain_User_Action_AddScreenTime::do($user_id, $local_date, $local_time);

		// инкрементим количество действий
		foreach ($conversation_map_list as $conversation_map) {
			Domain_User_Action_IncActionCount::incConversationRead($user_id, $conversation_map);
		}
	}
}