<?php

namespace Compass\Jitsi;

/**
 * класс для работы со статусом пространства
 * @package Compass\Jitsi
 */
class Domain_Space_Entity_Status {

	public const COMPANY_STATUS_CREATING   = 0; // статус компании - создается
	public const COMPANY_STATUS_VACANT     = 1; // статус компании - свободна
	public const COMPANY_STATUS_ACTIVE     = 2; // статус компании - активная
	public const COMPANY_STATUS_HIBERNATED = 10; // компания в гибернации
	public const COMPANY_STATUS_RELOCATING = 40; // компания переезжает
	public const COMPANY_STATUS_DELETED    = 50; // компания удалена
	public const COMPANY_STATUS_INVALID    = 99; // статус компании - недоступна

	/**
	 * проверяем, что пространство активно
	 *
	 * @throws Domain_Space_Exception_UnexpectedStatus
	 */
	public static function assertActive(Struct_Db_PivotCompany_Company $space):void {

		if ($space->status !== self::COMPANY_STATUS_ACTIVE) {
			throw new Domain_Space_Exception_UnexpectedStatus();
		}
	}
}