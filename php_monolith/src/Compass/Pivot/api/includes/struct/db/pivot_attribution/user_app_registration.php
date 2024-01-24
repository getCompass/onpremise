<?php

namespace Compass\Pivot;

/**
 * класс описывающий структуру таблицы
 */
class Struct_Db_PivotAttribution_UserAppRegistration {

	public function __construct(
		public int    $user_id,
		public string $ip_address,
		public string $platform,
		public string $platform_os,
		public int    $timezone_utc_offset,
		public int    $screen_avail_width,
		public int    $screen_avail_height,
		public int    $registered_at,
		public int    $created_at,
	) {
	}

	/**
	 * Конвертируем запись БД в структуру
	 *
	 * @return static
	 */
	public static function rowToStruct(array $row):self {

		return new self(
			$row["user_id"],
			$row["ip_address"],
			$row["platform"],
			$row["platform_os"],
			$row["timezone_utc_offset"],
			$row["screen_avail_width"],
			$row["screen_avail_height"],
			$row["registered_at"],
			$row["created_at"],
		);
	}
}