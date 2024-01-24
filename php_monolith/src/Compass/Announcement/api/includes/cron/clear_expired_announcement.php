<?php

namespace Compass\Announcement;

/**
 * Крон для удаления истекших анонсов
 */
class Cron_ClearExpiredAnnouncement extends \Cron_Default {

	protected const _PRODUCER_LIMIT = 20; // лимит записей за раз

	protected string $queue_prefix = "_" . CURRENT_MODULE;
	protected int    $memory_limit = 50;
	protected int    $sleep_time   = 1;

	/**
	 * Основной вызов для продьюсера задач.
	 */
	public function work() {

		// получаем задачи из базы
		$list = $this->_getList();

		// проверяем может задачи нет
		if (count($list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// отправляем задачу в doWork
		$this->_sendToRabbit($list);
		$this->sleep($this->sleep_time);
	}

	/**
	 * Функция для получения задачи из базы
	 * @return array
	 */
	protected function _getList():array {

		$offset = $this->bot_num * self::_PRODUCER_LIMIT;

		$active_status_list = Domain_Announcement_Entity::getActiveStatuses();
		return Gateway_Db_AnnouncementMain_Announcement::getExpired(time(), $active_status_list, self::_PRODUCER_LIMIT, $offset);
	}

	/**
	 * Функция для отправки задачи в doWork
	 *
	 * @param array $list
	 */
	protected function _sendToRabbit(array $list):void {

		foreach ($list as $item) {

			// отправляем задачу на doWork
			$this->doQueue($item);
		}
	}

	/**
	 * Функция для выполнения задач
	 * @noinspection PhpUnused
	 */
	public function doWork(array $item):void {

		$this->write("удаляю истекшие анонсы");

		// выполняем задачу локально
		Gateway_Db_AnnouncementMain_Announcement::delete($item["announcement_id"]);
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

		return "announcement_" . parent::_resolveBotName();
	}
}
