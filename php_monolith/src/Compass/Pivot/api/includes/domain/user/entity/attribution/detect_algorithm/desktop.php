<?php

namespace Compass\Pivot;

/**
 * класс описывающий алгоритм определения рекламной кампании, если пользователь зарегистрировался на desktop платформе
 */
class Domain_User_Entity_Attribution_DetectAlgorithm_Desktop extends Domain_User_Entity_Attribution_DetectAlgorithm_Abstract {

	/** @var int[] список параметров, используемых для расчета процента совпадения */
	protected const _MATCHING_PARAMETER_LIST = [
		self::PARAMETER_IP_ADDRESS,
		self::PARAMETER_PLATFORM,
		self::PARAMETER_PLATFORM_OS,
		self::PARAMETER_TIMEZONE_UTC_OFFSET,
		self::PARAMETER_SCREEN_AVAIL_WIDTH,
		self::PARAMETER_SCREEN_AVAIL_HEIGHT,
	];
}