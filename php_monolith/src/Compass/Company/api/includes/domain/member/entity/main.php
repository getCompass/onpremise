<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Gateway\Bus\CompanyCache;

/**
 * класс для взаимодействия с участниками компании
 *
 * унаследован от @see Member чтобы переопределить класс для работы с CompanyCache
 *
 * @package Compass\Company
 */
class Domain_Member_Entity_Main extends Member {

	/**
	 * Возвращаем инстанс класса для работы с company_cache
	 *
	 * @return CompanyCache
	 * @noinspection PhpUnused
	 */
	protected static function _getCompanyCache():CompanyCache {

		return new Gateway_Bus_CompanyCache();
	}
}