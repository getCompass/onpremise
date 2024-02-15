<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывает правило для фильтрации посещений по совпадениям с переданным параметром
 * @package Compass\Pivot
 */
class Domain_User_Entity_Attribution_VisitFilter_Rule_ParameterMatching implements Domain_User_Entity_Attribution_VisitFilter_Rule_Interface {

	protected string $_parameter;

	/**
	 * Создаем правило
	 *
	 * @return static
	 * @throws ParseFatalException
	 */
	public static function create(string $parameter):self {

		$result             = new self();
		$result->_parameter = $parameter;

		return $result;
	}

	/**
	 * Проверяем выполнение правила
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public function check(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration, Struct_Db_PivotAttribution_LandingVisit $visit):bool {

		$comparator = Domain_User_Entity_Attribution_Comparator_Abstract::choose($user_app_registration->platform);

		return $comparator->compareByParameter($user_app_registration, $visit, $this->_parameter)->is_equal;
	}
}