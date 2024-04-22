<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для действия - считаем актуальное количество пользователей
 */
class Domain_Premise_Action_RecountSpaceCounters {

	/**
	 * Выполняем
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function do():void {

		// получаем количество уникальных участников
		$member_count = Gateway_Db_PremiseUser_UserList::getCountByNpcAndSpaceStatus(
			\CompassApp\Domain\User\Main::NPC_TYPE_HUMAN, Domain_Premise_Entity_Space::UNIQUE_MEMBER_SPACE_STATUS
		);

		// обновляем количество в счётчике
		Gateway_Db_PremiseUser_SpaceCounter::insertOrUpdate(Domain_Premise_Entity_SpaceCounter::UNIQUE_MEMBER_KEY, $member_count);

		// получаем количество уникальных гостей
		$guest_count = Gateway_Db_PremiseUser_UserList::getCountByNpcAndSpaceStatus(
			\CompassApp\Domain\User\Main::NPC_TYPE_HUMAN, Domain_Premise_Entity_Space::UNIQUE_GUEST_SPACE_STATUS
		);

		// обновляем количество в счётчике
		Gateway_Db_PremiseUser_SpaceCounter::insertOrUpdate(Domain_Premise_Entity_SpaceCounter::UNIQUE_GUEST_KEY, $guest_count);
	}
}