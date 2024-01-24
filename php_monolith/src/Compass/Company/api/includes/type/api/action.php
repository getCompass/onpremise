<?php

namespace Compass\Company;

use CompassApp\Controller\ApiAction;

/**
 * экшены апи компании
 *
 * унаследован от @see ApiAction чтобы переопределить класс для работы с CompanyCache
 *
 * @package Compass\Company
 */
class Type_Api_Action extends ApiAction {

	/**
	 * Возвращаем инстанс класса для работы с company_cache
	 *
	 * @return Gateway_Bus_CompanyCache
	 */
	protected static function _getCompanyCache():Gateway_Bus_CompanyCache {

		return new Gateway_Bus_CompanyCache();
	}
}