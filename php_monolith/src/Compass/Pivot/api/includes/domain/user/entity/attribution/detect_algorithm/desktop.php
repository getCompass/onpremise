<?php

namespace Compass\Pivot;

/**
 * класс описывающий алгоритм определения рекламной кампании, если пользователь зарегистрировался на desktop платформе
 */
class Domain_User_Entity_Attribution_DetectAlgorithm_Desktop extends Domain_User_Entity_Attribution_DetectAlgorithm_Abstract {

	/** @var int[] список параметров, используемых для расчета процента совпадения */
	protected const _MATCHING_PARAMETER_LIST = [
		self::_PARAMETER_IP_ADDRESS,
		self::_PARAMETER_PLATFORM,
		self::_PARAMETER_PLATFORM_OS,
		self::_PARAMETER_TIMEZONE_UTC_OFFSET,
		self::_PARAMETER_SCREEN_AVAIL_WIDTH,
		self::_PARAMETER_SCREEN_AVAIL_HEIGHT,
	];
}