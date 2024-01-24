<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Абстрактный класс описывающий класс каждого API-шлюза к смс провайдеру
 */
abstract class Gateway_Sms_Provider_Abstract {

	public const ID = "";

	public const STATUS_IN_PROGRESS   = 0;
	public const STATUS_DELIVERED     = 1;
	public const STATUS_NOT_DELIVERED = 8;

	// таймаут для запроса к провайдеру
	public const PROVIDER_TIMEOUT = 10;

	/**
	 * Отправить смс сообщение на номер с переданным текстом сообщения
	 *
	 * @param string $phone_number Номер телефона получатели
	 * @param string $text         Отправляемый текст сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response Идентификатор отправленного сообщения
	 */
	abstract public static function sendSms(string $phone_number, string $text):Struct_Gateway_Sms_Provider_Response;

	/**
	 * Получить информацию об отправленном ранее сообщении
	 *
	 * @param string $sms_id Идентификатор сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response Ответ от провайдера
	 */
	abstract public static function getSmsStatus(string $sms_id):Struct_Gateway_Sms_Provider_Response;

	/**
	 * Получить значение остатка средства на балансе провайдера в валюте самого провайдера
	 *
	 */
	abstract public static function getBalance():Struct_Gateway_Sms_Provider_Response;

	/**
	 * Получить sms_id отправленного сообщения из тела ответа
	 *
	 */
	abstract public static function getSmsIdFromResponse(Struct_Gateway_Sms_Provider_Response $response):string;

	/**
	 * Получить статус отправленного сообщения из тела ответа
	 *
	 */
	abstract public static function getSmsStatusFromResponse(Struct_Gateway_Sms_Provider_Response $response):int;

	/**
	 * Получить временную метку, когда сообщение было отправлено провайдером оператору
	 *
	 */
	abstract public static function getSmsSentAtFromResponse(Struct_Gateway_Sms_Provider_Response $response):int;

	/**
	 * Получить целочисленного значение баланса из тела ответа
	 *
	 */
	abstract public static function getBalanceValueFromResponse(Struct_Gateway_Sms_Provider_Response $response):int;

	/**
	 * Получить целочисленное число, минимальный порог баланса провайдера для его дальнейшего отключения
	 *
	 * @throws \parseException
	 */
	public static function getTriggerMinBalanceValue():int {

		$config = self::_getConfig();
		return $config["min_balance_value"];
	}

	/**
	 * Получить конфигурацию провайдера
	 *
	 * @throws \parseException
	 */
	protected static function _getConfig():array {

		$config = Type_Sms_Config::get();
		if (!isset($config[static::ID])) {
			throw new ParseFatalException(sprintf("%s: config-file not exist for sms-provider [%s]", __METHOD__, static::ID));
		}

		return $config[static::ID];
	}
}