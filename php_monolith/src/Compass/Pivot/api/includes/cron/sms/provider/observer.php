<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * крон для наблюдения за провайдерами смс
 */
class Cron_Sms_Provider_Observer extends \Cron_Default {

	// интервал need_work для продюсера
	protected const _NEED_WORK_INTERVAL = 600;

	// лимит задач, которые берет продюсер за раз
	protected const _PRODUCER_LIMIT = 30;

	protected const _DB_KEY    = "pivot_sms_service";
	protected const _TABLE_KEY = "observer_provider";

	/**
	 * Получаем задачи из очереди в таблице
	 */
	public function work():void {

		// если не паблик-сервер, то стопим выполнение
		if (!ServerProvider::isProduction()) {
			return;
		}

		// получаем задачи из базы
		$list = $this->_getList();

		// проверям может задачи нет
		if (count($list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// формируем in
		$in = $this->_makeIn($list);

		// обновляем задачи по need_work и увеличиваем error_count
		$this->_updateTaskList($in);

		// отправляем задачу в doWork
		$this->_sendToRabbit($list);
	}

	/**
	 * Функция для получения задачи из базы
	 */
	protected function _getList():array {

		$offset = $this->bot_num * self::_PRODUCER_LIMIT;
		$query  = "SELECT * FROM `?p` WHERE `need_work` <= ?i LIMIT ?i OFFSET ?i";

		return ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, time(), self::_PRODUCER_LIMIT, $offset);
	}

	/**
	 * Формируем массива для обновления задач
	 */
	protected function _makeIn(array $list):array {

		$in = [];
		foreach ($list as $v) {
			$in[] = $v["provider_id"];
		}

		return $in;
	}

	/**
	 * Обновляем запись с задачей после получения ее из базы
	 */
	protected function _updateTaskList(array $in):void {

		// для этапа need_send обновляем только need_work
		$set = [
			"need_work" => time() + self::_NEED_WORK_INTERVAL,
		];

		ShardingGateway::database(self::_DB_KEY)
			->update("UPDATE `?p` SET ?u WHERE `provider_id` IN (?a) LIMIT ?i", self::_TABLE_KEY, $set, $in, self::_PRODUCER_LIMIT);
	}

	/**
	 * Отправляем задачу консамеру
	 */
	protected function _sendToRabbit(array $list):void {

		foreach ($list as $v) {

			// отправляем задачу consumer
			$this->doQueue($v);
		}
	}

	/**
	 * Выполняем задачу консамером
	 *
	 * @throws \parseException
	 * @throws \cs_RowIsEmpty
	 */
	public function doWork(array $row):void {

		$observe_provider_task = $this->_convertRowToStruct($row);

		// получаем запись о провайдере
		$provider = Gateway_Db_PivotSmsService_ProviderList::getById($observe_provider_task->provider_id);

		// проверяем, есть ли конфигурация о сервере, если надо удаляем
		if (!$this->_isConfigExist($observe_provider_task->provider_id)) {

			if ($provider->is_deleted != 1) {
				$this->_disableProvider($observe_provider_task->provider_id);
			}
			return;
		}

		// если провайдер был удален, но его конфиг вернули
		if ($provider->is_deleted == 1) {
			$this->_enableProvider($provider->provider_id);
		}

		// получаем баланс провайдера
		$provider_gateway = Type_Sms_Provider::getGatewayById($provider->provider_id);
		$response         = $provider_gateway::getBalance();
		$balance          = $provider_gateway::getBalanceValueFromResponse($response);

		// триггерим, если баланс опустился ниже
		$this->_triggerIfBalanceIsLowered($provider, $observe_provider_task, $provider_gateway, $balance);
	}

	/**
	 * Если конфиг не существует
	 */
	protected function _isConfigExist(string $provider_id):bool {

		$config = Type_Sms_Config::get();
		return isset($config[$provider_id]);
	}

	/**
	 * Проверяем существование конфигурации провайдера
	 *
	 * @param string $provider_id
	 */
	protected function _disableProvider(string $provider_id):void {

		// оповещаем
		Gateway_Notice_Sender::sendGroup(SMS_EXCEPTION, formatArgs([
			"text"         => "Отключили провайдера",
			"provider_id"  => $provider_id,
			"is_available" => 0,
			"is_deleted"   => 1,
			"updated_at"   => time(),
		]));

		// помечаем в базе провайдера удаленным
		Gateway_Db_PivotSmsService_ProviderList::update($provider_id, [
			"is_available" => 0,
			"is_deleted"   => 1,
			"updated_at"   => time(),
		]);
	}

	/**
	 * Активируем провайдера
	 */
	protected function _enableProvider(string $provider_id):void {

		// оповещаем
		Gateway_Notice_Sender::sendGroup(SMS_EXCEPTION, formatArgs([
			"text"         => "Включили провайдер",
			"provider_id"  => $provider_id,
			"is_available" => 0,
			"is_deleted"   => 1,
			"updated_at"   => time(),
		]));

		// помечаем в базе провайдера активированным
		Gateway_Db_PivotSmsService_ProviderList::update($provider_id, [
			"is_available" => 1,
			"is_deleted"   => 0,
			"updated_at"   => time(),
		]);
	}

	/**
	 * Проверяем баланс провайдера
	 */
	protected function _triggerIfBalanceIsLowered(Struct_PivotSmsService_Provider $provider, Struct_PivotSmsService_ObserverProvider $observe_provider_task, string $provider_gateway, int $balance):void {

		// если баланс выше минимальной отметки для триггера, то проверяем, не надо ли включить провайдера
		/** @var Gateway_Sms_Provider_Abstract $provider_gateway */
		if ($balance > $provider_gateway::getTriggerMinBalanceValue()) {

			// если провайдер доступен, то ничего не делаем
			if ($provider->is_available === 1) {
				return;
			}

			// иначе включаем снова
			Gateway_Db_PivotSmsService_ProviderList::update($provider->provider_id, [
				"is_available" => 1,
				"updated_at"   => time(),
			]);

			return;
		}

		// подсчитываем количество корректных/некорректных API запросов с момента прошлой проверки
		[$success_count, $failed_count] = $this->_countStatisticsOfSentSms($observe_provider_task);

		// считаем количество отправленных смс
		$total_count_sent_message = $success_count + $failed_count;

		// оповещаем
		$to_monitoring = [
			"text"                       => "Кончился баланс",
			"provider"                   => $provider->provider_id,
			"time"                       => time(),
			"balance"                    => $balance,
			"total_count_sent_message"   => $total_count_sent_message,
			"success_count_sent_message" => $success_count,
			"failed_count_sent_message"  => $failed_count,
		];
		Gateway_Notice_Sender::sendGroup(SMS_EXCEPTION, formatArgs($to_monitoring));
	}

	/**
	 * подсчитываем количество корректных/некорректных API запросов с момента прошлой проверки
	 *
	 * @return int[]
	 */
	protected function _countStatisticsOfSentSms(Struct_PivotSmsService_ObserverProvider $observer_provider_row):array {

		// достаем из extra – на каком row_id историй отправки смс остановились в прошлый раз
		$last_history_row_id = Type_Sms_Provider_Observer::getLastProcessedHistoryRowId($observer_provider_row->extra);

		// получаем историю из следующих 50 отправленных смс этим провайдером
		$history_of_sent_sms = Gateway_Db_PivotHistoryLogs_SendHistory::getByProviderAndOffset($observer_provider_row->provider_id, $last_history_row_id, 50);

		// если пусто, то отдаем по нулям
		if (count($history_of_sent_sms) === 0) {
			return [0, 0];
		}

		// считаем кол-во успешных и проваленных
		$success_count = 0;
		$failed_count  = 0;
		foreach ($history_of_sent_sms as $history_row) {
			$history_row->is_success === 1 ? $success_count++ : $failed_count++;
		}

		// получаем последний history_row_id и сохраняем в базу
		$last_history_row_id          = $history_of_sent_sms[count($history_of_sent_sms) - 1]->row_id;
		$observer_provider_row->extra = Type_Sms_Provider_Observer::setLastProcessedHistoryRowId($observer_provider_row->extra, $last_history_row_id);
		Gateway_Db_PivotSmsService_ObserverProvider::update($observer_provider_row->provider_id, [
			"extra" => $observer_provider_row->extra,
		]);

		return [$success_count, $failed_count];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Конвертируем запись из базы данных в объект структуры
	 */
	protected function _convertRowToStruct(array $row):Struct_PivotSmsService_ObserverProvider {

		return new Struct_PivotSmsService_ObserverProvider(
			$row["provider_id"],
			$row["created_at"],
			$row["need_work"],
			fromJson($row["extra"])
		);
	}

	/**
	 * Возвращает экземпляр Rabbit для указанного ключа.
	 */
	protected static function _getBusInstance(string $bus_key):\Rabbit {

		return ShardingGateway::rabbit($bus_key);
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "pivot_" . parent::_resolveBotName();
	}
}