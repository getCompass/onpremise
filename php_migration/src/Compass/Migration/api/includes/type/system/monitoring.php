<?php

namespace Compass\Migration;

/**
 * модель для отслеживания возникающих exceptions
 */
class Type_System_Monitoring {

	protected const _LOG_DELIMITER   = "-----"; // разделитель в логе
	protected const _EXCEPTION_LIMIT = 5;       // количество ошибок за одну отправку

	// список файлов за которыми следит мониторинг
	protected const _FILE_LIST = [
		"__php_error.log",
		"__php_critical.log",
		"__admin.log",
		"BaseFrame\Exception\Domain\ReturnFatalException.log",
		"BaseFrame\Exception\Domain\RowNotFoundException.log",
		"BaseFrame\Exception\Domain\ParseFatalException.log",
		"BaseFrame\Exception\Gateway\BusFatalException.log",
	];

	// список директорий в которых ищутся файлы
	protected const _DIR_LIST = [
		PATH_LOGS . "/cron/",
		PATH_LOGS,
		PATH_LOGS . "/exception/",
	];

	// точка входа
	public static function work():void {

		console("начинаем проверку на появление новых Exception");

		// проходимся по каждой папке из списка
		foreach (self::_DIR_LIST as $dir) {

			// проходимся по каждому файлу из списка
			foreach (self::_FILE_LIST as $file_name) {

				// парсим логи из файла
				self::_parseOneFile($dir, $file_name);
			}
		}
	}

	// проходим по каждому файлу и проверяем наличие новых exceptions
	protected static function _parseOneFile(string $dir, string $file_name):void {

		// получаем путь до файла и его хэш
		$path      = $dir . $file_name;
		$file_hash = self::_getKey($path);

		// проверяем существование файла
		if (!file_exists($path)) {

			// сбрасываем метку
			self::_updateCursor($file_hash, 0);
			return;
		}

		$actual_file_size = filesize($path);
		$cursor           = self::_getCursor($file_hash, $actual_file_size);
		if ($cursor === false) {
			return;
		}

		// парсим логи
		self::_parseLogs($path, $cursor);

		// обновляем метку
		self::_updateCursor($file_hash, $actual_file_size);
	}

	// ключ для datastore
	protected static function _getKey(string $file_path):string {

		return md5(__METHOD__ . CODE_UNIQ_VERSION . $file_path);
	}

	/**
	 * получаем курсор из базы
	 *
	 * @param string $file_hash
	 * @param int    $actual_file_size
	 *
	 * @return false|int|mixed
	 * @mixed
	 */
	protected static function _getCursor(string $file_hash, int $actual_file_size) {

		// получаем информацию из базы
		$file_info = Type_System_Datastore::get($file_hash);

		// получаем курсор
		$cursor = $file_info["cursor"] ?? 0;

		// если размер файла совпал с курсором в базе
		if ($actual_file_size == $cursor) {
			return false;
		}

		// если размер файла меньше курсора
		if ($actual_file_size < $cursor) {
			$cursor = 0;
		}

		return $cursor;
	}

	// парсим логи и отправляем
	protected static function _parseLogs(string $path, int $cursor):void {

		$logs = []; // массив для логов
		$text = ""; // строка для сбора одного лога
		foreach (self::_yieldOneLine($path, $cursor) as $line) {

			// объединяем лог в одну строку, если достигли разделителя - добавляем в массив логов
			if (strpos($line, self::_LOG_DELIMITER) !== false) {

				$logs[] = self::_prepareText($text);
				$text   = "";
			} else {
				$text .= $line;
			}

			// если количество логов превысило лимит, то отправляем
			if (count($logs) >= self::_EXCEPTION_LIMIT) {

				self::_notify($logs);
				$logs = [];
				break;
			}
		}

		if (count($logs) > 0) {
			self::_notify($logs);
		}
	}

	// достает из файла по строке генератором
	protected static function _yieldOneLine(string $file_path, int $cursor):Generator {

		$f = fopen($file_path, "r");
		fseek($f, $cursor);
		/** @noinspection PhpAssignmentInConditionInspection */
		while ($line = fgets($f)) {
			yield $line;
		}
		fclose($f);
	}

	// подставляем рюшечки чтобы смотрелось красивее
	protected static function _prepareText(string $text):string {

		// header
		$header = ":warning: *Exception* on *" . SERVER_NAME . "* server! :warning:\n";

		// разбиваем на 2 части
		$arr = explode("|", $text, 2);

		$prepared_text = $arr[0];
		$tt            = explode("\n", $prepared_text);
		$body          = implode("\n", array_slice($tt, 0, count($tt) - 2));

		return "{$header}```{$body}```";
	}

	// отправляем exceptions
	protected static function _notify(array $logs):void {

		// отправляем ошибки
		Gateway_Notice_Sender::sendGroup(EXCEPTION_CHANNEL, implode("\n", $logs));
	}

	// обновляем курсор в базе
	protected static function _updateCursor(string $file_hash, int $actual_file_size):void {

		// обновляем актуальные данные в `datastore`
		Type_System_Datastore::set($file_hash, [
			"cursor" => $actual_file_size,
		]);
	}
}
