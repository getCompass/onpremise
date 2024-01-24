<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с аналитикой
 */
class Type_Sms_Analytics {

	/**
	 * ключ логируемого события
	 */
	protected const _EVENT_KEY = "analytics_sms";

	/**
	 * список всех логируемых статусов по задаче с отправкой смс
	 */
	protected const _STATUS_SEND_RABBIT                               = 1; // отправили задачу в очередь
	protected const _STATUS_READ_FROM_RABBIT                          = 2; // крон взял задачу из очереди
	protected const _STATUS_EXPIRE_TIME_TO_SEND                       = 3; // у крона вышло время на отправку смс
	protected const _STATUS_NO_AVAILABLE_PROVIDERS                    = 4; // нет доступных провайдеров для отправки смс
	protected const _STATUS_GET_FREE_PROVIDER                         = 5; // определили провайдер для отправки смс
	protected const _STATUS_NO_SEND_SMS                               = 6; // смс не отправлено
	protected const _STATUS_SEND_SMS                                  = 7; // смс отправлено
	protected const _STATUS_PROVIDER_RETURN_SUCCESS                   = 8; // провайдер успешно отправил смс
	protected const _STATUS_USER_USE_SMS                              = 9; // пользователь воспользовался смс
	protected const _STATUS_PROVIDER_NOT_SEND_SMS                     = 10; // провайдер не отправил смс
	protected const _STATUS_PROVIDER_RESEND_SMS                       = 11; // провайдер попытался самостоятельно переотправить смс
	protected const _STATUS_DELETE_TASK_BECAUSE_MANY_ERROR            = 12; // удалили задачу на отправку, так как
	protected const _STATUS_USER_RESEND_SMS                           = 100; // [статус для flow] пользователь самостоятельно запросил переотправку смс
	protected const _STATUS_SEND_RABBIT_FOR_SECOND_STAGE_CHANGE_PHONE = 101; // [статус для flow] отправили задачу в очередь на втором шаге смены номера телефона
	protected const _STATUS_STORY_EXPIRED                             = 102; // [статус для flow] действие протухло

	/**
	 * Создать структуру на основе задачи на отправку смс
	 *
	 * @return Struct_Sms_Analytics
	 */
	public static function getStructBySmsTask(Struct_PivotSmsService_SendQueue $send_queue):Struct_Sms_Analytics {

		return self::getStruct(
			$send_queue->sms_id,
			$send_queue->phone_number,
			$send_queue->expires_at,
			Type_Sms_Queue_Extra::getStoryType($send_queue->extra),
			Type_Sms_Queue_Extra::getStoryId($send_queue->extra),
		);
	}

	/**
	 * Создать структуру из данных
	 */
	public static function getStruct(string $sms_id, string $phone_number, int $expires_at, int $story_type, string $story_map, int $user_id = 0, int $company_id = 0, string $sms_provider_id = "", string $phone_number_operator = ""):Struct_Sms_Analytics {

		// хэшируем мапу, чтобы не палить ее в чистом виде
		$story_id = sha1($story_map);

		return new Struct_Sms_Analytics(
			generateGUID(),
			$user_id,
			$company_id,
			$story_type,
			$story_id,
			$sms_id,
			self::doPhoneToSecret($phone_number),
			time(),
			$expires_at,
			0,
			"",
			$sms_provider_id,
			"",
			$phone_number_operator,
		);
	}

	/**
	 * Поставим статус что отдали задачу в рэббит
	 */
	public static function onSentToRabbit(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_SEND_RABBIT);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что прочитали задачу из рэббит
	 */
	public static function onReadFromRabbit(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$fill_sms_analytics = self::doFillAnalytics($sms, self::_STATUS_READ_FROM_RABBIT);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $fill_sms_analytics);
	}

	/**
	 * Поставим статус что исстекло время задачи
	 */
	public static function onExpiredTimeToSend(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_EXPIRE_TIME_TO_SEND);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что нет доступных провайдеров
	 */
	public static function onFreeProviderNotFound(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_NO_AVAILABLE_PROVIDERS);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что получили свободного провайдера
	 */
	public static function onFreeProviderFound(Struct_Sms_Analytics $sms, string $sms_provider_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_GET_FREE_PROVIDER, sms_provider_id: $sms_provider_id);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что не отправили смс
	 */
	public static function onNoSentSms(Struct_Sms_Analytics $sms, int|string|array $response_provider, int $status_code_response):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_NO_SEND_SMS, self::doResponseProviderToString($response_provider), $status_code_response);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что отправили смс
	 */
	public static function onSentSms(Struct_Sms_Analytics $sms, int|string|array $response_provider, int $status_code_response):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_SEND_SMS, self::doResponseProviderToString($response_provider), $status_code_response);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что провайдер подтвердил отправку смс
	 */
	public static function onProviderReturnSuccess(Struct_Sms_Analytics $sms, int|string|array $response_provider, int $status_code_response):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_PROVIDER_RETURN_SUCCESS, self::doResponseProviderToString($response_provider), $status_code_response);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что пользователь воспользовался смс
	 */
	public static function onUserUseSms(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_USER_USE_SMS);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что провайдер не смог отправить смс
	 */
	public static function onProviderNotSentSms(Struct_Sms_Analytics $sms, int|string|array $response_provider, int $status_code_response):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_PROVIDER_NOT_SEND_SMS, self::doResponseProviderToString($response_provider), $status_code_response);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что задачу по отправке удалили так как много ошибок
	 */
	public static function onDeletedTaskBecauseManyError(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_DELETE_TASK_BECAUSE_MANY_ERROR);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что провайдер переотправил смс
	 */
	public static function onProviderResendSms(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_PROVIDER_RESEND_SMS);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что провайдер переотправил смс
	 */
	public static function onUserResendSms(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_USER_RESEND_SMS);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что провайдер переотправил смс
	 */
	public static function onSendSmsForSecondStageChangePhone(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_SEND_RABBIT_FOR_SECOND_STAGE_CHANGE_PHONE);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * Поставим статус что действие протухло
	 */
	public static function onStoryExpire(Struct_Sms_Analytics $sms):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$sms = self::doFillAnalytics($sms, self::_STATUS_STORY_EXPIRED);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add(self::_EVENT_KEY, (array) $sms);
	}

	/**
	 * подменяем содержимое конфига
	 * работает только в тестах!
	 *
	 * @throws \parseException
	 */
	public static function substituteConfig(array $config):void {

		assertTestServer();

		// подменяем содержимое конфиг-файла, чтобы в будущем возвращать именно его
		$GLOBALS[self::class] = $config;
	}

	/**
	 * Заполним аналитику
	 *
	 * @return Struct_Sms_Analytics
	 */
	protected static function doFillAnalytics(Struct_Sms_Analytics $sms, int $type_id, string $response_provider = "", int $status_code_response = 0, string $sms_provider_id = ""):Struct_Sms_Analytics {

		$sms->type_id         = $type_id;
		$sms->created_at      = time();
		$sms->uuid            = generateGUID();
		$sms->status_provider = $status_code_response;
		$sms->status_response = $response_provider;
		$sms->sms_provider_id = $sms_provider_id;

		return $sms;
	}

	/**
	 * Функция скрытия номера
	 *
	 * @param string $phone
	 *
	 * @return string
	 */
	public static function doPhoneToSecret(string $phone):string {

		// количество симвовлов которые покажем сначала
		$visibleCount = 5;

		// количество символов покажем с конца
		$endVisibleCount = -2;

		// количество скрытых сиволов
		$hiddenCount = strlen($phone) - ($visibleCount + abs($endVisibleCount));

		return substr($phone, 0, $visibleCount) . str_repeat("*", $hiddenCount) . substr($phone, $endVisibleCount);
	}

	/**
	 * Функция перевода ответа провайдера в json
	 *
	 * @param int|string|array $response_provider
	 *
	 * @return string
	 */
	protected static function doResponseProviderToString(int|string|array $response_provider):string {

		$response = [
			"response" => $response_provider,
		];

		return toJson($response);
	}
}