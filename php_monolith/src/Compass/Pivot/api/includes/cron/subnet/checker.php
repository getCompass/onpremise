<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\GatewayException;

/**
 * Крон для проверки подсетей
 */
class Cron_Subnet_Checker extends \Cron_Default {

	// лимит задач, которые берет продюсер за раз
	protected const _PRODUCER_LIMIT = 30;

	// интервал need_work для продюсера
	protected const _NEED_WORK_INTERVAL = 20;

	/**
	 * Получаем задачи из очереди в таблице
	 */
	public function work():void {

		// получаем задачи из базы
		$list = Gateway_Db_PivotSystem_Subnet24CheckList::getNextWorkList(self::_PRODUCER_LIMIT, $this->bot_num * self::_PRODUCER_LIMIT);

		// проверям может задачи нет
		if (count($list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// обновляем задачи по need_work и увеличиваем error_count
		$in = [];
		foreach ($list as $item) {
			$in[] = $item->subnet_24;
		}
		self::_updateTaskList($in, self::_NEED_WORK_INTERVAL);

		// отправляем задачу в doWork
		$this->_sendToRabbit($list);
	}

	/**
	 * Обновляем список задач после того как они были взяты в работу
	 */
	protected static function _updateTaskList(array $subnet_24_list, int $need_work_interval):void {

		Gateway_Db_PivotSystem_Subnet24CheckList::updateList($subnet_24_list, [
			"need_work"  => time() + $need_work_interval,
			"updated_at" => time(),
		]);
	}

	/**
	 * Отправляем задача консамеру
	 *
	 * @param Struct_Db_PivotSystem_Subnet24CheckList[] $list
	 */
	protected function _sendToRabbit(array $list):void {

		// отправляем задачу consumer
		$this->doQueue($list);
	}

	/**
	 * Выполняем задачу консамером
	 *
	 * @param array $list
	 *
	 * @throws GatewayException
	 * @throws \cs_CurlError
	 */
	public function doWork(array $list):void {

		$subnet_list = [];
		$task_list   = [];
		foreach ($list as $row) {

			$row["extra"]                         = toJson($row["extra"]);
			$task                                 = Struct_Db_PivotSystem_Subnet24CheckList::rowToStruct($row);
			$subnet_list[]                        = long2ip($task->subnet_24);
			$task_list[long2ip($task->subnet_24)] = $task;
		}

		$ip_checker  = new Gateway_Ip_Checker();
		$result_list = $ip_checker->batchCheckSubnet24($subnet_list);

		// проходим по каждому ip
		foreach ($result_list as $result) {

			// скипаем такую проверку и пишем лог
			if (!isset($result["status"]) || $result["status"] !== "success") {

				Type_System_Admin::log("subnet_check_failed", ["subnet_list" => $subnet_list, "result" => $result]);
				continue;
			}

			$subnet_24      = $result["query"];
			$subnet_24_long = Domain_Subnet_Entity_Ip::getIp2LongSubnet24($subnet_24);
			$is_mobile      = (int) $result["mobile"];
			$is_proxy       = (int) $result["proxy"];
			$is_hosting     = (int) $result["hosting"];
			$country_code   = (string) $result["countryCode"];
			$as             = (string) $result["as"];

			// сохраняем результат
			Gateway_Db_PivotSystem_Subnet24ResultList::insert($subnet_24_long, $is_mobile, $is_proxy, $is_hosting, $country_code, $as);

			// обновляем таблицу проверки
			$extra = $task_list[$subnet_24]->extra;
			$extra = Domain_Subnet_Entity_Check::setResponse($extra, $result);
			Gateway_Db_PivotSystem_Subnet24CheckList::update($subnet_24_long, [
				"status" => Domain_Subnet_Entity_Check::STATUS_CHECKED,
				"extra"  => $extra,
			]);
		}
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