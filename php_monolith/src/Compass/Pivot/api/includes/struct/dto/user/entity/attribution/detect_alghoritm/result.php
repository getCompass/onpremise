<?php

namespace Compass\Pivot;

/**
 * DTO описывает структуру результата функции @see Domain_User_Entity_Attribution_DetectAlgorithm_Abstract::countMatchingPercent
 * @package Compass\Pivot
 */
class Struct_Dto_User_Entity_Attribution_DetectAlghoritm_Result {

	public function __construct(
		public int   $matched_percentage = 0,

		/** @var Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult[] $parameter_comparing_result_list */
		public array $parameter_comparing_result_list = [],
	) {
	}
}