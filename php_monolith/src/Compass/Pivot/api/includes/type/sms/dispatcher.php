<?php

namespace Compass\Pivot;

/**
 * Класс для основной работы по отправке смс
 */
class Type_Sms_Dispatcher {

	// макс кол-во попыток проверки статуса смс у задачи
	protected const _MAX_TRIES_COUNT = 3;

	// интервал need_work для продюсера
	protected const _NEED_WORK_INTERVAL = 10;

	// лимит задач, которые берет продюсер за раз
	protected const _PRODUCER_LIMIT = 30;

	protected const _DB_KEY    = "pivot_sms_service";
	protected const _TABLE_KEY = "send_queue";

	/**
	 * Функция для получения задачи из базы
	 *
	 */
	public static function getList(int $bot_num, int $count = self::_PRODUCER_LIMIT):array {

		$offset = $bot_num * $count;
		$query  = "SELECT * FROM `?p` WHERE need_work <= ?i LIMIT ?i OFFSET ?i";

		return ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, time(), $count, $offset);
	}

	/**
	 * получаем количество задач в очереди
	 *
	 * @return int
	 */
	public static function getCountOfTasks():int {

		// запрос проверен на EXPLAIN(INDEX=cron_sms_dispatcher)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE true LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1);

		return $row["count"];
	}

	/**
	 * получаем количество задач в очереди для конкретного этапа
	 *
	 * @return int
	 */
	public static function getCountOfTasksByStage(int $stage):int {

		assertTestServer();

		// сервисный запрос используемый только в тестах, поэтому explain показывает что все плохо
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `stage` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $stage, 1);

		return $row["count"];
	}

	/**
	 * Обновляем запись с задачей после получения ее из базы
	 *
	 */
	public static function updateTaskList(array $send_sms_id_list, array $check_sms_id_list):void {

		// для этапа need_send обновляем только need_work
		$set = [
			"need_work" => time() + self::_NEED_WORK_INTERVAL,
		];
		count($send_sms_id_list) > 0 && ShardingGateway::database(self::_DB_KEY)
			->update("UPDATE `?p` SET ?u WHERE sms_id IN (?a) LIMIT ?i", self::_TABLE_KEY, $set, $send_sms_id_list, self::_PRODUCER_LIMIT);

		// для этапа need_send обновляем need_work и error_count
		$set["error_count"] = "error_count + 1";
		count($check_sms_id_list) > 0 && ShardingGateway::database(self::_DB_KEY)
			->update("UPDATE `?p` SET ?u WHERE sms_id IN (?a) LIMIT ?i", self::_TABLE_KEY, $set, $check_sms_id_list, self::_PRODUCER_LIMIT);
	}

	/**
	 * Выполняем задачу на этапе «нужна отправка смс»
	 *
	 * @throws cs_PhoneNumberNotFound
	 * @throws cs_SmsFailedRequestToProvider
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \cs_CurlError
	 */
	public static function doWorkOnStageNeedSend(Struct_PivotSmsService_SendQueue $send_queue):void {

		// запишем аналитику по смс
		$sms_analytics = Type_Sms_Analytics::getStructBySmsTask($send_queue);
		Type_Sms_Analytics::onReadFromRabbit($sms_analytics);

		if (self::_isExpiredTimeToWork($send_queue, $sms_analytics)) {
			return;
		}

		$provider_gateway = self::_getProvider($send_queue, $sms_analytics);

		if ($provider_gateway == "") {
			return;
		}

		try {
			$response = self::_sendSmsByProvider($provider_gateway, $send_queue, $sms_analytics);
		} catch (cs_PhoneNumberNotFound $e) {

			// если не нашли номер телефона и поймали экзепшен, то для пользовательского бота на стейдже и тестовом просто пропускаем
			if ($provider_gateway == Gateway_Sms_Provider_Userbot::class && (isStageServer() || isTestServer())) {

				Type_System_Admin::log("sms_send", ["Не нашли номер пользователя", $send_queue]);
				return;
			}

			// для остальных случаев прокидываем экзепшен дальше
			throw new $e();
		}

		if (isTestServer()) {
			Type_System_Admin::log("sms_send", ["Успех", $send_queue]);
		}
		self::_onSuccessDuringNeedSendStage($send_queue, $provider_gateway, $response);
	}

	/**
	 * при неудаче на этапе отправки смс
	 *
	 * @throws \queryException
	 */
	protected static function _onFailDuringNeedSendStage(Struct_PivotSmsService_SendQueue $send_queue, bool $is_need_delete_task):void {

		// пишем в историю
		Gateway_Db_PivotHistoryLogs_SendHistory::insert(
			$send_queue->sms_id, 0, $send_queue->created_at_ms, 0, 0, time(), $send_queue->provider_id, 0, [], $send_queue->extra
		);

		if ($is_need_delete_task) {

			// удаляем таску
			self::deleteTask($send_queue->sms_id);
		}
	}

	/**
	 * при успехе на этапе отправки смс
	 *
	 * @throws \queryException
	 * @mixed
	 */
	protected static function _onSuccessDuringNeedSendStage(Struct_PivotSmsService_SendQueue $send_queue, string|Gateway_Sms_Provider_Abstract $provider_gateway, Struct_Gateway_Sms_Provider_Response $response):void {

		$provider_sms_id   = $provider_gateway::getSmsIdFromResponse($response);
		$send_queue->extra = Type_Sms_Queue_Extra::setProviderSmsId($send_queue->extra, $provider_sms_id);

		// добавляем запись в историю
		$history_row_id = Gateway_Db_PivotHistoryLogs_SendHistory::insert(
			$send_queue->sms_id, 0, $send_queue->created_at_ms, $response->request_send_at_ms,
			0, time(), $provider_gateway::ID, $response->http_status_code, toJson($response), $send_queue->extra
		);

		// переводим задачу на STAGE_NEED_CHECK_STATUS_SMS
		$send_queue->extra = Type_Sms_Queue_Extra::setLastHistoryRowId($send_queue->extra, $history_row_id);
		Gateway_Db_PivotSmsService_SendQueue::update($send_queue->sms_id, [
			"stage"       => Type_Sms_Queue::STAGE_NEED_CHECK_STATUS_SMS,
			"updated_at"  => time(),
			"provider_id" => $provider_gateway::ID,
			"extra"       => Type_Sms_Queue_Extra::setProviderSmsId($send_queue->extra, $provider_sms_id),
		]);
	}

	/**
	 * Получить gateway-класс провайдера
	 *
	 * @throws cs_SmsNoAvailableProviders
	 * @throws \parseException
	 */
	protected static function _getProviderGateway(Struct_PivotSmsService_SendQueue $send_queue):Gateway_Sms_Provider_Abstract|string {

		// получаем исключенных из выборки провайдеров
		$excluded_provider_list = Type_Sms_Queue_Extra::getExcludedProviderList($send_queue->extra);

		// на стейдже отправляем смс через бота на паблик
		if (isStageServer()) {
			$send_queue->provider_id = Gateway_Sms_Provider_Userbot::ID;
		}

		return $send_queue->provider_id === "" ? Type_Sms_Provider_Choose::doAction($send_queue->phone_number, $excluded_provider_list)
			: Type_Sms_Provider::getGatewayById($send_queue->provider_id);
	}

	/**
	 * Выполняем задачу на этапе «нужна проверка статуса отправки»
	 *
	 * @throws \parseException
	 */
	public static function doWorkOnStageNeedCheckStatus(Struct_PivotSmsService_SendQueue $send_queue):void {

		// получаем статус отправки смс
		$provider_gateway = Type_Sms_Provider::getGatewayById($send_queue->provider_id);
		$response         = $provider_gateway::getSmsStatus(Type_Sms_Queue_Extra::getProviderSmsId($send_queue->extra));
		$status           = $provider_gateway::getSmsStatusFromResponse($response);

		$sms_analytics = Type_Sms_Analytics::getStructBySmsTask($send_queue);

		// если успешная отправка sms
		if ($status === Gateway_Sms_Provider_Abstract::STATUS_DELIVERED) {

			Type_Sms_Analytics::onProviderReturnSuccess($sms_analytics, $response->body, $status);
			self::_onSuccessDuringNeedCheckStage($send_queue, $provider_gateway, $response);
			return;
		}

		// если неуспешная отправка sms или на протяжении _MAX_TRIES_COUNT итераций статус задачи не сдвинулся с IN_PROGRESS
		if ($status === Gateway_Sms_Provider_Abstract::STATUS_NOT_DELIVERED
			|| ($status === Gateway_Sms_Provider_Abstract::STATUS_IN_PROGRESS && $send_queue->error_count >= self::_MAX_TRIES_COUNT)) {

			self::_doWithMaxCount($status, $response, $send_queue, $sms_analytics);
		}
	}

	/**
	 * при успехе на этапе проверки статуса отправки смс
	 *
	 * @mixed
	 */
	protected static function _onSuccessDuringNeedCheckStage(Struct_PivotSmsService_SendQueue $send_queue, string|Gateway_Sms_Provider_Abstract $provider_gateway, Struct_Gateway_Sms_Provider_Response $response):void {

		// обновляем запись в истории
		Gateway_Db_PivotHistoryLogs_SendHistory::update(Type_Sms_Queue_Extra::getLastHistoryRowId($send_queue->extra), [
			"is_success"     => 1,
			"sms_sent_at_ms" => $provider_gateway::getSmsSentAtFromResponse($response),
		]);

		// удаляем задачу
		self::deleteTask($send_queue->sms_id);
	}

	/**
	 * Меняем этап задачи на need_send_sms, чтобы попытаться отправить сообщение снова
	 *
	 */
	protected static function _changeStageToNeedSend(Struct_PivotSmsService_SendQueue $send_queue):void {

		// отправляем задачу в статус need_send_sms
		$send_queue->extra = Type_Sms_Queue_Extra::setExcludedProviderList($send_queue->extra, array_merge(
			Type_Sms_Queue_Extra::getExcludedProviderList($send_queue->extra),
			[$send_queue->provider_id]
		));

		Gateway_Db_PivotSmsService_SendQueue::update($send_queue->sms_id, [
			"stage"       => Type_Sms_Queue::STAGE_NEED_SEND_SMS,
			"updated_at"  => time(),
			"provider_id" => "",
			"extra"       => $send_queue->extra,
		]);
	}

	/**
	 * Проверим имеет ли смысл решать задачу
	 *
	 * @return bool
	 * @throws \queryException
	 */
	protected static function _isExpiredTimeToWork(Struct_PivotSmsService_SendQueue $send_queue, Struct_Sms_Analytics $sms_analytics):bool {

		// проверяем, вдруг задачу уже несвоевременно решать (expires_at)
		if ($send_queue->expires_at < time()) {

			if (isTestServer()) {
				Type_System_Admin::log("sms_send", ["Вышли в 1 кейсе", $send_queue]);
			}
			Type_Sms_Analytics::onExpiredTimeToSend($sms_analytics);
			self::_onFailDuringNeedSendStage($send_queue, true);
			return true;
		}

		return false;
	}

	/**
	 * Получим свободного провайдера
	 *
	 * @return Gateway_Sms_Provider_Abstract|string
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _getProvider(Struct_PivotSmsService_SendQueue $send_queue, Struct_Sms_Analytics $sms_analytics):Gateway_Sms_Provider_Abstract|string {

		try {
			$provider_gateway = self::_getProviderGateway($send_queue);
		} catch (cs_SmsNoAvailableProviders) {

			Type_Sms_Analytics::onFreeProviderNotFound($sms_analytics);

			if (isTestServer()) {
				Type_System_Admin::log("sms_send", ["Вышли в 2 кейсе", $send_queue]);
			}
			self::_onFailDuringNeedSendStage($send_queue, false);
			return "";
		}

		Type_Sms_Analytics::onFreeProviderFound($sms_analytics, $provider_gateway::ID);

		return $provider_gateway;
	}

	/**
	 * Выполним действия если слишком много попыток на отправку смс
	 */
	protected static function _doWithMaxCount(string $status, Struct_Gateway_Sms_Provider_Response $response, Struct_PivotSmsService_SendQueue $send_queue, Struct_Sms_Analytics $sms_analytics):void {

		Type_Sms_Analytics::onProviderNotSentSms($sms_analytics, $response->body, $status);

		// обновляем запись в истории
		Gateway_Db_PivotHistoryLogs_SendHistory::update(Type_Sms_Queue_Extra::getLastHistoryRowId($send_queue->extra), [
			"is_success" => 0,
		]);

		// если task_expired_at не превышен
		if ($send_queue->expires_at < time()) {

			self::_changeStageToNeedSend($send_queue);
			return;
		}

		Type_Sms_Analytics::onDeletedTaskBecauseManyError($sms_analytics);

		// иначе избавляемся от задачи
		self::deleteTask($send_queue->sms_id);
	}

	/**
	 * Отправим смс и получим ответ
	 *
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider
	 * @throws \queryException
	 */
	protected static function _sendSmsByProvider(Gateway_Sms_Provider_Abstract|string $provider_gateway, Struct_PivotSmsService_SendQueue $send_queue, Struct_Sms_Analytics $sms_analytics):Struct_Gateway_Sms_Provider_Response {

		try {

			// отправляем сообщение и получаем sms_id в контексте провайдера
			$response = $provider_gateway::sendSms($send_queue->phone_number, $send_queue->text);
		} catch (cs_SmsFailedRequestToProvider $response_exception) {

			Type_Sms_Analytics::onNoSentSms($sms_analytics, $response_exception->getResponse()->body, $response_exception->getResponse()->http_status_code);
			throw new $response_exception($response_exception->getResponse());
		} catch (\cs_CurlError $e) {

			// пишем в историю
			Gateway_Db_PivotHistoryLogs_SendHistory::insert(
				$send_queue->sms_id, 0, $send_queue->created_at_ms, 0, 0, time(), $send_queue->provider_id, 0, [], $send_queue->extra
			);

			// шлем аналитику
			Type_Sms_Analytics::onNoSentSms($sms_analytics, "curl_error", 500);

			// пробрасываем исключение дальше
			throw $e;
		}

		Type_Sms_Analytics::onSentSms($sms_analytics, $response->body, $response->http_status_code);

		return $response;
	}

	/**
	 * Удаляем задачу
	 *
	 */
	public static function deleteTask(string $sms_id):void {

		ShardingGateway::database(self::_DB_KEY)
			->delete("DELETE FROM `?p` WHERE sms_id = ?s LIMIT ?i", self::_TABLE_KEY, $sms_id, 1);
	}

}