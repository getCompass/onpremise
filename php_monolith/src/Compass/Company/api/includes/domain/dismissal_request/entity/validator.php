<?php

namespace Compass\Company;

/**
 * Класс для валидации данных заявки
 */
class Domain_DismissalRequest_Entity_Validator {

	protected const _MAX_DISMISSAL_REQUEST_COUNT = 50;

	/**
	 * Выбрасываем исключение если передан некорректный $dismissal_request_id
	 *
	 * @throws cs_IncorrectDismissalRequestId
	 */
	public static function assertDismissalRequestId(int $dismissal_request_id):void {

		if ($dismissal_request_id < 1) {
			throw new cs_IncorrectDismissalRequestId();
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный $dismissal_request_id_list
	 *
	 * @throws cs_IncorrectDismissalRequestId
	 * @throws cs_IncorrectDismissalRequestIdList
	 */
	public static function assertDismissalRequestIdList(array $dismissal_request_id_list):void {

		if (count($dismissal_request_id_list) < 1 || count($dismissal_request_id_list) > self::_MAX_DISMISSAL_REQUEST_COUNT) {
			throw new cs_IncorrectDismissalRequestIdList();
		}

		foreach ($dismissal_request_id_list as $v) {
			self::assertDismissalRequestId($v);
		}
	}
}
