<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с captcha-list
 */
class Type_List_CaptchaList {

	/**
	 * Проверяем наличие компании в captcha-list
	 *
	 */
	public static function isCompanyInCaptchaList(int $company_id):bool {

		try {
			$company_in_list = Gateway_Db_PivotData_CheckpointCompanyList::get(Type_List_Main::CAPTCHA_LIST_TYPE, $company_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}
		if ($company_in_list->expires_at < time()) {
			return false;
		}
		return true;
	}

	/**
	 * Добавляем компанию в captcha-list
	 *
	 */
	public static function addCompanyToCaptchaList(int $company_id, int $expire):void {

		Gateway_Db_PivotData_CheckpointCompanyList::set(Type_List_Main::CAPTCHA_LIST_TYPE, $company_id, $expire);
	}

	/**
	 * Удалить компанию из captcha-list
	 *
	 */
	public static function deleteCompanyFromCaptchaList(int $company_id):void {

		Gateway_Db_PivotData_CheckpointCompanyList::delete(Type_List_Main::CAPTCHA_LIST_TYPE, $company_id);
	}

	/**
	 * Проверяем наличие номера в captcha-list
	 *
	 */
	public static function isPhoneHashInCaptchaList(string $phone_number_hash):bool {

		try {
			$phone_in_list = Gateway_Db_PivotData_CheckpointPhoneNumberList::get(Type_List_Main::CAPTCHA_LIST_TYPE, $phone_number_hash);
		} catch (\cs_RowIsEmpty) {
			return false;
		}
		if ($phone_in_list->expires_at < time()) {
			return false;
		}
		return true;
	}

	/**
	 * Проверяем подсеть в subnet_result_list
	 */
	public static function isSuspectSubnet(string $ip_address):bool {

		// на тестовых не просим
		if (ServerProvider::isTest()) {
			return false;
		}

		try {
			$subnet_24_long = Domain_Subnet_Entity_Ip::getIp2LongSubnet24($ip_address);
			if ($subnet_24_long === false) {
				return true;
			}
			$subnet = Gateway_Db_PivotSystem_Subnet24ResultList::get($subnet_24_long);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		// получаем конфиг с блеклистом as хостеров
		$as_black_list_config = getConfig("ASBLACKLIST");

		// если это не хостинг и не прокси
		// и подсеть не находится в блеклисте
		// то капчу не просим
		if ($subnet->is_hosting != 1 && $subnet->is_proxy != 1 && !in_array($subnet->as, $as_black_list_config)) {
			return false;
		}

		return true;
	}

	/**
	 * Добавляем номер в captcha-list
	 *
	 */
	public static function addPhoneHashToCaptchaList(string $phone_number_hash, int $expire):void {

		Gateway_Db_PivotData_CheckpointPhoneNumberList::set(Type_List_Main::CAPTCHA_LIST_TYPE, $phone_number_hash, $expire);
	}

	/**
	 * Удалить номер из captcha-list
	 *
	 */
	public static function deletePhoneHashFromCaptchaList(string $phone_number_hash):void {

		Gateway_Db_PivotData_CheckpointPhoneNumberList::delete(Type_List_Main::CAPTCHA_LIST_TYPE, $phone_number_hash);
	}
}