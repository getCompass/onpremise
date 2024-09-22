<?php

namespace Compass\FileNode;

use BaseFrame\Server\ServerProvider;

/**
 * Класс крона, который производит обработку video файлов после их загрузки на файловую ноду
 */
class Cron_Postupload_Video extends \Cron_Default {

	protected const _MAX_ERROR_COUNT             = 3; // максимальное количество ошибок
	protected const _PRODUCER_INTERVAL           = 60 * 10; // интервал продюсера
	protected const _ONPREMISE_PRODUCER_INTERVAL = 60 * 20; // интервал продюсера для онпрема
	protected const _PRODUCER_LIMIT              = 30;

	// параметры крона
	protected int $sleep_time   = 0;
	protected int $memory_limit = 50;

	/** рабочая функция */
	public function work():void {

		// получаем очередь image файлов для обработки
		$list = Gateway_Db_FileNode_PostUpload::getListForWork(FILE_TYPE_VIDEO, self::_PRODUCER_LIMIT, $this->bot_num * self::_PRODUCER_LIMIT);

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
	protected function _doUpdateTaskList(array $list):void {

		// формируем IN для обновления need_work
		$in = formatIn($list, "queue_id");

		$need_work = ServerProvider::isOnPremise() ? time() + self::_ONPREMISE_PRODUCER_INTERVAL : time() + self::_PRODUCER_INTERVAL;

		// обновляем need_work взятым задачам
		$set = [
			"error_count" => "error_count + 1",
			"need_work"   => $need_work,
		];
		Gateway_Db_FileNode_PostUpload::updateList($in, $set);
	}

	/**
	 * Функция для отправки задачи в doWork
	 *
	 * @param array $list
	 *
	 * @return void
	 * @throws \returnException
	 */
	protected function _sendToRabbit(array $list):void {

		foreach ($list as $item) {

			if ($item["error_count"] > self::_MAX_ERROR_COUNT) {

				// не удалось обработать видео - удаляем его из очереди и помечаем не обработанным
				$item["extra"] = fromJson($item["extra"]);

				$video_type          = $item["extra"]["video_type"];
				$format              = $item["extra"]["format"];
				$postupload_start_at = isset($item["extra"]["postupload_start_at"]) ? $item["extra"]["postupload_start_at"] : time();
				$duration            = isset($item["extra"]["duration"]) ? $item["extra"]["duration"] : 0;
				$this->_setFinalStatusForVideoItem($item["queue_id"], $item["file_key"], $video_type, $format, 0, false, $postupload_start_at, $duration, false);
				continue;
			}

			$this->doQueue($item);
			$this->say("Отдал в Rabbit задачу [{$item["queue_id"]}]");
		}
	}

	/**
	 * Обрабатываем задачи
	 *
	 * @param array $item
	 *
	 * @return void
	 * @throws \returnException
	 */
	public function doWork(array $item):void {

		// проверяем что тип файла video
		if ($item["file_type"] != FILE_TYPE_VIDEO) {

			throw new \returnException(__METHOD__ . ": got file with type '{$item["file_type"]}', but expected VIDEO");
		}

		$item["extra"]       = fromJson($item["extra"]);
		$width               = $item["extra"]["width"];
		$height              = $item["extra"]["height"];
		$video_type          = $item["extra"]["video_type"];
		$format              = $item["extra"]["format"];
		$postupload_start_at = isset($item["extra"]["postupload_start_at"]) ? $item["extra"]["postupload_start_at"] : time();
		$duration            = isset($item["extra"]["duration"]) ? $item["extra"]["duration"] : 0;

		// проверяем не удален ли файл (а вдруг) и если вдруг удален - выходим
		$file_row = Gateway_Db_FileNode_File::getOne($item["file_key"]);
		if ($file_row["is_deleted"] == 1) {

			$this->_setFinalStatusForVideoItem($item["queue_id"], $item["file_key"], $video_type, $format, 0, false, $postupload_start_at, $duration, false);
			return;
		}

		if (!$this->_isTaskExist($item["queue_id"])) {
			return;
		}

		// нарезаем видео на необходимый размер - начинаем с самого маленького
		[$is_success, $is_hdr] = Type_File_Video_Process::doPostProcess($item["part_path"], $item["extra"]["part_path"], $width, $height);

		// получаем размер файла
		$size_kb = 0;
		if ($is_success === true) {

			$new_file_path = Type_File_Utils::getFilePathFromPartPath($item["extra"]["part_path"]);
			$size_kb       = Type_File_Utils::getFileSizeKb($new_file_path);
		}

		// удаляем задачу из очереди
		$this->_setFinalStatusForVideoItem(
			$item["queue_id"], $item["file_key"], $video_type, $format, $size_kb, $is_success, $postupload_start_at, $duration, $is_hdr
		);
	}

	// проверяем существует ли задача
	protected function _isTaskExist(int $queue_id):bool {

		// получаем запись из очереди, проверяем может она уже обработана - тогда просто мягко выходим
		// чтобы один и тот же видос не обрабатывался несколько раз если вдруг залагает и в rabbit отправится много раз
		$row = Gateway_Db_FileNode_PostUpload::get($queue_id);
		if (!isset($row["queue_id"])) {
			return false;
		}

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Устанавливаем конечный статус для видео
	 *
	 * @param int    $queue_id
	 * @param string $file_key
	 * @param int    $video_type
	 * @param int    $format
	 * @param int    $size_kb
	 * @param bool   $is_success
	 * @param int    $postupload_start_at
	 * @param int    $duration
	 * @param bool   $is_hdr
	 *
	 * @return void
	 * @throws \returnException
	 */
	protected function _setFinalStatusForVideoItem(int $queue_id, string $file_key, int $video_type, int $format, int $size_kb, bool $is_success, int $postupload_start_at, int $duration, bool $is_hdr):void {

		// если не успешно обработалось то пишем ошибку в обработку
		Type_File_Video_Main::updateVideoItem($file_key, $video_type, $format, $size_kb, $is_success);

		// удаляем элемент из очереди
		Gateway_Db_FileNode_PostUpload::delete($queue_id);

		// записываем аналитику
		Type_System_Analytic::save(0, 0, Type_System_Analytic::TYPE_POSTUPLOAD_VIDEO_TIME, [
			"file_key"        => $file_key,
			"postupload_time" => time() - $postupload_start_at,
			"size_kb"         => $size_kb,
			"format"          => $format,
			"duration"        => $duration,
			"video_type"      => $video_type,
			"is_hdr"          => $is_hdr ? 1 : 0,
			"is_success"      => $is_success ? 1 : 0,
		]);
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "file" . NODE_ID . "_" . parent::_resolveBotName();
	}
}
