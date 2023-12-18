<?php

namespace Compass\Conversation;

/**
 * Action для получения приглашенных в группу
 */
class Domain_Conversation_Action_GetInvitedList {

	/**
	 * Поулчить общие группы с пользователем
	 *
	 * @param string $conversation_map
	 *
	 * @return array
	 */
	public static function do(string $conversation_map):array {

		// получаем список активных инвайтов
		$invite_list = Gateway_Db_CompanyConversation_ConversationInviteList::getAllWithoutPagination($conversation_map, Type_Invite_Handler::STATUS_ACTIVE);

		return self::_getInvitedUserIds($invite_list);
	}

	// возвращает массив user_id в предсказуемом порядке с отфильтрованными отключенными пользователями
	protected static function _getInvitedUserIds(array $invite_list):array {

		// обеспечиваем предсказуемый порядок user_id
		usort($invite_list, function(array $a, array $b):int {

			// оператор <=> возвращает -1, 0 или 1 в зависимости от сравнения
			return $a["updated_at"] <=> $b["updated_at"];
		});

		$invited_user_id_list = array_column($invite_list, "user_id");

		// убираем повторяющиеся записи
		$invited_user_id_list = array_unique($invited_user_id_list);
		$invited_user_id_list = array_values($invited_user_id_list);

		// фильтруем пользователей, убирая заблокированных системой
		return self::_doFilterByDisabledSystem($invited_user_id_list);
	}

	// фильтруем пользователей, убирая заблокированных системой
	protected static function _doFilterByDisabledSystem(array $invited_user_id_list):array {

		// получаем информацию о пользователях
		$invited_user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($invited_user_id_list);

		// убираем заблокированных пользователей
		$invited_user_list = \CompassApp\Domain\Member\Entity\Member::getNotDisabledUsers($invited_user_info_list);

		// возвращаем id приглашенных незаблокированных пользователей
		return array_column($invited_user_list, "user_id");
	}
}