<?php

namespace Compass\FileNode;

/**
 * Класс крона, который производит обработку изображений после их загрузки на файловую ноду
 */
class Cron_Postupload_Image extends \Cron_Default {

	protected const _MAX_ERROR_COUNT   = 3; // максимальное количество ошибок
	protected const _PRODUCER_INTERVAL = 60 * 2; // интервал продюсера
	protected const _PRODUCER_LIMIT    = 50;

	// параметры крона
	protected int $sleep_time   = 0;
	protected int $memory_limit = 50;

	public function work():void {

		// получаем задачи из базы
		$list = Gateway_Db_FileNode_PostUpload::getListForWork(FILE_TYPE_IMAGE, self::_PRODUCER_LIMIT, $this->bot_num * self::_PRODUCER_LIMIT);

		// проверяем, что нам вернулись задачи
		if (count($list) < 1) {

			$this->say("Записей нет, спим секунду");
			$this->sleep(1);
			return;
		}

		// выводим что получили задачу в работу
		$this->say(sprintf("Получил %d задач в работу", count($list)));

		// обновляем need_work у всех задач
		$this->_doUpdateTaskList($list);

		// отдаем задачи в rabbit
		$this->_sendToRabbit($list);

		$this->sleep(0);
	}

	// обновляем список задач
	protected function _doUpdateTaskList(array $list):void {

		// формируем IN для обновления need_work
		$in = formatIn($list, "queue_id");

		// обновляем need_work взятым задачам
		$set = [
			"error_count" => "error_count + 1",
			"need_work"   => time() + self::_PRODUCER_INTERVAL,
		];
		Gateway_Db_FileNode_PostUpload::updateList($in, $set);
	}

	// функция для отправки задачи в doWork
	protected function _sendToRabbit(array $list):void {

		// отдаем задачи в rabbit
		foreach ($list as $item) {

			// не удалось обработать видео - удаляем его
			if ($item["error_count"] > self::_MAX_ERROR_COUNT) {

				Gateway_Db_FileNode_PostUpload::delete($item["queue_id"]);
				continue;
			}

			$this->doQueue($item);
			$this->say("Отдал в Rabbit задачу [{$item["queue_id"]}]");
		}
	}

	// обрабатываем
	public function doWork(array $item):void {

		// проверяем что тип файла image
		if ($item["file_type"] != FILE_TYPE_IMAGE) {

			throw new \returnException(__METHOD__ . ": got file with type {$item["file_type"]}, but expected IMAGE");
		}

		// проверяем не удален ли файл (а вдруг)
		$file_row = Gateway_Db_FileNode_File::getOne($item["file_key"]);

		// если вдруг удален - выходим
		if ($file_row["is_deleted"] == 1) {

			Gateway_Db_FileNode_PostUpload::delete($item["queue_id"]);
			return;
		}

		// обновляем поле extra для файла
		try {
			$extra = Type_File_Image_Process::doPostProcess($item["part_path"], $file_row["extra"]["company_id"], $file_row["extra"]["company_url"], $file_row["extra"]);
		} catch (WarningException $e) {

			// удаляем из очереди на тестовом сервере, так как там файлы чистятся
			if (isTestServer()) {

				Gateway_Db_FileNode_PostUpload::delete($item["queue_id"]);
				return;
			} else {
				throw $e;
			}
		}

		// обновляем данные файла
		try {
			Type_File_Main::updateFile($file_row["file_key"], $extra);
		} catch (Gateway_Socket_Exception_CompanyIsNotServed|Gateway_Socket_Exception_CompanyIsHibernated $e) {

			// если при обновлении файла компания уже неактивна, то кидаем exception только для паблика
			// так как для тестового и стейджа можно вручную такой кейс получить
			if (!isTestServer() && !isStageServer()) {
				throw $e;
			}
		}

		// удаляем из очереди
		Gateway_Db_FileNode_PostUpload::delete($item["queue_id"]);
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "file" . NODE_ID . "_" . parent::_resolveBotName();
	}
}