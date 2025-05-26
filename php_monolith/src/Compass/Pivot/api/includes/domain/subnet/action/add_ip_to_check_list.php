<?php

namespace Compass\Pivot;

/**
 * Класс для добавления ip на проверку
 */
class Domain_Subnet_Action_AddIpToCheckList {

	/**
	 * Выполняем действие
	 *
	 * @param string $ip_address
	 *
	 * @return void
	 */
	public static function do(string $ip_address):void {

		$subnet_24_long = Domain_Subnet_Entity_Ip::getIp2LongSubnet24($ip_address);

		try {
			Gateway_Db_PivotSystem_Subnet24ResultList::get($subnet_24_long);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotSystem_Subnet24CheckList::insert(
				$subnet_24_long,
				Domain_Subnet_Entity_Check::STATUS_NEED_CHECK,
				ip2long($ip_address),
				time(),
				Domain_Subnet_Entity_Check::initExtra()
			);
		}
	}
}