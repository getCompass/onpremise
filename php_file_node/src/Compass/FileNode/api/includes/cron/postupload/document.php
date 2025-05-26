<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс крона, который производит обработку текстовых документов после их загрузки на файловую ноду
 */
class Cron_Postupload_Document extends \Cron_Default {

	protected const _MAX_ERROR_COUNT   = 3; // максимальное количество ошибок
	protected const _PRODUCER_INTERVAL = 60 * 2; // интервал продюсера

	// ключи к бд
	protected const _DB_KEY         = "file_node";
	protected const _TABLE_KEY      = "post_upload_queue";
	protected const _PRODUCER_LIMIT = 50;

	// параметры крона
	protected int $sleep_time   = 0;
	protected int $memory_limit = 50;

	// максимальный размер содержимого документа
	// 100к символов, чтобы и балансер не загибался, и индекс тоже норм себя чувствовал
	protected const _MAX_CONTENT_LENGTH = 100_000;

	/**
	 * producer
	 */
	public function work():void {

		// получаем задачи из базы
		$list = $this->_getList();

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

	/**
	 * функция для получения задачи из базы
	 *
	 * @return array
	 */
	protected function _getList():array {

		$offset = $this->bot_num * self::_PRODUCER_LIMIT;

		// запрос проверен на EXPLAIN (INDEX=type)
		$query = "SELECT * FROM `?p` WHERE `file_type` = ?i AND `need_work` <= ?i LIMIT ?i OFFSET ?i";

		return \sharding::pdo(self::_getDbKey())->getAll($query, self::_TABLE_KEY, FILE_TYPE_DOCUMENT, time(), self::_PRODUCER_LIMIT, $offset);
	}

	/**
	 * обновляем список задач
	 */
	protected function _doUpdateTaskList(array $list):void {

		// формируем IN для обновления need_work
		$in = array_column($list, "queue_id");

		// обновляем need_work взятым задачам
		$set = [
			"error_count" => "error_count + 1",
			"need_work"   => time() + self::_PRODUCER_INTERVAL,
		];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `queue_id` IN (?a) LIMIT ?i";
		\sharding::pdo(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $in, count($in));
	}

	/**
	 * функция для отправки задачи в doWork (consumer0
	 */
	protected function _sendToRabbit(array $list):void {

		// отдаем задачи в rabbit
		foreach ($list as $item) {

			// не удалось обработать аудио - удаляем его
			if ($item["error_count"] > self::_MAX_ERROR_COUNT) {

				$this->_deleteQueue($item["queue_id"]);
				continue;
			}

			$this->doQueue($item);
			$this->say("Отдал в Rabbit задачу [{$item["queue_id"]}]");
		}
	}

	/**
	 * описываем работу consumer (работаем с 1 задачей)
	 *
	 * @param array $task
	 *
	 * @throws ReturnFatalException
	 */
	public function doWork(array $task):void {

		// проверяем что тип файла документ
		if ($task["file_type"] != FILE_TYPE_DOCUMENT) {

			throw new \BaseFrame\Exception\Domain\ReturnFatalException(__METHOD__ . ": got file with type {$task["file_type"]}, but expected DOCUMENT");
		}

		// получаем запись с файлом
		$file_row = Gateway_Db_FileNode_File::getOne($task["file_key"]);

		// проверяем, что документ имеет расширение, которое индексируется
		// проверяем, удален ли файл
		if (!Type_File_Document_Main::isIndexableDocument($file_row["file_extension"])
			|| $file_row["is_deleted"] == 1) {

			$this->_deleteQueue($task["queue_id"]);
			return;
		}

		// создаем объект парсера
		$content_parser = new Type_File_Document_ContentParser($file_row["file_extension"]);

		// получаем содержимое текстового документа
		try {
			$file_content = $content_parser->parse(Type_File_Utils::getFilePathFromPartPath($task["part_path"]));
		} catch (\Throwable $e) {

			// конструкция нужна чтобы крон не прекращал свое дальнейшее выполнение в случае ошибки
			// чтобы очередь продолжала разгребаться
			// исключение залогируется привычным образом и не потеряется

			// логируем исключение
			if (IS_NEED_LOG_POST_UPLOAD_DOCUMENTS) {

				$exception_message = \BaseFrame\Exception\ExceptionUtils::makeMessage($e, 0);
				\BaseFrame\Exception\ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			}

			// удаляем из очереди, как показала практика - тот что не смог спарситься уже не спарсится
			$this->_deleteQueue($task["queue_id"]);
			return;
		}

		// если содержимое пустое, то завершаем задачу
		if (mb_strlen($file_content) < 1) {

			// удаляем из очереди
			$this->_deleteQueue($task["queue_id"]);

			return;
		}

		// обрезаем содержимое, чтобы не привысить максимальное значение
		$file_content = mb_substr($file_content, 0, self::_MAX_CONTENT_LENGTH);

		// сохраняем содержимое
		Type_File_Main::setContent($task["file_key"], $file_row["extra"], $file_content);

		// удаляем из очереди
		$this->_deleteQueue($task["queue_id"]);
	}

	/**
	 * удаляем выполненную задачу из очереди
	 */
	protected function _deleteQueue(int $queue_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		\sharding::pdo(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `queue_id` = ?i LIMIT ?i", self::_TABLE_KEY, $queue_id, 1);
	}

	/**
	 * получаем ключ базы
	 */
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "file" . NODE_ID . "_" . parent::_resolveBotName();
	}
}