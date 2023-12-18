<?php

namespace Compass\Pivot;

/**
 * Класс для работы с white-list
 */
class Type_List_WhiteList {

	/**
	 * Проверяем наличие компании в белом списке
	 *
	 */
	public static function isCompanyInWhiteList(int $company_id):bool {

		try {
			Gateway_Db_PivotData_CheckpointCompanyList::get(Type_List_Main::WHITE_LIST_TYPE, $company_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}
		return true;
	}

	/**
	 * Добавляем компанию в белый список
	 *
	 */
	public static function addCompanyToWhiteList(int $company_id):void {

		Gateway_Db_PivotData_CheckpointCompanyList::set(Type_List_Main::WHITE_LIST_TYPE, $company_id);
	}

	/**
	 * Удалить компанию из белого списка
	 *
	 */
	public static function deleteCompanyFromWhiteList(int $company_id):void {

		Gateway_Db_PivotData_CheckpointCompanyList::delete(Type_List_Main::WHITE_LIST_TYPE, $company_id);
	}

	/**
	 * Проверяем наличие пользователя в белом списке
	 *
	 */
	public static function isPhoneHashInWhiteList(string $phone_number_hash):bool {

		try {
			Gateway_Db_PivotData_CheckpointPhoneNumberList::get(Type_List_Main::WHITE_LIST_TYPE, $phone_number_hash);
		} catch (\cs_RowIsEmpty) {
			return false;
		}
		return true;
	}

	/**
	 * Добавляем пользователя в белый список
	 *
	 */
	public static function addPhoneHashToWhiteList(string $phone_number_hash):void {

		Gateway_Db_PivotData_CheckpointPhoneNumberList::set(Type_List_Main::WHITE_LIST_TYPE, $phone_number_hash);
	}

	/**
	 * Удалить пользователя из белого списка
	 *
	 */
	public static function deletePhoneHashFromWhiteList(string $phone_number_hash):void {

		Gateway_Db_PivotData_CheckpointPhoneNumberList::delete(Type_List_Main::WHITE_LIST_TYPE, $phone_number_hash);
	}
}