<?php

namespace Compass\Pivot;

/**
 * DTO описывает структуру аналитики по поищку 100% совпадения между цифровыми отпечатками регистрации и цифровыми отпечатками посещений /join/-страницы
 * @package Compass\Pivot
 */
class Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics {

	public function __construct(
		public int    $user_id,
		public int    $result,
		public string $matched_visit_id,
		public int    $matched_percentage,

		/**
		 * здесь хранится мапа следующего формата: [$visit_id] => [
		 * @see Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult,
		 * @see Struct_Dto_User_Entity_Attribution_DetectAlghoritm_ParameterComparingResult,
		 *      ...
		 * ]
		 */
		public array  $visit_parameters_comparing_result_map,
	) {
	}

	/**
	 * Форматируем структуру перед тем как отправить в аналитику
	 *
	 * @return array
	 */
	public function format():array {

		$formatted_data = [
			"matched_percentage"   => $this->matched_percentage,
			"selection_visit_list" => [],
		];

		foreach ($this->visit_parameters_comparing_result_map as $visit_id => $compare_parameter_list) {

			$formatted_data["selection_visit_list"][] = [
				"visit_id"               => $visit_id,
				"compare_parameter_list" => $compare_parameter_list,
			];
		}

		return [
			"user_id"          => $this->user_id,
			"result"           => $this->result,
			"matched_visit_id" => $this->matched_visit_id,
			"data"             => $formatted_data,
		];
	}
}