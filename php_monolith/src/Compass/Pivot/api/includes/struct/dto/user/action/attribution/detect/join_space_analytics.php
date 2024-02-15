<?php

namespace Compass\Pivot;

/**
 * DTO описывает структуру аналитики по поищку 100% совпадения между цифровыми отпечатками регистрации и цифровыми отпечатками посещений /join/-страницы
 * @package Compass\Pivot
 */
class Struct_Dto_User_Action_Attribution_Detect_JoinSpaceAnalytics {

	public function __construct(
		public int    $user_id,
		public int    $result_mask,
		public string $matched_visit_id,
		public string $traffic_type,

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
			"selection_visit_list" => [],
			"traffic_type"         => $this->traffic_type,
		];

		foreach ($this->visit_parameters_comparing_result_map as $visit_id => $compare_parameter_list) {

			$formatted_data["selection_visit_list"][] = [
				"visit_id"               => $visit_id,
				"compare_parameter_list" => $compare_parameter_list,
			];
		}

		return [
			"user_id"          => $this->user_id,
			"result_mask"      => $this->result_mask,
			"matched_visit_id" => $this->matched_visit_id,
			"data"             => $formatted_data,
		];
	}
}