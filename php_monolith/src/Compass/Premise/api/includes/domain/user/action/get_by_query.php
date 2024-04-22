<?php

namespace Compass\Premise;

/**
 * Класс для действия - получить пользователей по запросу
 */
class Domain_User_Action_GetByQuery {

	protected const _LIMIT_WITH_QUERY    = 100; // лимит записей по запросу
	protected const _LIMIT_WITHOUT_QUERY = 500; // лимит записей если запрос пустой

	/**
	 * Выполняем действие.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(string $query):array {

		// получаем записи
		if (mb_strlen($query) > 0) {

			Domain_User_Entity_Search::validateSearchQuery($query);
			$limit     = self::_LIMIT_WITH_QUERY;
			$user_list = Domain_User_Entity_Search::find($query, $limit);
		} else {

			$limit     = self::_LIMIT_WITHOUT_QUERY;
			$user_list = Gateway_Db_PivotUser_UserList::getByLimit($limit * 2);
		}

		// получаем список пользователей из базы премайза
		$premise_user_list = Gateway_Db_PremiseUser_UserList::getList(array_column($user_list, "user_id"));

		// убираем заблокированных и ботов
		$filtered_user_list = [];
		foreach ($user_list as $user) {

			if (!isset($premise_user_list[$user->user_id])) {
				continue;
			}

			// если пустое имя - случай когда зарегистрировался, но не успел заполнить имя
			if (mb_strlen($user->full_name) < 1) {
				continue;
			}

			if (!\CompassApp\Domain\User\Main::isHuman($user->npc_type) || Type_User_Main::isDisabledProfile($user->extra)) {
				continue;
			}

			$filtered_user_list[] = $user;
		}

		return array_slice($filtered_user_list, 0, $limit);
	}
}