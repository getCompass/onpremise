<?php

namespace Compass\Announcement;

/**
 * Крон для повторной отправки анонсов
 */
class Cron_ResendAnnouncement extends \Cron_Default {

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
		$time   = time();

		$output = [];

		// собираем все активные анонсы которые должны повторяться
		foreach (Gateway_Db_AnnouncementUser_UserAnnouncement::getAllTableShards() as $table_shard) {

			// получаем все связи анонсов для обновления
			$tmp = Gateway_Db_AnnouncementUser_UserAnnouncement::getToResend($table_shard, $time, self::_PRODUCER_LIMIT, $offset);

			foreach ($tmp as $user_announcement) {
				$output[$user_announcement->announcement_id][] = $user_announcement->user_id;
			}
		}

		return $output;
	}

	/**
	 * Функция для отправки задачи в doWork
	 *
	 * @param array $list
	 */
	protected function _sendToRabbit(array $list):void {

		// отправляем задачу на doWork
		$this->doQueue($list);
	}

	/**
	 * Функция для выполнения задач
	 * @noinspection PhpUnused
	 */
	public function doWork(array $item):void {

		foreach ($item as $announcement_id => $user_id_list) {

			Gateway_Bus_SenderBalancer::announcementPublished($user_id_list);
			Gateway_Db_AnnouncementUser_UserAnnouncement::updateNextResendAttemptedAt($announcement_id, $user_id_list, time());
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

		return "announcement_" . parent::_resolveBotName();
	}
}
