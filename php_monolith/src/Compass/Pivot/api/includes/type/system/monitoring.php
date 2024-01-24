<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

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
	];

	// список директорий в которых ищутся файлы
	protected const _DIR_LIST = [
		PATH_LOGS . "/cron/",
		PATH_LOGS,
		PATH_LOGS . "/exception/",
	];

	protected const _SELECTEL_MONITORING_KEY = "selectel_inbox_monitoring";
	protected const _SELECTEL_SENDER         = "selectel.ru";
	protected const _IMAP_TIMEOUT            = 5;
	protected const _IMAP_RETRIES            = 5;

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
	 */
	protected static function _getCursor(string $file_hash, false|int $actual_file_size):int|false {

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
			if (str_contains($line, self::_LOG_DELIMITER)) {

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
	protected static function _yieldOneLine(string $file_path, int $cursor):\Generator {

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
		$header = ":warning: *Exception* on *" . SERVER_NAME . "* server, module:" . CURRENT_MODULE . " :warning:\n";

		// разбиваем на 2 части
		$arr = explode("|", $text, 2);

		$prepared_text = $arr[0];
		$tt            = explode("\n", $prepared_text);
		$body          = implode("\n", array_slice($tt, 0, count($tt) - 2));

		return $header . $body;
	}

	// уведомляем
	protected static function _notify(array $logs):void {

		$text = implode("\n", $logs);
		Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $text);
	}

	// обновляем курсор в базе
	protected static function _updateCursor(string $file_hash, int $actual_file_size):void {

		// обновляем актуальные данные в `datastore`
		Type_System_Datastore::set($file_hash, [
			"cursor" => $actual_file_size,
		]);
	}

	/**
	 * мониторинг баланса CDN у Selectel
	 *
	 * @long
	 */
	public static function balanceSelectelCdnForImap():void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		if (MONITORING_EMAIL_LOGIN == "" || MONITORING_EMAIL_PASSWORD == "" || IMAP_CONNECT == "") {
			return;
		}

		// открываем соединение
		$inbox = self::_tryOpen(MONITORING_EMAIL_LOGIN, MONITORING_EMAIL_PASSWORD, IMAP_CONNECT);
		if (!$inbox) {
			return;
		}

		// получаем актуальное количество сообщений на почте
		$inbox_message_count = imap_num_msg($inbox);

		// получаем предыдущее количество сообщений на почте
		$inbox_message_count_before = 0;
		$row                        = Type_System_Datastore::get(self::_SELECTEL_MONITORING_KEY);
		if (isset($row["inbox_message_count"])) {
			$inbox_message_count_before = $row["inbox_message_count"];
		}
		if (count($row) === 0) {

			// записываем актуальное количество сообщений
			Type_System_Datastore::set(self::_SELECTEL_MONITORING_KEY, [
				"inbox_message_count" => $inbox_message_count,
			]);
		}

		// логируем работу мониторинга
		Type_System_Admin::log(self::_SELECTEL_MONITORING_KEY, " - запуск мониторинга");

		// не делаем ничего если количество сообщений не изменилось
		if ($inbox_message_count_before == $inbox_message_count) {
			return;
		}

		// записываем актуальное количество сообщений
		Type_System_Datastore::set(self::_SELECTEL_MONITORING_KEY, [
			"inbox_message_count" => $inbox_message_count,
		]);

		// отсчет писем начинается с 1
		if ($inbox_message_count_before == 0) {
			$inbox_message_count_before = 1;
		}

		// проходимся по всем новым сообщениям и смотрим есть ли нужное нам
		for ($i = $inbox_message_count_before; $i <= $inbox_message_count; $i++) {

			$header = (array) imap_headerinfo($inbox, $i);
			$sender = (array) $header["sender"][0];
			if ($sender["host"] != self::_SELECTEL_SENDER) {
				continue;
			}

			$message = self::_getTextFromEmailMessage($inbox, $i);

			// отправляем сообщения с алертом в чат, что заканчивается баланс для работы CDN
			if (strripos($message, "Пополните баланс") && strripos($message, "CDN")) {
				Gateway_Notice_Sender::sendGroup(SMS_EXCEPTION, "Срочно необходимо пополнить баланс Хранилища и CDN Selectel!!!");
			}
		}
	}

	/**
	 * открыть соединение с imap шлюзом
	 *
	 * return resource|false
	 * @mixed
	 *
	 * @param string $login
	 * @param string $password
	 * @param string $imap_connect
	 *
	 * @return false|resource
	 */
	protected static function _tryOpen(string $login, string $password, string $imap_connect) {

		if (mb_strlen($login) < 1 || mb_strlen($password) < 1 || mb_strlen($imap_connect) < 1) {

			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, "Не смогли подключиться к email через imap для мониторинга баланса CDN - не заполнены необходимые данные для подключения");
			return false;
		}

		// устанавливаем timeout на соединения
		imap_timeout(IMAP_OPENTIMEOUT, self::_IMAP_TIMEOUT);
		imap_timeout(IMAP_READTIMEOUT, self::_IMAP_TIMEOUT);

		try {
			$inbox = imap_open("{" . $imap_connect . "}INBOX", $login, $password, OP_READONLY, self::_IMAP_RETRIES);
		} catch (\Error $e) {

			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, "Не смогли подключиться к email через imap для мониторинга баланса CDN" . __METHOD__ . ": #1 " . $e->getMessage());
			return false;
		}

		$last_error = imap_last_error();
		if ($last_error != false) {

			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, "Не смогли подключиться к email через imap для мониторинга баланса CDN" . __METHOD__ . ": #2 " . $last_error);
			return false;
		}

		if (!$inbox) {

			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, "Не смогли подключиться к email через imap для мониторинга баланса CDN - не получили получили подключение к inbox");
			return false;
		}

		return $inbox;
	}

	/**
	 * получаем текст из email сообщения
	 *
	 * @param     $inbox
	 * @param int $message_number
	 *
	 * @mixed
	 * @return string
	 */
	protected static function _getTextFromEmailMessage($inbox, int $message_number):string {

		$structure = imap_fetchstructure($inbox, $message_number);
		$message   = imap_fetchbody($inbox, $message_number, 1);

		// расшифровываем если есть encoding в структуре (может не быть)
		if (isset($structure->encoding)) {

			$message = match ($structure->encoding) {
				ENC8BIT            => imap_8bit($message),
				ENCBINARY          => imap_binary($message),
				ENCBASE64          => imap_base64($message),
				ENCQUOTEDPRINTABLE => imap_qprint($message),
			};
		}

		// расшифровываем если encoding в parts
		if (isset($structure->parts[0])) {

			$message = match ($structure->parts[0]->encoding) {
				ENC8BIT            => imap_8bit($message),
				ENCBINARY          => imap_binary($message),
				ENCBASE64          => imap_base64($message),
				ENCQUOTEDPRINTABLE => imap_qprint($message),
			};
		}

		return $message;
	}
}