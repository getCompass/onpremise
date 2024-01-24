<?php

namespace Compass\Pivot;

/**
 * класс, описывающий поведение любого алгоритма для определения рекламной кампании
 */
abstract class Domain_User_Entity_Attribution_DetectAlgorithm_Abstract {

	/** список всех параметров, по которым осуществляется поиск совпадений */
	public const PARAMETER_IP_ADDRESS          = "ip_address";
	public const PARAMETER_PLATFORM            = "platform";
	public const PARAMETER_PLATFORM_OS         = "platform_os";
	public const PARAMETER_TIMEZONE_UTC_OFFSET = "timezone_utc_offset";
	public const PARAMETER_SCREEN_AVAIL_WIDTH  = "screen_avail_width";
	public const PARAMETER_SCREEN_AVAIL_HEIGHT = "screen_avail_height";

	/** @var int[] список параметров, используемых для расчета процента совпадения */
	protected const _MATCHING_PARAMETER_LIST = [];

	/**
	 * выбираем класс с алгоритмом, с помощью которого будем определять рекламную кампанию
	 *
	 * @param string $platform
	 *
	 * @return static
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function chooseAlgorithm(string $platform):self {

		return match ($platform) {
			Type_Api_Platform::PLATFORM_ELECTRON,
			Type_Api_Platform::PLATFORM_OTHER => new Domain_User_Entity_Attribution_DetectAlgorithm_Desktop(),
			Type_Api_Platform::PLATFORM_ANDROID,
			Type_Api_Platform::PLATFORM_IOS,
			Type_Api_Platform::PLATFORM_IPAD  => new Domain_User_Entity_Attribution_DetectAlgorithm_Mobile(),
			default                           => throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected platform ($platform)"),
		};
	}

	/**
	 * высчитываем процент совпадения
	 *
	 * @param Struct_Db_PivotAttribution_UserAppRegistration $registration_log
	 * @param Struct_Db_PivotAttribution_LandingVisit        $visit_log
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function countMatchingPercent(
		Struct_Db_PivotAttribution_UserAppRegistration $registration_log,
		Struct_Db_PivotAttribution_LandingVisit        $visit_log,
	):Struct_Dto_User_Entity_Attribution_DetectAlghoritm_Result {

		$matched_parameters_count        = 0;
		$parameter_comparing_result_list = [];
		foreach (static::_MATCHING_PARAMETER_LIST as $parameter_name) {

			// получаем значения из двух сравниваемых логов
			$visit_value        = $this->_getParameterValue($visit_log, $parameter_name);
			$registration_value = $this->_getParameterValue($registration_log, $parameter_name);
			$is_equal           = $visit_value == $registration_value;

			$parameter_comparing_result_list[] = new Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult(
				$parameter_name, $visit_value, $registration_value, $is_equal
			);

			// если значения не совпадают
			if (!$is_equal) {
				continue;
			}

			// если совпали, то инкрементим кол-во совпавших параметров
			$matched_parameters_count += 1;
		}

		return new Struct_Dto_User_Entity_Attribution_DetectAlghoritm_Result(
			round($matched_parameters_count / count(static::_MATCHING_PARAMETER_LIST) * 100),
			$parameter_comparing_result_list
		);
	}

	/**
	 * получаем значение параметра
	 *
	 * @return mixed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected function _getParameterValue(Struct_Db_PivotAttribution_UserAppRegistration|Struct_Db_PivotAttribution_LandingVisit $log, string $parameter_name):mixed {

		// если передали левачный параметр
		if (!in_array($parameter_name, static::_MATCHING_PARAMETER_LIST)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected parameter ($parameter_name)");
		}

		// если такого свойства нет у объекта, то передали какой-то неизвестный параметр
		if (!isset($log->$parameter_name)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected behaviour");
		}

		return $log->$parameter_name;
	}

}