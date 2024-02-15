<?php

namespace Compass\Pivot;

/**
 * абстрактный класс, содержит логику по сравнению параметров регистрации и посещения
 */
abstract class Domain_User_Entity_Attribution_Comparator_Abstract {

	/** список всех параметров, по которым осуществляется поиск совпадений */
	public const PARAMETER_IP_ADDRESS          = "ip_address";
	public const PARAMETER_PLATFORM            = "platform";
	public const PARAMETER_PLATFORM_OS         = "platform_os";
	public const PARAMETER_TIMEZONE_UTC_OFFSET = "timezone_utc_offset";
	public const PARAMETER_SCREEN_AVAIL_WIDTH  = "screen_avail_width";
	public const PARAMETER_SCREEN_AVAIL_HEIGHT = "screen_avail_height";

	/** @var int[] список параметров, используемых для сравнения */
	protected const _COMPARING_PARAMETER_LIST = [];

	/**
	 * выбираем класс, с помощью которого будем сравнивать параметры
	 *
	 * @param string $platform
	 *
	 * @return static
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function choose(string $platform):self {

		return match ($platform) {
			Type_Api_Platform::PLATFORM_ELECTRON,
			Type_Api_Platform::PLATFORM_OTHER => new Domain_User_Entity_Attribution_Comparator_Desktop(),
			Type_Api_Platform::PLATFORM_ANDROID,
			Type_Api_Platform::PLATFORM_IOS,
			Type_Api_Platform::PLATFORM_IPAD  => new Domain_User_Entity_Attribution_Comparator_Mobile(),
			default                           => throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected platform ($platform)"),
		};
	}

	/**
	 * высчитываем процент совпадающих параметров
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
		foreach (static::_COMPARING_PARAMETER_LIST as $parameter_name) {

			$result                            = $this->compareByParameter($registration_log, $visit_log, $parameter_name);
			$parameter_comparing_result_list[] = $result;

			// если значения не совпадают
			if (!$result->is_equal) {
				continue;
			}

			// если совпали, то инкрементим кол-во совпавших параметров
			$matched_parameters_count += 1;
		}

		return new Struct_Dto_User_Entity_Attribution_DetectAlghoritm_Result(
			round($matched_parameters_count / count(static::_COMPARING_PARAMETER_LIST) * 100),
			$parameter_comparing_result_list
		);
	}

	/**
	 * Сравниваем по конкретному параметру
	 *
	 * @return Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function compareByParameter(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, Struct_Db_PivotAttribution_LandingVisit $visit, string $parameter_name):Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult {

		// получаем значения из двух сравниваемых логов
		$registration_value = $this->_getParameterValue($user_app_registration, $parameter_name);
		$visit_value        = $this->_getParameterValue($visit, $parameter_name);

		$is_equal = $visit_value == $registration_value;

		return new Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult(
			$parameter_name, $visit_value, $registration_value, $is_equal
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
		if (!in_array($parameter_name, static::_COMPARING_PARAMETER_LIST)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected parameter ($parameter_name)");
		}

		// если такого свойства нет у объекта, то передали какой-то неизвестный параметр
		if (!isset($log->$parameter_name)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected behaviour");
		}

		return $log->$parameter_name;
	}

}