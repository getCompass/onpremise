<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс сущности динамик данных компании
 */
class Domain_Company_Entity_Dynamic {

	public const HIRING_REQUEST_WAITING                  = "hiring_request_waiting";
	public const HIRING_REQUEST_APPROVED                 = "hiring_request_approved";
	public const HIRING_REQUEST_REJECTED                 = "hiring_request_rejected";
	public const HIRING_REQUEST_CONFIRMED                = "hiring_request_confirmed";
	public const HIRING_REQUEST_CONFIRMED_POSTMODERATION = "hiring_request_confirmed_postmoderation";
	public const HIRING_REQUEST_NEED_CONFIRM             = "hiring_request_need_confirm";
	public const HIRING_REQUEST_NEED_POSTMODERATION      = "hiring_request_need_postmoderation";
	public const HIRING_REQUEST_DECLINED                 = "hiring_request_declined";
	public const HIRING_REQUEST_DISMISSED                = "hiring_request_dismissed";
	public const DISMISSAL_REQUEST_WAITING               = "dismissal_request_waiting";
	public const DISMISSAL_REQUEST_APPROVED              = "dismissal_request_approved";
	public const DISMISSAL_REQUEST_REJECTED              = "dismissal_request_rejected";
	public const DISMISSAL_REQUEST_DELETED               = "dismissal_request_deleted";
	public const HIBERNATION_IMMUNITY_TILL               = "hibernation_immunity_till";
	public const LAST_WAKEUP_AT                          = "last_wakeup_at";

	// список доступных ключей
	public const ALLOWED_KEYS = [
		self::HIRING_REQUEST_WAITING,
		self::HIRING_REQUEST_APPROVED,
		self::HIRING_REQUEST_REJECTED,
		self::HIRING_REQUEST_CONFIRMED,
		self::HIRING_REQUEST_CONFIRMED_POSTMODERATION,
		self::HIRING_REQUEST_NEED_CONFIRM,
		self::HIRING_REQUEST_NEED_POSTMODERATION,
		self::HIRING_REQUEST_DECLINED,
		self::HIRING_REQUEST_DISMISSED,
		self::DISMISSAL_REQUEST_WAITING,
		self::DISMISSAL_REQUEST_APPROVED,
		self::DISMISSAL_REQUEST_REJECTED,
		self::DISMISSAL_REQUEST_DELETED,
		self::HIBERNATION_IMMUNITY_TILL,
		self::LAST_WAKEUP_AT,
	];

	// список ключей найма
	public const HIRING_REQUEST_STATUS_TO_KEY_SCHEMA = [
		Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION      => self::HIRING_REQUEST_NEED_POSTMODERATION,
		Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED                => self::HIRING_REQUEST_CONFIRMED,
		Domain_HiringRequest_Entity_Request::STATUS_NEED_CONFIRM             => self::HIRING_REQUEST_NEED_CONFIRM,
		Domain_HiringRequest_Entity_Request::STATUS_CONFIRMED_POSTMODERATION => self::HIRING_REQUEST_CONFIRMED_POSTMODERATION,
		Domain_HiringRequest_Entity_Request::STATUS_REJECTED                 => self::HIRING_REQUEST_REJECTED,
		Domain_HiringRequest_Entity_Request::STATUS_REVOKED                  => self::HIRING_REQUEST_DECLINED,
		Domain_HiringRequest_Entity_Request::STATUS_DISMISSED                => self::HIRING_REQUEST_DISMISSED,
	];

	// список ключей увольнения
	public const DISMISSAL_REQUEST_STATUS_TO_KEY_SCHEMA = [
		Domain_DismissalRequest_Entity_Request::STATUS_DELETED  => self::DISMISSAL_REQUEST_DELETED,
		Domain_DismissalRequest_Entity_Request::STATUS_WAITING  => self::DISMISSAL_REQUEST_WAITING,
		Domain_DismissalRequest_Entity_Request::STATUS_REJECTED => self::DISMISSAL_REQUEST_REJECTED,
		Domain_DismissalRequest_Entity_Request::STATUS_APPROVED => self::DISMISSAL_REQUEST_APPROVED,
	];

	/**
	 * получаем значение по ключу
	 *
	 * @param string $key
	 *
	 * @return Struct_Db_CompanyData_CompanyDynamic
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function get(string $key):Struct_Db_CompanyData_CompanyDynamic {

		// проверяем что пеереданный ключ существует
		self::_throwIfBadDynamicKey($key);

		return Gateway_Db_CompanyData_CompanyDynamic::get($key);
	}

	/**
	 * Получаем массив значений из dynamic
	 */
	public static function getList(array $key_list):array {

		return Gateway_Db_CompanyData_CompanyDynamic::getList($key_list);
	}

	/**
	 * инкрементим по ключу
	 *
	 * @throws \parseException
	 */
	public static function inc(string $key):void {

		// проверяем что пеереданный ключ существует
		self::_throwIfBadDynamicKey($key);

		self::_inc($key);
	}

	/**
	 * инкрементим по статусу
	 *
	 * @throws \parseException
	 */
	public static function incHiringByStatus(int $status):void {

		// проверяем что пеереданный ключ существует
		if (!isset(self::HIRING_REQUEST_STATUS_TO_KEY_SCHEMA[$status])) {
			throw new ParseFatalException("Not exist dynamic key");
		}
		$key = self::HIRING_REQUEST_STATUS_TO_KEY_SCHEMA[$status];
		self::_inc($key);
	}

	/**
	 * инкрементим по статусу
	 *
	 * @throws \parseException
	 */
	public static function incDismissalByStatus(int $status):void {

		// проверяем что пеереданный ключ существует
		if (!isset(self::DISMISSAL_REQUEST_STATUS_TO_KEY_SCHEMA[$status])) {
			throw new ParseFatalException("Not exist dynamic key");
		}
		$key = self::DISMISSAL_REQUEST_STATUS_TO_KEY_SCHEMA[$status];
		self::_inc($key);
	}

	// инкрементим по ключу
	protected static function _inc(string $key):void {

		try {

			Gateway_Db_CompanyData_CompanyDynamic::set($key, [
				"updated_at" => time(),
				"value"      => "value + 1",
			]);
		} catch (cs_RowNotUpdated) {
			Gateway_Db_CompanyData_CompanyDynamic::insert(new Struct_Db_CompanyData_CompanyDynamic($key, 1, time(), time()));
		}
	}

	// обновляем по ключу
	public static function set(string $key, int $value):void {

		try {

			Gateway_Db_CompanyData_CompanyDynamic::set($key, [
				"updated_at" => time(),
				"value"      => $value,
			]);
		} catch (cs_RowNotUpdated) {
			Gateway_Db_CompanyData_CompanyDynamic::insert(new Struct_Db_CompanyData_CompanyDynamic($key, $value, time(), time()));
		}
	}

	/**
	 * декрементим по ключу
	 *
	 * @param int $subtrahend вычитаемое значение
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function dec(string $key, int $subtrahend = 1):void {

		// проверяем что пеереданный ключ существует
		self::_throwIfBadDynamicKey($key);
		self::_dec($key, $subtrahend);
	}

	/**
	 * декрементим по статусу
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function decHiringByStatus(int $status):void {

		// проверяем что пеереданный ключ существует
		if (!isset(self::HIRING_REQUEST_STATUS_TO_KEY_SCHEMA[$status])) {
			throw new ParseFatalException("Not exist dynamic key");
		}
		$key = self::HIRING_REQUEST_STATUS_TO_KEY_SCHEMA[$status];
		self::_dec($key);
	}

	/**
	 * декрементим по статусу
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function decDismissalByStatus(int $status):void {

		// проверяем что пеереданный ключ существует
		if (!isset(self::DISMISSAL_REQUEST_STATUS_TO_KEY_SCHEMA[$status])) {
			throw new ParseFatalException("Not exist dynamic key");
		}
		$key = self::DISMISSAL_REQUEST_STATUS_TO_KEY_SCHEMA[$status];
		self::_dec($key);
	}

	/**
	 * Проверяем, удалена ли компания
	 *
	 * @throws cs_CompanyIsDeleted
	 */
	public static function assertCompanyIsNotDeleted():void {

		// проверяем что пеереданный ключ существует
		try {
			$is_deleted = Gateway_Db_CompanyData_CompanyDynamic::get("is_deleted_alias");
		} catch (\cs_RowIsEmpty) {
			return;
		}

		if ($is_deleted) {
			throw new cs_CompanyIsDeleted();
		}
	}

	/**
	 * уменьшаем значения ключа
	 *
	 * @param string $key
	 * @param int    $subtrahend
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _dec(string $key, int $subtrahend = 1):void {

		try {

			$value_row = Gateway_Db_CompanyData_CompanyDynamic::getForUpdate($key);
			$new_value = $value_row->value - $subtrahend;

			// если каким то образом value ушел в минус, то где то ошибка
			// приравниваем к нулю и пишем в лог что такое случилось
			if ($new_value < 0) {

				Type_System_Admin::log("company dynamic", "dynamic key value < 0: key = $key");
				$new_value = 0;
			}

			Gateway_Db_CompanyData_CompanyDynamic::set($key, [
				"updated_at" => time(),
				"value"      => $new_value,
			]);
		} catch (\cs_RowIsEmpty | cs_RowNotUpdated) {
			Gateway_Db_CompanyData_CompanyDynamic::insert(new Struct_Db_CompanyData_CompanyDynamic($key, 0, time(), time()));
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * проверяем что переданный ключ существует
	 *
	 * @throws \parseException
	 */
	protected static function _throwIfBadDynamicKey(string $key):void {

		if (!in_array($key, self::ALLOWED_KEYS)) {
			throw new ParseFatalException("Not exist dynamic key");
		}
	}
}
