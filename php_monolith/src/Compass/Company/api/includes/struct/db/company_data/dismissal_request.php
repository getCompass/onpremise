<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.dismissal_request
 */
class Struct_Db_CompanyData_DismissalRequest {

	public int   $dismissal_request_id;
	public int   $status;
	public int   $created_at;
	public int   $updated_at;
	public int   $creator_user_id;
	public int   $dismissal_user_id;
	public array $extra;

	/**
	 * Struct_Db_CompanyData_DismissalRequest constructor.
	 *
	 * @param int   $dismissal_request_id
	 * @param int   $status
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param int   $creator_user_id
	 * @param int   $dismissal_user_id
	 * @param array $extra
	 */
	public function __construct(int   $dismissal_request_id,
					    int   $status,
					    int   $created_at,
					    int   $updated_at,
					    int   $creator_user_id,
					    int   $dismissal_user_id,
					    array $extra) {

		$this->dismissal_request_id = $dismissal_request_id;
		$this->status               = $status;
		$this->created_at           = $created_at;
		$this->updated_at           = $updated_at;
		$this->creator_user_id      = $creator_user_id;
		$this->dismissal_user_id    = $dismissal_user_id;
		$this->extra                = $extra;
	}

	/**
	 * Статический конструктор с поддержкой ленивого обновления.
	 * Тут немного каша, из-за непонятности, куда правильно двигать, но пока так.
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest[]
	 */
	public static function construct(array $raw_data_list):array {

		return array_map(static fn(array $raw_item) => new Struct_Db_CompanyData_DismissalRequest(
			$raw_item["dismissal_request_id"],
			$raw_item["status"],
			$raw_item["created_at"],
			$raw_item["updated_at"],
			$raw_item["creator_user_id"],
			$raw_item["dismissal_user_id"],
			fromJson($raw_item["extra"]),
		), $raw_data_list);
	}

	/**
	 * Статический конструктор с поддержкой ленивого обновления.
	 * Тут немного каша, из-за непонятности, куда правильно двигать, но пока так.
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest[]
	 */
	public static function constructAndLazyUpdate(array $raw_data_list):array {

		// создаем список
		$result = static::construct($raw_data_list);

		Domain_DismissalRequest_Lazy_UpdateMessageMap::process($result);
		return $result;
	}
}