<?php

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Conf\ConfProvider;

/**
 * класс управления базами данными и подключениями
 * служит, что через него удобно шардить подключения и некоторые базы данных
 */
class sharding {

	/**
	 * Пока такое решение для объединенных модулей.
	 *
	 * @return myPDObasic
	 */
	public static function configuredPDO(array $conf):myPDObasic {

		if (!isset($GLOBALS["pdo_driver"][$conf["db"]])) {

			$GLOBALS["pdo_driver"][$conf["db"]] = self::pdoConnect(
				$conf["mysql"]["host"],
				$conf["mysql"]["user"],
				$conf["mysql"]["pass"],
				$conf["mysql"]["ssl"],
				$conf["db"]
			);
		}

		return $GLOBALS["pdo_driver"][$conf["db"]];
	}

	// адаптер mysql для работы с уонкретной базой
	// список баз задается в конфиге sharding.php
	public static function pdo(string $db):myPDObasic {

		if (!isset($GLOBALS["pdo_driver"][$db])) {

			// если нет вообще массива
			if (!isset($GLOBALS["pdo_driver"])) {
				$GLOBALS["pdo_driver"] = [];
			}

			// получаем sharding конфиг
			$conf = ConfProvider::shardingMysql()[$db];

			// создаем соединение
			$GLOBALS["pdo_driver"][$db] = self::pdoConnect(
				$conf["mysql"]["host"],
				$conf["mysql"]["user"],
				$conf["mysql"]["pass"],
				$conf["mysql"]["ssl"],
				$conf["db"]
			);
		}

		return $GLOBALS["pdo_driver"][$db];
	}

	// для негифрованного подключения к sphinx
	public static function sphinx(string $sharding_key):myPDObasic {

		if (!isset($GLOBALS["pdo_driver_sphinx"][$sharding_key])) {

			// если нет вообще массива
			if (!isset($GLOBALS["pdo_driver_sphinx"])) {
				$GLOBALS["pdo_driver_sphinx"] = [];
			}

			// получаем sharding конфиг
			$conf = ConfProvider::shardingSphinx()[$sharding_key];

			// устанавливаем соединение
			$GLOBALS["pdo_driver_sphinx"][$sharding_key] = self::pdoConnect(
				$conf["mysql"]["host"],
				$conf["mysql"]["user"],
				$conf["mysql"]["pass"],
				false,
				$conf["db"]
			);
		}

		return $GLOBALS["pdo_driver_sphinx"][$sharding_key];
	}

	// функция для создания соединения с MySQL сервером
	public static function pdoConnect(string $host, string $user, string $password, bool $ssl, string $db = null):myPDObasic {

		// опции подключения
		$opt = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => true,  // ! Важно чтобы было TRUE
		];

		// если подключение зашифровано
		if ($ssl == true) {

			$opt[PDO::MYSQL_ATTR_SSL_CIPHER]             = "DHE-RSA-AES256-SHA:AES128-SHA";
			$opt[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
		}

		// собираем DSN строку подключения
		$dsn = "mysql:host={$host};";
		if (!is_null($dsn)) {
			$dsn .= "dbname={$db};";
		}
		$dsn .= "charset=utf8mb4;";

		return new myPDObasic($dsn, $user, $password, $opt);
	}

	// для подключени к rabbit
	public static function configuredRabbit(array $conf, string $key):Rabbit {

		if (!isset($GLOBALS["rabbit_driver"][$key])) {

			// создаем новое подключение к rabbit
			$GLOBALS["rabbit_driver"][$key] = new Rabbit($conf["host"], $conf["port"], $conf["user"], $conf["pass"]);
		}

		return $GLOBALS["rabbit_driver"][$key];
	}

	// для подключени к rabbit
	public static function rabbit(string $key):Rabbit {

		if (!isset($GLOBALS["rabbit_driver"][$key])) {

			// если нет вообще массива
			if (!isset($GLOBALS["rabbit_driver"])) {
				$GLOBALS["rabbit_driver"] = [];
			}

			// получаем sharding конфиг
			$conf = ConfProvider::shardingRabbit()[$key];

			$GLOBALS["rabbit_driver"][$key] = new Rabbit($conf["host"], $conf["port"], $conf["user"], $conf["pass"]);
		}

		return $GLOBALS["rabbit_driver"][$key];
	}

	// отключает и закрывает все соединения и статистические методы
	public static function end(bool $close_rabbit = true):bool {

		// не разрывать подключение с RabbitMQ
		// используется в одном месте в проекте - Cron_Default
		// когда крон слишком долго спал и нужно очистить подключения ПЕРЕД doWork
		// т.е. подключение с rabbit гарантированно не протухло, т/к крон только что получил из него задачу
		// ВО ВСЕХ ОСТАЛЬНЫХ СЛУЧАЯХ ИСПОЛЬЗОВАТЬ СТРОГО-НАСТРОГО ЗАПРЕЩЕНО
		if ($close_rabbit == true) {

			// разрываем подключение с RabbitMQ
			console("RABBIT sharding::end()...");
			self::_endRabbit();
		}

		Mcache::end();
		Bus::end();

		// удаляем соединения с MySQL
		self::_endMySql();

		// удаляем соединения с Sphinx
		self::_endSphinx();

		console("sharding::end()...");
		return true;
	}

	// разрываем подключение с RabbitMQ
	protected static function _endRabbit():void {

		if (isset($GLOBALS["rabbit_driver"])) {

			foreach ($GLOBALS["rabbit_driver"] as $key => $value) {

				// удаляем связь
				$GLOBALS["rabbit_driver"][$key]->closeAll();
				unset($GLOBALS["rabbit_driver"][$key]);
			}

			unset($GLOBALS["rabbit_driver"]);
		}
	}

	// удаляем соединения с MySQL
	protected static function _endMySql():void {

		if (!isset($GLOBALS["pdo_driver"])) {

			return;
		}

		foreach ($GLOBALS["pdo_driver"] as $key => $value) {

			// удаляем связь
			$GLOBALS["pdo_driver"][$key] = null;
			unset($GLOBALS["pdo_driver"][$key]);
		}

		unset($GLOBALS["pdo_driver"]);
	}

	// удаляем соединения с Sphinx
	protected static function _endSphinx():void {

		if (!isset($GLOBALS["pdo_driver_sphinx"])) {

			return;
		}

		foreach ($GLOBALS["pdo_driver_sphinx"] as $key => $value) {

			// удаляем связь
			$GLOBALS["pdo_driver_sphinx"][$key] = null;
			unset($GLOBALS["pdo_driver_sphinx"][$key]);
		}

		unset($GLOBALS["pdo_driver_sphinx"]);
	}
}

/**
 * класс для расширения и удобства работы с базой данных через PDO
 * @deprecated заменено на \BaseFrame\Database\PDODriver
 */
#[\JetBrains\PhpStorm\Deprecated(reason: "заменено на \BaseFrame\Database\PDODriver")]
class myPDObasic extends \BaseFrame\Database\PDODriver {

	public const ISOLATION_READ_UNCOMMITTED = "READ UNCOMMITTED";
	public const ISOLATION_REPEATABLE_READ  = "REPEATABLE READ";
	public const ISOLATION_READ_COMMITTED   = "READ COMMITTED";
	public const ISOLATION_SERIALIZABLE     = "SERIALIZABLE";

	// устанавливаем уровень транзакции
	public function setTransactionIsolationLevel(string $isolation_level):bool {

		// проверяем что не прислали левачок
		$isolation_level_list = [
			self::ISOLATION_READ_UNCOMMITTED,
			self::ISOLATION_REPEATABLE_READ,
			self::ISOLATION_READ_COMMITTED,
			self::ISOLATION_SERIALIZABLE,
		];
		if (!in_array($isolation_level, $isolation_level_list)) {
			throw new QueryFatalException("Unknown isolation level = '{$isolation_level}', please use one of myPDObasic::ISOLATION_ constants");
		}
		$query  = "SET TRANSACTION ISOLATION LEVEL {$isolation_level};";
		$result = $this->query($query);
		return $result->errorCode() == PDO::ERR_NONE;
	}

	// начинаем транзакцию
	public function beginTransaction():bool {

		$this->_showDebugIfNeed("BEGIN");
		return parent::beginTransaction();
	}

	// коммитим транзакцию (может быть удачно|нет)
	public function commit():bool {

		$this->_showDebugIfNeed("COMMIT");
		return parent::commit();
	}

	// коммитим транзакцию (бросаем исключение если не вышло)
	public function forceCommit():void {

		$result = self::commit();
		if ($result != true) {
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	// rollback транзакции
	public function rollback():bool {

		$this->_showDebugIfNeed("ROLLBACK");
		return parent::rollBack();
	}

	// возвращает одну запись или пустой массив если не найден
	public function getOne(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_showDebugIfNeed($query);
		$prepared_query->execute();
		$result = $prepared_query->fetch();
		return is_array($result) ? $result : [];
	}

	// возвращает множество записей или пустой массив если не найден
	public function getAll(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_showDebugIfNeed($query);
		$prepared_query->execute();
		$result = $prepared_query->fetchAll();

		return is_array($result) ? $result : [];
	}

	// обновляем значения в базе
	public function update(string $query, string $table, ...$params):int {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_showDebugIfNeed($query);
		$prepared_query->execute();

		return $prepared_query->rowCount();
	}

	// удаляем значение из базы
	public function delete(string $query, string $table, ...$params):int {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_showDebugIfNeed($query);
		$prepared_query->execute();
		return $prepared_query->rowCount();
	}

	// пытаемся вставить данные в таблицу, если есть - изменяем
	public function insertOrUpdate(string $table, array $insert, array $update = null):int {

		if (!is_array($insert) || count($insert) < 1) {
			$this->_throwError("INSERT DATA is empty!");
		}

		if ($update === null) {
			$update = $insert;
		}
		[$update_part, $update_params]   = $this->_prepareUpdateQueryPart($table, $update);
		$prepared_query = $this->_prepareInsertQuery($table, [$insert], update_part: $update_part, update_params: $update_params);

		$this->_showDebugIfNeed($prepared_query->queryString);
		$prepared_query->execute();

		return $prepared_query->rowCount();
	}

	/**
	 * вставить значения в таблицу (возвращает lastInsertId() - если надо)
	 *
	 * @param string $table
	 * @param array  $insert
	 * @param bool   $is_ignore
	 *
	 * @return false|string
	 * @throws \queryException
	 * @mixed - хз что вернет
	 */
	public function insert(string $table, array $insert, bool $is_ignore = true):false|string {

		if (!is_array($insert) || count($insert) < 1) {
			return $this->_throwError("INSERT DATA is empty!");
		}

		$prepared_query = $this->_prepareInsertQuery($table, [$insert], $is_ignore);
		$this->_showDebugIfNeed($prepared_query->queryString);
		$prepared_query->execute();
		return $this->lastInsertId();
	}

	// вставить массив значенй в таблицу (возвращает количество вставленных строк)
	public function insertArray(string $table, array $insert):int {

		if (!is_array($insert) || count($insert) < 1) {
			$this->_throwError("INSERT DATA is empty!");
		}

		$prepared_query = $this->_prepareInsertQuery($table, $insert);
		$this->_showDebugIfNeed($prepared_query->queryString);
		$prepared_query->execute();
		return $prepared_query->rowCount();
	}

	/**
	 * пытаемся вставить список записей в таблицу, но для всех имеющихся записей с таким PRIMARY KEY
	 * будет произведено их обновление
	 *
	 * @return int
	 */
	public function insertArrayOrUpdate(string $table, array $insert, array $update):int {

		[$update_part, $update_params]   = $this->_prepareUpdateQueryPart($table, $update);
		$prepared_query = $this->_prepareInsertQuery($table, $insert, update_part: $update_part, update_params: $update_params);

		$this->_showDebugIfNeed($prepared_query->queryString);
		$prepared_query->execute();
		return $prepared_query->rowCount();
	}

	// возвращает массив значений первого столбца или пустой массив, если записи не найдены
	public function getAllColumn(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_showDebugIfNeed($query);
		$prepared_query->execute();
		$result = $prepared_query->fetchAll(PDO::FETCH_COLUMN);

		return is_array($result) ? $result : [];
	}

	/**
	 * Возвращает <b>одномерный</b> ассоциативный массив записей или пустой массив
	 * В запросе ожидается ровно два стобца
	 * Индексы массива беруться из первого столбца, значения берутся из второго
	 *
	 * @param mixed ...$args
	 *
	 * @return array
	 * @throws queryException
	 */
	public function getAllKeyPair(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_showDebugIfNeed($query);
		$prepared_query->execute();
		$result = $prepared_query->fetchAll(PDO::FETCH_KEY_PAIR);

		return is_array($result) ? $result : [];
	}

	// -----------------------------------------------------------------
	// PROTECTED
	// -----------------------------------------------------------------

	// форматируем array для вставки
	// @long
	protected function _prepareInsertQuery(string $table, array $row_list, bool $is_ignore = true, bool $is_delayed = false, string $update_part = "", array $update_params = []):PDOStatement {

		$columns = "";
		$values  = "";
		$params  = [];

		$columns        = array_keys($row_list[0]);
		$columns        = array_map(fn ($column) => "`" . $this->_removeQuote($column) . "`", $columns);
		$columns_string = implode(", ", $columns);
		$columns_count  = count($columns);

		foreach ($row_list as $row) {

			if (count($row) !== $columns_count) {
				$this->_throwError("insert row column count dont match");
			}

			// ищем массивы и превращаем их в JSON
			$temp = array_map(fn ($value) => is_array($value) ? toJson($value) : $value, $row);

			$values .= $this->_prepareInsertRow($row);
			$params = array_merge($params, array_values($temp));
		}

		$extra   = $is_ignore ? "IGNORE" : "";
		$delayed = $is_delayed ? "delayed" : "";
		$values  = substr($values, 0, -1);

		$query = "INSERT $delayed $extra INTO `$table` ($columns_string) VALUES $values";

		if ($update_part !== "") {
			$query = "INSERT $delayed $extra INTO `$table` ($columns_string) VALUES $values ON DUPLICATE KEY UPDATE $update_part";
		}

		$prepared_query = $this->prepare($query);

		$last_index = 0;

		// в PDO параметры биндятся указателями
		foreach ($params as &$param) {

			++$last_index;
			$prepared_query->bindParam($last_index, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}

		// если есть параметры для апдейта, добавляем в запрос
		if ($update_params !== []) {

			// в PDO параметры биндятся указателями
			foreach ($update_params as &$param) {

				++$last_index;
				$prepared_query->bindParam($last_index, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
			}
		}

		return $prepared_query;
	}

	protected function _prepareInsertRow(array $row):string {

		$token_list = str_repeat("?,", count($row) - 1) . "?";

		return "($token_list),";
	}

	// понимает такие вещи как ["value"=>"value + 1"] в этом случае идет инкремент, если же просто ["value"=>"3"];
	// @long
	protected function _prepareUpdateQueryPart(string $table, array $set):array {

		$temp = [];
		$param_list = [];

		foreach ($set as $k => $v) {

			// чистим название ключа
			$k = $this->_removeQuote($k);

			if (is_array($v)) {
				$v = toJson($v);

			} elseif ((inHtml($v, "-") || inHtml($v, "+")) && inHtml($v, $k)) {

				// если это контрукция инкремента / декремента вида value = value + 1
				$gg = str_replace($k, "", $v);
				$gg = str_replace("-", "", $gg);
				$gg = intval(trim(str_replace("+", "", $gg)));

				// если инкремент декремент больше 0
				if ($gg > 0) {

					if (inHtml($v, "-")) {

						$temp[] = "`$k` = `$k` - $gg";
						continue;
					} else {

						$temp[] = "`$k` = `$k` + $gg";
						continue;
					}
				}
			}

			//
			$temp[]       = "`{$k}` =  ?";
			$param_list[] = $v;
		}

		return [implode(", ", $temp), $param_list];
	}

	/**
	 * Подготовить запрос
	 *
	 * @param string $raw
	 * @param string $table_name
	 * @param array  $raw_param_list
	 *
	 * @return PDOStatement
	 * @long
	 */
	protected function 	_getPreparedQuery(string $raw, string $table_name, array $raw_param_list):PDOStatement {

		$query = "";
		$param_list = [];

		// защита от левака
		$raw_param_list = array_values($raw_param_list);

		// проверяем что имя таблицы обернуто в косые кавычки `
		$pos = strpos($raw, "`?p`");
		if ($pos === false) {
			$this->_throwError("cant find table name, query $raw");
		}
	    $raw = substr_replace($raw, "`$table_name`", $pos, strlen("`?p`"));

		preg_match_all("(\?[siuap])", $raw, $matches);
		$raw_marker_list = $matches[0];
		$marker_list = [];
		$param_list = [];

		$param_count  = count($raw_param_list);
		$marker_count = count($raw_marker_list);

		if ($param_count != $marker_count) {
			$this->_throwError("Number of args ($param_count) doesn\"t match number of placeholders ($marker_count) in [$raw]");
		}

		// проходимся по всем текущим плейсхолдерам и приводим к типам параметры
		foreach ($raw_marker_list as $index => $marker) {

			if ($marker == "?u") {
				[$update_query_part, $update_param_list] = $this->_prepareUpdateQueryPart($table_name, $raw_param_list[$index]);
			}

			$param = match($marker) {
				"?s", "?p" => (string) $raw_param_list[$index],
				"?i"       => (int) $raw_param_list[$index],
				"?a"       => (array) array_values($raw_param_list[$index]),
				"?u"       => (array) $update_param_list,
			};

			$marker_list[] = 	match($marker) {
				"?p"             => $param,
				"?s", "?i"       => "?",
				"?a"             => count($param) > 0 ? str_repeat("?,", count($param) - 1) . "?" : "NULL",
				"?u"             => $update_query_part
			};

			// для ?p параметр добавлять не надо
			if ($marker === "?p") {
				continue;
			}

			in_array($marker, ["?u", "?a"]) ? $param_list = array_merge($param_list, $param) : $param_list[] = $param;
		}

		// проверяем, что нигде в запросе не переданы числа (а только все через подготовленные выражения)
		if (preg_match("#[0-9]+#ism", preg_replace("#`.+?`#ism", "", $raw))) {
			$this->_throwError("В запросе присуствуют цифры, которые не являются частью названия таблицы или полей!\nПРОВЕРЬТЕ: Все названия полей должны быть в косых ковычках, а любые значения только через переданные параметры.\n{$raw}");
		}

		// заменяем все плейсхолдерами
		$query = preg_replace_callback("(\?[siuap])", function(array $_) use (&$marker_list) { return (string) array_shift($marker_list);}, $raw);

		if (!inHtml(strtolower($query), "limit") || !inHtml(strtolower($query), "where")) {
			$this->_throwError("WHERE or LIMIT not found on SQL: {$query}");
		}

		// подготавливаем запрос
		$prepared_query = $this->prepare($query);

		// приклеиваем параметры
		// указатель нужен, так как PDO приклеивает к запросу параметр именно по указателю
		foreach($param_list as $index => &$param) {
			$prepared_query->bindParam($index + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}

		return $prepared_query;
	}

	/**
	 * кидает исключение
	 *
	 * @param string $message
	 *
	 * @throws queryException
	 * @mixed
	 */
	protected function _throwError(string $message) {

		throw new QueryFatalException($message);
	}

	// показываем debug в запросе если надо
	protected function _showDebugIfNeed(string $query):void {

		if (!defined("DEBUG_MYSQL")) {
			return;
		}
		if (DEBUG_MYSQL != true) {
			return;
		}

		console(blueText($query));
	}

	// удаляем кавычки из текста (нужно для названия столбцов)
	protected function _removeQuote(string $value):string {

		return str_replace(["\"", "`", "'"], "", $value);
	}

}

/**
 * класс содержит вспомогательные функции для описания параметров подключения к mysql в конфиг-файле
 * api/conf/sharding.php
 */
class shardingConf {

	// существующие разновидности шардирования
	public const SHARDING_TYPE_NONE  = "none";
	public const SHARDING_TYPE_INT   = "int";
	public const SHARDING_TYPE_HEX   = "hex";
	public const SHARDING_TYPE_MONTH = "month";

	/**
	 * сформировать информацию, для описания шардирования типа int
	 *
	 * @param int $from
	 * @param int $to
	 *
	 * @return int[]
	 */
	public static function makeDataForIntShardingType(int $from, int $to):array {

		return [
			"from" => (int) $from,
			"to"   => (int) $to,
		];
	}

	/**
	 * сформировать информацию, для описания шардирования типа hex
	 *
	 * @param string $max_hex
	 *
	 * @return array
	 */
	public static function makeDataForHexShardingType(string $max_hex):array {

		return [
			"max_hex" => (string) $max_hex,
		];
	}

	/**
	 * сформировать информацию, для описания шардирования типа month
	 *
	 * @param string $month_sharding например: 2018_6
	 *
	 * @return array
	 */
	public static function makeDataForMonthShardingType(string $month_sharding):array {

		return [
			"month_sharding" => (string) $month_sharding,
		];
	}

	/**
	 * генерирует поле schemas для одного месяца
	 *
	 * @param string $db_postfix
	 * @param array  $table_list
	 * @param array  $extra_merge_list
	 *
	 * @return array
	 */
	public static function makeMonthShardingSchemas(string $db_postfix, array $table_list, array $extra_merge_list = []):array {

		// разбиваем 2019_6 на 2019 и 6
		[$year, $month] = explode("_", $db_postfix);

		$day_max = cal_days_in_month(CAL_GREGORIAN, $month, $year); // получаем количество дней в месяце

		// бежим по каждой таблице
		$output = [];
		foreach ($table_list as $k1 => $v1) {

			// заполняем от 1 дня месяца до последнего
			for ($i = 1; $i <= $day_max; $i++) {

				$postfix_table_name          = "{$k1}_{$i}"; // имя таблицы с префиксом (dynamic_14)
				$output[$postfix_table_name] = $v1;
			}
		}

		// добавляем к ответу extra
		return array_merge($output, $extra_merge_list);
	}

	/**
	 * генерирует поле schemas для таблиц от 0 до hex (например ff)
	 *
	 * @param string $max_hex
	 * @param array  $table_list
	 * @param array  $extra_merge_list
	 *
	 * @return array
	 */
	public static function makeHexShardingSchemas(string $max_hex, array $table_list, array $extra_merge_list = []):array {

		$hex_len     = strlen($max_hex); // чтобы все хексы получались одной длины
		$dec_max_hex = hexdec($max_hex); // переводим макс hex в int чтобы пробежать циклом

		// бежим по всем табличкам
		$output = [];
		foreach ($table_list as $k1 => $v1) {

			// заполняем от 0 до hex
			for ($i = 0; $i <= $dec_max_hex; $i++) {

				$postfix                     = sprintf("%0{$hex_len}s", dechex($i));
				$postfix_table_name          = "{$k1}_{$postfix}"; // имя таблицы с префиксом (blacklist_01)
				$output[$postfix_table_name] = $v1;
			}
		}

		// добавляем к ответу extra
		return array_merge($output, $extra_merge_list);
	}

	/**
	 * генерирует поле schemas для таблиц от 0 до int
	 *
	 * @param int   $from
	 * @param int   $to
	 * @param array $table_list
	 * @param array $extra_merge_list
	 *
	 * @return array
	 */
	public static function makeIntShardingSchemas(int $from, int $to, array $table_list, array $extra_merge_list = []):array {

		$output = [];
		foreach ($table_list as $k1 => $v1) {

			// заполняем от 0 до hex
			for ($i = $from; $i <= $to; $i++) {

				$postfix_table_name          = "{$k1}_{$i}"; // имя таблицы с постфиксом (left_menu_1)
				$output[$postfix_table_name] = $v1;
			}
		}

		// добавляем к ответу extra
		return array_merge($output, $extra_merge_list);
	}
}

/**
 * класс для обертки значения в функцию
 *
 * Class PdoFuncValue
 */
class PdoFuncValue {

	private function __construct(
		public string $function_name,
		public array  $param_list,
	) {

	}

	// init
	public static function init(string $function_name, ...$param):self {

		return new self($function_name, $param);
	}
}