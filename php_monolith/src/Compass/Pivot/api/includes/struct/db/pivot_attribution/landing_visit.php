<?php

namespace Compass\Pivot;

/**
 * класс описывающий структуру таблицы
 */
class Struct_Db_PivotAttribution_LandingVisit {

	public function __construct(
		public string $visit_id,
		public string $guest_id,
		public string $link,
		public string $utm_tag,
		public string $source_id,
		public string $ip_address,
		public string $platform,
		public string $platform_os,
		public int    $timezone_utc_offset,
		public int    $screen_avail_width,
		public int    $screen_avail_height,
		public int    $visited_at,
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
			$row["visit_id"],
			$row["guest_id"],
			$row["link"],
			$row["utm_tag"],
			$row["source_id"],
			$row["ip_address"],
			$row["platform"],
			$row["platform_os"],
			$row["timezone_utc_offset"],
			$row["screen_avail_width"],
			$row["screen_avail_height"],
			$row["visited_at"],
			$row["created_at"],
		);
	}
}