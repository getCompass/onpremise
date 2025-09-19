<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\InappropriateContentException;

/**
 * Класс крона, который отправляет файлы в DLP на проверку
 */
class Cron_DlpCheck extends \Cron_Default
{
	protected const _MAX_ERROR_COUNT   = 3; // максимальное количество ошибок
	protected const _PRODUCER_LIMIT    = 30;
	protected const _PRODUCER_INTERVAL = 60 * 3; // интервал продюсера

	// параметры крона
	protected int $sleep_time = 0;

	protected int $memory_limit = 50;

	/** рабочая функция */
	public function work(): void
	{

		// получаем очередь image файлов для обработки
		$list = Gateway_Db_FileNode_DlpCheckQueue::getListForWork(self::_PRODUCER_LIMIT, $this->bot_num * self::_PRODUCER_LIMIT);

		// проверяем, что нам вернулись задачи
		if (count($list) < 1) {

			$this->say("Записей нет, спим секунду");
			$this->sleep(1);
			return;
		}

		// выводим что получили задачу в работу
		$this->say(sprintf("Получил %d задач в работу", count($list)));
		$this->_doUpdateTaskList($list);

		// отдаем задачи в rabbit
		$this->_sendToRabbit($list);

		$this->sleep(0);
	}

	// обновляем список задач
	protected function _doUpdateTaskList(array $list): void
	{

		// формируем IN для обновления need_work
		$in = formatIn($list, "queue_id");

		$need_work = time() + self::_PRODUCER_INTERVAL;

		// обновляем need_work взятым задачам
		$set = [
			"error_count" => "error_count + 1",
			"need_work"   => $need_work,
		];
		Gateway_Db_FileNode_DlpCheckQueue::updateList($in, $set);
	}

	/**
	 * Функция для отправки задачи в doWork
	 *
	 *
	 * @throws \returnException
	 */
	protected function _sendToRabbit(array $list): void
	{

		foreach ($list as $item) {

			// если превысили количество ошибок - файл признается запрещенным
			if ($item["error_count"] > self::_MAX_ERROR_COUNT) {

				$file_row = Gateway_Db_FileNode_File::getOne($item["file_key"]);
				$this->_setFinalStatusForFile($file_row, Type_File_Main::FILE_STATUS_RESTRICTED);

				// удаляем элемент из очереди
				Gateway_Db_FileNode_DlpCheckQueue::delete($item["queue_id"]);
				continue;
			}

			$this->doQueue($item);
			$this->say("Отдал в Rabbit задачу [{$item["queue_id"]}]");
		}
	}

	/**
	 * Обрабатываем задачи
	 *
	 *
	 * @throws \returnException
	 */
	public function doWork(array $item): void
	{

		if (!$this->_isTaskExist($item["queue_id"])) {
			return;
		}

		// проверяем, а вообще нужно ли обрабатывать файл
		$file_row = Gateway_Db_FileNode_File::getOne($item["file_key"]);

		if ($file_row["status"] != Type_File_Main::FILE_STATUS_PROCESSING) {
			return;
		}

		try {

			// отправляем файл в dlp
			Domain_File_Action_SendToDlp::do(
				$file_row["user_id"],
				$file_row["file_name"],
				$file_row["file_extension"],
				Type_File_Utils::getFilePathFromPartPath($file_row["part_path"])
			);
		} catch (InappropriateContentException) {

			// устанавливаем финальный статус файла
			$this->_setFinalStatusForFile($file_row, Type_File_Main::FILE_STATUS_RESTRICTED);

			// удаляем элемент из очереди
			Gateway_Db_FileNode_DlpCheckQueue::delete($item["queue_id"]);
			return;
		} catch (ReturnFatalException) {

			// получили ошибку при подключении к DLP серверу, повторим попытку позже
			Gateway_Db_FileNode_DlpCheckQueue::updateList([$item["queue_id"]], ["need_work" => time()]);
			return;
		}

		// если ошибок нет - значит файл разрешен
		$this->_setFinalStatusForFile($file_row, Type_File_Main::FILE_STATUS_APPROVED);

		// отсылаем его на постобработку
		Type_File_Process::sendToPostUpload($file_row, $file_row["part_path"]);

		// удаляем элемент из очереди
		Gateway_Db_FileNode_DlpCheckQueue::delete($item["queue_id"]);
	}

	// проверяем существует ли задача
	protected function _isTaskExist(int $queue_id): bool
	{

		// получаем запись из очереди, проверяем может она уже обработана - тогда просто мягко выходим
		// чтобы один и тот же видос не обрабатывался несколько раз если вдруг залагает и в rabbit отправится много раз
		$row = Gateway_Db_FileNode_DlpCheckQueue::get($queue_id);
		if (!isset($row["queue_id"])) {
			return false;
		}

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Устанавливаем конечный статус для файла
	 *
	 *
	 * @throws \returnException
	 */
	protected function _setFinalStatusForFile(array $file_row, int $final_status): void
	{

		$file_key           = $file_row["file_key"];
		$company_id         = Type_File_Default_Extra::getCompanyId($file_row["extra"]);
		$company_entrypoint = Type_File_Default_Extra::getCompanyUrl($file_row["extra"]);

		$set = [
			"status"     => $final_status,
			"updated_at" => time(),
		];

		// обновляем статус в базе ноды
		Gateway_Db_FileNode_File::update($file_key, $set);

		// обновляем файл на ноде
		Gateway_Socket_FileBalancer::updateFileStatus($file_key, $final_status, $company_id, $company_entrypoint);
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName(): string
	{

		return "file" . NODE_ID . "_" . parent::_resolveBotName();
	}
}
