<?php

namespace Compass\Pivot;

/**
 * класс для валидации данных вводимых пользователем о компании
 */
class Domain_Company_Entity_Validator {

	protected const _MAX_NAME_LENGTH       = 80; // максимальная длина имени компании
	protected const _MAX_NAME_USER_COMMENT = 80; // максимальная длина комментария пользователя

	/**
	 * Выбрасываем исключение если передан неккоректный avatar_color_id
	 *
	 * @throws cs_CompanyIncorrectAvatarColorId
	 */
	public static function assertIncorrectAvatarColorId(int $avatar_color_id):void {

		if (!in_array($avatar_color_id, Domain_Company_Entity_Company::ALLOW_AVATAR_COLOR_ID_LIST)) {
			throw new cs_CompanyIncorrectAvatarColorId();
		}
	}

	/**
	 * Выбрасываем исключение если передано некоректное имя компании
	 *
	 * @throws cs_CompanyIncorrectName
	 */
	public static function assertIncorrectName(string $name):void {

		if (mb_strlen($name) < 1 || mb_strlen($name) > self::_MAX_NAME_LENGTH) {
			throw new cs_CompanyIncorrectName();
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный company_id
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function assertCorrectCompanyId(int $company_id):void {

		if ($company_id < 1) {
			throw new cs_CompanyIncorrectCompanyId();
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный min_order
	 *
	 * @throws cs_CompanyIncorrectMinOrder
	 */
	public static function assertIncorrectMinOrder(int $min_order):void {

		if ($min_order < 0) {
			throw new cs_CompanyIncorrectMinOrder();
		}
	}

	/**
	 * Выбрасываем исключение если передан некорректный limit
	 *
	 * @throws cs_CompanyIncorrectLimit
	 */
	public static function assertIncorrectLimit(int $limit):void {

		if ($limit < 1) {
			throw new cs_CompanyIncorrectLimit();
		}
	}

	/**
	 * Проверяем, активна ли компания
	 *
	 * @throws cs_CompanyIsNotActive
	 */
	public static function assertActiveCompany(Struct_Db_PivotCompany_Company $company):void {

		if ($company->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
			throw new cs_CompanyIsNotActive();
		}
	}

	/**
	 * Выбрасываем исключение если передано неккоректный device_id
	 *
	 * @throws cs_CompanyIncorrectDeviceId
	 */
	public static function assertDeviceId(string $test_device_id):void {

		if (isEmptyString($test_device_id)) {
			throw new cs_CompanyIncorrectDeviceId();
		}

		if (!checkUuid($test_device_id) && !checkGuid($test_device_id)) {
			throw new cs_CompanyIncorrectDeviceId();
		}
	}

	/**
	 * Выбрасываем исключение если передано некоректное client company id
	 *
	 * @throws cs_CompanyIncorrectClientCompanyId
	 */
	public static function assertIncorrectClientCompanyId(string $client_company_id):void {

		if (!checkUuid($client_company_id) && !checkGuid($client_company_id)) {
			throw new cs_CompanyIncorrectClientCompanyId();
		}
	}

	/**
	 * Выбрасываем исключение если передано некорректное число компаний
	 *
	 * @throws cs_CompanyIncorrectCompanyIdList
	 */
	public static function assertIncorrectCompanyIdList(array $company_id_list):void {

		if (count($company_id_list) < 1 || count($company_id_list) > Domain_Company_Entity_Filter::MAX_GET_USER_COMPANY_LIST) {
			throw new cs_CompanyIncorrectCompanyIdList();
		}
	}
}
