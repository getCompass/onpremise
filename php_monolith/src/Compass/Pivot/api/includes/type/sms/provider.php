<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с смс-провайдерами
 */
class Type_Sms_Provider {

	public const ASSOC_GATEWAY_CLASS_BY_ID = [
		"sms_agent_alphanumeric_v1" => Gateway_Sms_Provider_SmsAgent::class,
		"vonage_alphanumeric_v1"    => Gateway_Sms_Provider_Vonage::class,
		"twilio_alphanumeric_v1"    => Gateway_Sms_Provider_Twilio::class,
		"userbot_v1"                => Gateway_Sms_Provider_Userbot::class,
		"idigital_alphanumeric_v1"  => Gateway_Sms_Provider_Idigital::class,
	];

	/**
	 * Создаем сущность провайдера
	 *
	 */
	public static function create(string $provider_id):Struct_PivotSmsService_Provider {

		$provider = new Struct_PivotSmsService_Provider($provider_id, 1, 0, time(), 0, Type_Sms_Provider_Extra::init());

		// создаем основную сущность в provider_list
		Gateway_Db_PivotSmsService_ProviderList::insert($provider);

		// добавляем задачу на наблюдение за провайдером
		Gateway_Db_PivotSmsService_ObserverProvider::insert($provider_id, 0, time(), Type_Sms_Provider_Observer::initExtra());

		return $provider;
	}

	/**
	 * Получить класс обработчик провайдера по его идентификатору
	 *
	 * @return string|Gateway_Sms_Provider_Abstract
	 *
	 * @throws \parseException
	 * @mixed
	 */
	public static function getGatewayById(string $provider_id) {

		if (!isset(self::ASSOC_GATEWAY_CLASS_BY_ID[$provider_id])) {
			throw new ParseFatalException(__METHOD__ . ": gateway class not defined for provider: " . $provider_id);
		}

		return self::ASSOC_GATEWAY_CLASS_BY_ID[$provider_id];
	}
}