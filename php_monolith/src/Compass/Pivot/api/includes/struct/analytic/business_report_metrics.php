<?php

namespace Compass\Pivot;

/**
 * Структура метрик, которые отправляются в отчете для бизнес отдела
 * @package Compass\Pivot
 */
class Struct_Analytic_BusinessReportMetrics {

	public function __construct(

		/** Reg */
		public int    $registered_users_count,

		/** Cnew */
		public int    $unique_space_creators_count,

		/** Cnew conversion */
		public float  $unique_space_creators_conversion,

		/** Cadd */
		public int    $unique_space_joined_users_count,

		/** Cadd conversion */
		public float  $unique_space_joined_users_conversion,

		/** RegAtr */
		public int    $attr_app_registered_user_count,

		/** RegAtr conversion */
		public float  $attr_app_registered_user_conversion,

		/** CaddAtr */
		public int    $attr_app_total_enter_space_count,

		/** CaddAtr conversion */
		public float  $attr_app_total_enter_space_conversion,

		/** spaces -> New */
		public int    $created_spaces_count,

		/** spaces -> Add */
		public int    $space_joining_count,

		/** spaces -> Rev */
		public string $revenue_sum,

		/** Sms-Agent */
		public string $sms_agent_balance,

		/** Vonage */
		public string $vonage_balance,

		/** Twilio */
		public string $twilio_balance,

		/** кол-во созданных temporary конференций */
		public string $temporary_conference_row_data,

		/** кол-во созданных single конференций */
		public string $single_conference_row_data,

		/** кол-во созданных permanent конференций */
		public string $permanent_conference_row_data,
	) {
	}

	/**
	 * Переводим объект в массив
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"registered_users_count"                => $this->registered_users_count,
			"unique_space_creators_count"           => $this->unique_space_creators_count,
			"unique_space_creators_conversion"      => $this->unique_space_creators_conversion,
			"unique_space_joined_users_count"       => $this->unique_space_joined_users_count,
			"unique_space_joined_users_conversion"  => $this->unique_space_joined_users_conversion,
			"attr_app_registered_user_count"        => $this->attr_app_registered_user_count,
			"attr_app_registered_user_conversion"   => $this->attr_app_registered_user_conversion,
			"attr_app_total_enter_space_count"      => $this->attr_app_total_enter_space_count,
			"attr_app_total_enter_space_conversion" => $this->attr_app_total_enter_space_conversion,
			"created_spaces_count"                  => $this->created_spaces_count,
			"space_joining_count"                   => $this->space_joining_count,
			"revenue_sum"                           => $this->revenue_sum,
			"sms_agent_balance"                     => $this->sms_agent_balance,
			"vonage_balance"                        => $this->vonage_balance,
			"twilio_balance"                        => $this->twilio_balance,
			"temporary_conference_row_data"         => $this->temporary_conference_row_data,
			"single_conference_row_data"            => $this->single_conference_row_data,
			"permanent_conference_row_data"         => $this->permanent_conference_row_data,
		];
	}
}