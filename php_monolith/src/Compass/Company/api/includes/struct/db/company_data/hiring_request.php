<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.hiring_request
 */
class Struct_Db_CompanyData_HiringRequest {

	public int    $hiring_request_id;
	public int    $status;
	public string $join_link_uniq;
	public int    $entry_id;
	public int    $hired_by_user_id;
	public int    $created_at;
	public int    $updated_at;
	public int    $candidate_user_id;
	public array  $extra;

	/**
	 * Struct_Db_CompanyData_HiringRequest constructor.
	 *
	 * @param int    $hiring_request_id
	 * @param int    $status
	 * @param string $invite_link_uniq
	 * @param int    $entry_id
	 * @param int    $hired_by_user_id
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $candidate_user_id
	 * @param array  $extra
	 */
	public function __construct(int    $hiring_request_id,
					    int    $status,
					    string $join_link_uniq,
					    int    $entry_id,
					    int    $hired_by_user_id,
					    int    $created_at,
					    int    $updated_at,
					    int    $candidate_user_id,
					    array  $extra) {

		$this->hiring_request_id = $hiring_request_id;
		$this->status            = $status;
		$this->join_link_uniq    = $join_link_uniq;
		$this->entry_id          = $entry_id;
		$this->hired_by_user_id  = $hired_by_user_id;
		$this->candidate_user_id = $candidate_user_id;
		$this->created_at        = $created_at;
		$this->updated_at        = $updated_at;
		$this->extra             = $extra;
	}

	/**
	 * Статический конструктор с поддержкой ленивого обновления.
	 * Тут немного каша, из-за непонятности, куда правильно двигать, но пока так.
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 */
	public static function construct(array $raw_data_list):array {

		return array_map(static fn(array $raw_item) => new Struct_Db_CompanyData_HiringRequest(
			$raw_item["hiring_request_id"],
			$raw_item["status"],
			$raw_item["join_link_uniq"],
			$raw_item["entry_id"],
			$raw_item["hired_by_user_id"],
			$raw_item["created_at"],
			$raw_item["updated_at"],
			$raw_item["candidate_user_id"],
			fromJson($raw_item["extra"]),
		), $raw_data_list);
	}

	/**
	 * Статический конструктор с поддержкой ленивого обновления.
	 * Тут немного каша, из-за непонятности, куда правильно двигать, но пока так.
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 */
	public static function constructAndLazyUpdate(array $raw_data_list):array {

		$result = static::construct($raw_data_list);

		Domain_HiringRequest_Lazy_UpdateMessageMap::check($result);
		return $result;
	}
}