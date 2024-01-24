<?php

namespace Compass\Pivot;

/**
 * DTO описывает структуру результата сравнения параметров визита и регистрации
 * @package Compass\Pivot
 */
class Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult {

	public function __construct(
		public string $parameter_name,
		public string $visit_value,
		public string $registration_value,
		public bool   $is_equal,
	) {
	}
}