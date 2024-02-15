<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывает правило для фильтрации посещений по проценту совпадения параметров
 * @package Compass\Pivot
 */
class Domain_User_Entity_Attribution_VisitFilter_Rule_PercentMatching implements Domain_User_Entity_Attribution_VisitFilter_Rule_Interface {

	public const    OPERATOR_GREATER_THAN          = ">";
	public const    OPERATOR_LESS_THAN             = "<";
	public const    OPERATOR_EQUALS                = "=";
	public const    OPERATOR_GREATER_THAN_OR_EQUAL = ">=";
	public const    OPERATOR_LESS_THAN_OR_EQUAL    = "<=";
	protected const _AVAILABLE_OPERATOR_LIST       = [
		self::OPERATOR_GREATER_THAN,
		self::OPERATOR_LESS_THAN,
		self::OPERATOR_EQUALS,
		self::OPERATOR_GREATER_THAN_OR_EQUAL,
		self::OPERATOR_LESS_THAN_OR_EQUAL,
	];

	protected string $_operator;
	protected int    $_value;

	/**
	 * Создаем правило
	 *
	 * @return static
	 * @throws ParseFatalException
	 */
	public static function create(string $operator, int $value):self {

		if (!in_array($operator, self::_AVAILABLE_OPERATOR_LIST)) {
			throw new ParseFatalException("unexpected operator");
		}

		$result            = new self();
		$result->_operator = $operator;
		$result->_value    = $value;

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
		$result     = $comparator->countMatchingPercent($user_app_registration, $visit);

		return match ($this->_operator) {
			self::OPERATOR_GREATER_THAN          => $result->matched_percentage > $this->_value,
			self::OPERATOR_LESS_THAN             => $result->matched_percentage < $this->_value,
			self::OPERATOR_EQUALS                => $result->matched_percentage == $this->_value,
			self::OPERATOR_GREATER_THAN_OR_EQUAL => $result->matched_percentage >= $this->_value,
			self::OPERATOR_LESS_THAN_OR_EQUAL    => $result->matched_percentage <= $this->_value,
		};
	}
}