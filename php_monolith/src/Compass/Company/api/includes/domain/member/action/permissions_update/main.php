<?php

namespace Compass\Company;

/**
 * Интерфейс для апдейта прав
 */
interface Domain_Member_Action_PermissionsUpdate_Main {

	/**
	 * Обновить права
	 *
	 * @param array                 $member_list
	 * @param \BaseFrame\System\Log $log
	 * @param bool                  $is_dry
	 *
	 * @return array
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function do(array $member_list, \BaseFrame\System\Log $log, bool $is_dry):array;

}