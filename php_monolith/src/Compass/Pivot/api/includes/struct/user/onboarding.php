<?php

namespace Compass\Pivot;

/**
 * Структура онбоардинга
 */
class Struct_User_Onboarding {

	/**
	 * Struct_User_Company constructor.
	 *
	 * @param int   $type
	 * @param int   $status
	 * @param array $data
	 * @param int   $activated_at
	 * @param int   $finished_at
	 */
	public function __construct(
		public int   $type,
		public int   $status,
		public array $data,
		public int   $activated_at,
		public int   $finished_at,
	) {

	}

	/**
	 * Из массива
	 *
	 * @param array $onboarding_arr
	 *
	 * @return static
	 */
	public static function fromArray(array $onboarding_arr):self {

		return new self(
			$onboarding_arr["type"],
			$onboarding_arr["status"],
			$onboarding_arr["data"],
			$onboarding_arr["activated_at"],
			$onboarding_arr["finished_at"],
		);
	}

	/**
	 * В массив
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"type"         => $this->type,
			"status"       => $this->status,
			"data"         => $this->data,
			"activated_at" => $this->activated_at,
			"finished_at"  => $this->finished_at,
		];
	}
}
