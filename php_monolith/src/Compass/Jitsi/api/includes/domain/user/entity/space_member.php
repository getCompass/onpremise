<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с участниками пространства
 * @package Compass\Jitsi
 */
class Domain_User_Entity_SpaceMember {

	/**
	 * является ли пользователь участником пространства
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function isMember(int $space_id, int $user_id):bool {

		// пытаемся достать запись, что пользователь участник пространства
		try {

			Gateway_Db_PivotCompany_CompanyUserList::getOne($space_id, $user_id);
			return true;
		} catch (RowNotFoundException) {

			// запись не найдена – тут ничего не делаем, ниже вернем ответ
		}

		return false;
	}

	/**
	 * Имеют ли пользователи пересекающиеся компании, когда и user_id_1 и user_id_2 состоят хотя бы в 1 общей компании
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function usersHaveIntersectSpace(int $user_id_1, int $user_id_2):bool {

		$intersect_space_id_list = Gateway_Socket_Pivot::getUsersIntersectSpaces($user_id_1, $user_id_2);

		return count($intersect_space_id_list) > 0;
	}

	/**
	 * Является ли пользователи участниками пространства
	 *
	 * @param int   $space_id
	 * @param array $user_id_list
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function areMembers(int $space_id, array $user_id_list):bool {

		// пытаемся достать записи, что пользователи участники пространства
		$company_user_list = Gateway_Db_PivotCompany_CompanyUserList::getList($space_id, $user_id_list);

		return count($company_user_list) === count($user_id_list);
	}
}