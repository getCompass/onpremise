<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Action для получения счётчиков пользователей сервера.
 */
class Domain_User_Action_GetCounters {

	/**
	 * Выполяем действие.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do():array {

		$list = Gateway_Db_PremiseUser_SpaceCounter::getList([
			Domain_Premise_Entity_SpaceCounter::UNIQUE_MEMBER_KEY,
			Domain_Premise_Entity_SpaceCounter::UNIQUE_GUEST_KEY,
		]);

		$member_count = 0;
		$guest_count  = 0;
		foreach ($list as $obj) {

			if ($obj->key == Domain_Premise_Entity_SpaceCounter::UNIQUE_MEMBER_KEY) {

				$member_count = $obj->count;
				continue;
			}

			if ($obj->key == Domain_Premise_Entity_SpaceCounter::UNIQUE_GUEST_KEY) {
				$guest_count = $obj->count;
			}
		}

		return [$member_count, $guest_count];
	}
}
