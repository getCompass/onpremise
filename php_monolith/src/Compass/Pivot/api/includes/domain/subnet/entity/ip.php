<?php

namespace Compass\Pivot;

/**
 * Класс для работы с ip
 */
class Domain_Subnet_Entity_Ip {

	/**
	 * Получаем подсеть /24 из ip
	 *
	 * @param string $ip_address
	 *
	 * @return string
	 */
	public static function getIpSubnet24(string $ip_address):string {

		return self::_getIpSubnet($ip_address, 24);
	}

	/**
	 * Получаем подсеть /24 из ip преобразованную через ip2long())
	 *
	 * @param string $ip_address
	 *
	 * @return int|false
	 */
	public static function getIp2LongSubnet24(string $ip_address):int|false {

		$subnet_24 = self::getIpSubnet24($ip_address);
		return ip2long(explode("/", $subnet_24)[0]);
	}

	/**
	 * Получаем необходимый subnet из ip
	 *
	 * @param string $ip_address
	 * @param int    $mask
	 *
	 * @return string
	 */
	protected static function _getIpSubnet(string $ip_address, int $mask):string {

		$subnet_mask = -1 << (32 - $mask);
		$num         = ip2long($ip_address);
		$num         = $num & $subnet_mask;
		return long2ip($num) . "/" . $mask;
	}
}