<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * Основной класс для сущность пространства
 */
class Domain_Space_Entity_Main {

	/**
	 * Получаем пространство
	 *
	 * @throws Domain_Space_Exception_NotFound
	 * @throws Domain_Space_Exception_UnexpectedStatus
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function get(int $space_id):Struct_Db_PivotCompany_Company {

		try {
			$space = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (RowNotFoundException) {
			throw new Domain_Space_Exception_NotFound();
		}

		Domain_Space_Entity_Status::assertActive($space);

		return $space;
	}
}