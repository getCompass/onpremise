<?php

// класс управления базами данными и подключениями
// служит, что через него удобно шардить подключения и некоторые базы данных
class sharding {

	// адаптер mysql для работы с уонкретной базой
	// список баз задается в конфиге sharding.php
	public static function pdo(string $db):myPDObasic {

		if (!isset($GLOBALS["pdo_driver"][$db])) {

			// если нет вообще массива
			if (!isset($GLOBALS["pdo_driver"])) {
				$GLOBALS["pdo_driver"] = [];
			}

			// получаем sharding конфиг
			$conf = getConfig("SHARDING_MYSQL")[$db];

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
			$conf = getConfig("SHARDING_SPHINX")[$sharding_key];

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
			PDO::ATTR_STATEMENT_CLASS    => ["myPDOStatement"],
		];

		// если подключение зашифровано
		if ($ssl === true) {
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
	public static function rabbit(string $key):Rabbit {

		if (!isset($GLOBALS["rabbit_driver"][$key])) {

			// если нет вообще массива
			if (!isset($GLOBALS["rabbit_driver"])) {
				$GLOBALS["rabbit_driver"] = [];
			}

			// получаем sharding конфиг
			$conf = getConfig("SHARDING_RABBIT")[$key];

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
		if ($close_rabbit === true) {

			// разрываем подключение с RabbitMQ
			console("RABBIT sharding::end()...");
			self::_endRabbit();
		}

		Mcache::end();
		\Bus::end();

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

			foreach ($GLOBALS["rabbit_driver"] as $key => $_) {

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

		foreach ($GLOBALS["pdo_driver"] as $key => $_) {

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

		foreach ($GLOBALS["pdo_driver_sphinx"] as $key => $_) {

			// удаляем связь
			$GLOBALS["pdo_driver_sphinx"][$key] = null;
			unset($GLOBALS["pdo_driver_sphinx"][$key]);
		}

		unset($GLOBALS["pdo_driver_sphinx"]);
	}
}

// класс для расширения и удобства работы с базой данных через PDO
class myPDObasic extends PDO {

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

		$result = $this->commit();
		if ($result !== true) {
			throw new returnException("Transaction commit failed");
		}
	}

	// rollback транзакции
	public function rollback():bool {

		$this->_showDebugIfNeed("ROLLBACK");
		return parent::rollBack();
	}

	// возвращает одну запись или пустой массив если не найден
	public function getOne():array {

		// подготавливаем запрос (очищаем его)
		$query = $this->_prepareQuery(func_get_args());

		// если нет лимита
		if (!inHtml(strtolower($query), "limit") || !inHtml(strtolower($query), "where")) {
			return $this->_throwError("WHERE or LIMIT not found on SQL: {$query}");
		}

		//
		$this->_showDebugIfNeed($query);
		$result = $this->query($query)->fetch();
		return is_array($result) ? $result : [];
	}

	// возвращает множество записей или пустой массив если не найден
	public function getAll():array {

		// подготавливаем запрос (очищаем его)
		$query = $this->_prepareQuery(func_get_args());

		if (!inHtml(strtolower($query), "limit") || !inHtml(strtolower($query), "where")) {
			return $this->_throwError("WHERE or LIMIT not found on SQL: {$query}");
		}

		$this->_showDebugIfNeed($query);
		$result = $this->query($query)->fetchAll();
		return is_array($result) ? $result : [];
	}

	// обновляем значения в базе
	public function update():int {

		// подготавливаем запрос (очищаем его)
		$query = $this->_prepareQuery(func_get_args());
		//

		if (!inHtml(strtolower($query), "limit") || !inHtml(strtolower($query), "where")) {
			return $this->_throwError("WHERE or LIMIT not found on SQL: {$query}");
		}

		//
		$this->_showDebugIfNeed($query);
		$result = $this->query($query);
		return $result->rowCount();
	}

	// удаляем значение из базы
	public function delete():int {

		// подготавливаем запрос (очищаем его)
		$query = $this->_prepareQuery(func_get_args());
		//

		if (!inHtml(strtolower($query), "limit") || !inHtml(strtolower($query), "where")) {
			return $this->_throwError("WHERE or LIMIT not found on SQL: {$query}");
		}

		//
		$this->_showDebugIfNeed($query);
		$result = $this->query($query);
		return $result->rowCount();
	}

	// делаем explain
	// @mixed - для удобства
	public function explain() {

		// подготавливаем запрос (очищаем его)
		$query = $this->_prepareQuery(func_get_args());
		//

		if (!inHtml(strtolower($query), "limit") || !inHtml(strtolower($query), "where")) {
			return $this->_throwError("WHERE or LIMIT not found on SQL: {$query}");
		}

		//
		console(yellowText($query));
		$result = $this->query("explain format=json $query")->fetch();
		console($result["EXPLAIN"]);
		die; // чтобы не выполнялось ничего в explain
	}

	// пытаемся вставить данные в таблицу, если есть - изменяем
	public function insertOrUpdate(string $table, array $insert):int {

		if (!is_array($insert) || count($insert) < 1) {
			return $this->_throwError("INSERT DATA is empty!");
		}

		$set   = $this->_makeQuery($insert);
		$query = $this->_formatArray($table, [$insert]);
		$query .= "on duplicate key update
			{$set}
		";

		$this->_showDebugIfNeed($query);
		$result = $this->query($query);
		return $result->rowCount();
	}

	// вставить значения в таблицу (возвращает lastInsertId() - если надо)
	// @mixed - хз что вернет
	public function insert(string $table, array $insert, bool $is_ignore = true) {

		if (!is_array($insert) || count($insert) < 1) {
			return $this->_throwError("INSERT DATA is empty!");
		}

		$query = $this->_formatArray($table, [$insert], $is_ignore);
		$this->_showDebugIfNeed($query);
		$this->query($query);
		return $this->lastInsertId();
	}

	// вставить массив значенй в таблицу (возвращает количество вставленных строк)
	public function insertArray(string $table, array $list):int {

		if (!is_array($list) || count($list) < 1) {
			return $this->_throwError("INSERT DATA is empty!");
		}

		$query = $this->_formatArray($table, $list);
		$this->_showDebugIfNeed($query);
		$result = $this->query($query);
		return $result->rowCount();
	}

	// -----------------------------------------------------------------
	// PROTECTED
	// -----------------------------------------------------------------

	// ескейпим строку
	protected function _escapeString(string $value = null):string {

		if ($value === null) {
			return "NULL";
		}

		return $this->quote($value);
	}

	// удаляем кавычки из текста (нужно для названия столбцов)
	protected function _removeQuote(string $value):string {

		$value = str_replace("\"", "", $value);
		$value = str_replace("'", "", $value);
		$value = str_replace("`", "", $value);
		return $value;
	}

	// создает часть запроса из IN значений
	protected function _createIN(array $data):string {

		if (!is_array($data)) {
			$this->_throwError("Value for IN (?a) placeholder should be array");
		}
		if (!$data) {
			return "NULL";
		}
		$query = $comma = "";
		foreach ($data as $value) {
			$query .= $comma . $this->_escapeString($value);
			$comma = ",";
		}

		return $query;
	}

	// ескейпим int
	// @mixed тут что угодно
	protected function _escapeInt($value) {

		if ($value === null) {
			return "NULL";
		}

		if (!is_numeric($value)) {
			$this->_throwError("Integer (?i) placeholder expects numeric value, " . gettype($value) . " given");
		}

		if (is_float($value)) {
			$value = number_format($value, 0, ".", ""); // may lose precision on big numbers
			return $value;
		}

		return intval($value);
	}

	// форматируем array для вставки
	// @long
	protected function _formatArray(string $table, array $ar_set, bool $is_ignore = true, bool $is_delayed = false):string {

		$ins_key = true;
		$keys    = "";
		$values  = "";
		$qq      = "";
		foreach ($ar_set as $ar_query) {
			foreach ($ar_query as $key => $value) {
				if ($ins_key) {
					$keys .= "`" . $this->_removeQuote($key) . "`,";
				}

				if (is_array($value)) {
					$value = toJson($value);
				}

				$values .= $this->_escapeString($value) . ",";
			}
			$values  = substr($values, 0, -1);
			$ins_key = false;
			$qq      .= "($values),";
			$values  = "";
		}
		$keys = substr($keys, 0, -1);

		$table   = strpos($table, ".") === false ? "`$table`" : $table;
		$extra   = $is_ignore ? "IGNORE" : "";
		$delayed = $is_delayed ? "delayed" : "";
		$query   = "INSERT $delayed $extra INTO $table ($keys)  \n VALUES \n" . substr($qq, 0, -1);
		return $query;
	}

	// понимает такие вещи как ["value"=>"value + 1"] в этом случае идет инкремент, если же просто ["value"=>"3"];
	// @long
	protected function _makeQuery(array $set):string {

		$temp = [];
		foreach ($set as $k => $v) {
			// чистим название ключа
			$k = $this->_removeQuote($k);

			//
			if (is_array($v)) {
				// если массив джейсоним ))
				$v = toJson($v);
			} elseif ((inHtml($v, "-") || inHtml($v, "+")) && inHtml($v, $k)) {
				// если это контрукция инкремента / декремента вида value = value + 1
				$gg = str_replace($k, "", $v);
				$gg = str_replace("-", "", $gg);
				$gg = intval(trim(str_replace("+", "", $gg)));

				// если инкремент декремент больше 0
				if ($gg > 0) {
					if (inHtml($v, "-")) {
						$temp[] = "`{$k}` = `{$k}` - {$gg}";
						continue;
					} else {
						$temp[] = "`{$k}` = `{$k}` + {$gg}";
						continue;
					}
				}
			}

			//
			$temp[] = "`{$k}` = " . $this->_escapeString($v);
		}

		return implode(", ", $temp);
	}

	// вовзвращает корректный sql запрос
	// @long
	protected function _prepareQuery(array $args):string {

		$query = "";
		$raw   = array_shift($args);
		$array = preg_split("~(\?[siuap])~u", $raw, null, PREG_SPLIT_DELIM_CAPTURE);
		$anum  = count($args);
		$pnum  = floor(count($array) / 2);
		if ($pnum != $anum) {
			$this->_throwError("Number of args ($anum) doesn\"t match number of placeholders ($pnum) in [$raw]");
		}

		// проверяем что имя таблицы обернуто в косые кавычки `
		$table = "";
		if (
			preg_match_all("#SELECT.*?FROM(.+?) #ism", $raw, $matches) ||
			preg_match_all("#DELETE[ ]+FROM(.+?) #ism", $raw, $matches) ||
			preg_match_all("#UPDATE (.+?) #ism", $raw, $matches) ||
			preg_match_all("#INSERT.+?INTO (.+?) #ism", $raw, $matches)
		) {
			$table = trim($matches[1][0]);
		}

		if (substr($table, 0, 1) != "`" || substr($table, -1) != "`") {
			$this->_throwError("Название таблицы не обернуто в косые кавычки -> ` <-, запрос: {$raw}");
		}

		// проверяем, что нигде в запросе не переданы числа (а только все через подготовленные выражения)
		if (preg_match("#[0-9]+#ism", preg_replace("#`.+?`#ism", "", $raw))) {
			$this->_throwError("В запросе присуствуют цифры, которые не являются частью названия таблицы или полей!\nПРОВЕРЬТЕ: Все названия полей должны быть в косых ковычках, а любые значения только через переданные параметры.\n{$raw}");
		}

		//
		foreach ($array as $i => $part) {
			if (($i % 2) == 0) {
				$query .= $part;
				continue;
			}

			$value = array_shift($args);
			switch ($part) {
				case "?s":
					$part = $this->_escapeString($value);
					break;
				case "?i":
					$part = $this->_escapeInt($value);
					break;
				case "?a":
					$part = $this->_createIN($value);
					break;
				case "?u":
					$part = $this->_makeQuery($value);
					break;
				case "?p":
					$part = $value;
					break;
			}
			$query .= $part;
		}

		return $query;
	}

	// кидает исключение
	// @mixed
	protected function _throwError(string $message) {

		throw new queryException($message);
	}

	// показываем debug в запросе если надо
	protected function _showDebugIfNeed(string $query):void {

		if (!defined("DEBUG_MYSQL")) {
			return;
		}
		if (DEBUG_MYSQL !== true) {
			return;
		}

		console(blueText($query));
	}

}

// служебный класс для работы PDO
// его смысл в изменении работы функции execute
class myPDOStatement extends PDOStatement {

	// @mixed
	public function execute($data = []):self {

		parent::execute($data);
		return $this;
	}
}