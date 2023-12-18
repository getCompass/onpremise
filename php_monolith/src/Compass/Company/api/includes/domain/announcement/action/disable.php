<?php

namespace Compass\Company;

/**
 * Отключение анонсов.
 */
class Domain_Announcement_Action_Disable {

	/**
	 * Публикует новый анонс и возвращает его id.
	 */
	public static function run(int $numeric_type, array $extra_filter = [], int $company_id = COMPANY_ID):void {

		try {
			Gateway_Announcement_Main::disable($numeric_type, $company_id, $extra_filter);
		} catch (\Exception) {

			// никак не зависим от анонсов
			// просто пока игнорируем то, что ничего не отключилось
		}
	}
}
