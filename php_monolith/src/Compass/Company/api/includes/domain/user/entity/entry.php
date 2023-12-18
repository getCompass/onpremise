<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с сущностью entry
 *
 * Class Domain_User_Entity_Entry
 */
class Domain_User_Entity_Entry {

	/**
	 * добавляем в таблицу entry_list
	 *
	 * @throws \queryException
	 */
	public static function addEntryList(int $user_id):array {

		$entry_type = \CompassApp\Domain\Member\Entity\Entry::ENTRY_INVITE_LINK_TYPE;
		$entry_id   = Gateway_Db_CompanyData_EntryList::insert($entry_type, $user_id);

		return [$entry_id, $entry_type];
	}

	/**
	 * добавляем с помощью инвайт линка
	 *
	 * @param int    $entry_id
	 * @param string $invite_link_uniq
	 * @param int    $inviter_user_id
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function addWithInviteLink(int $entry_id, string $invite_link_uniq, int $inviter_user_id):void {

		Gateway_Db_CompanyData_EntryJoinLinkList::insert($entry_id, $invite_link_uniq, $inviter_user_id);
	}

	/**
	 * добавляем создателя
	 *
	 * @throws \queryException
	 */
	public static function addCreatorType(int $user_id):array {

		$entry_type = \CompassApp\Domain\Member\Entity\Entry::ENTRY_CREATOR_TYPE;

		$entry_id = Gateway_Db_CompanyData_EntryList::insert($entry_type, $user_id);
		return [$entry_id, $entry_type];
	}

	/**
	 * добавляем без типа
	 *
	 * @throws \queryException
	 */
	public static function addWithoutType(int $user_id):array {

		$entry_type = \CompassApp\Domain\Member\Entity\Entry::ENTRY_WITHOUT_TYPE;

		$entry_id = Gateway_Db_CompanyData_EntryList::insert($entry_type, $user_id);
		return [$entry_id, $entry_type];
	}

	/**
	 * получаем тип entry
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getType(int $user_id):int {

		return Gateway_Db_CompanyData_EntryList::getOne($user_id)->entry_type;
	}
}
