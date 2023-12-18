<?php

namespace Compass\Pivot;

/**
 * DTO описывает структуру результата функции @see Domain_User_Action_Attribution_Detect::do
 * @package Compass\Pivot
 */
class Struct_Dto_User_Action_Attribution_Detect_Result {

	public function __construct(
		public null|Struct_Db_PivotAttribution_LandingVisit $matched_visit,
		public int                                          $join_page_matched_percentage,
	) {
	}
}