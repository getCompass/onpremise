<?php

namespace BaseFrame\Database;

use BaseFrame\Database\PDODriver\DebugMode;
use BaseFrame\Exception\Gateway\QueryFatalException;
use PDOStatement;

/**
 * Класс для работы с базой данных SQL.
 *
 * Подстановки, использующиеся в запросе:
 *  ?s ("string")  - strings (also DATE, FLOAT and DECIMAL)
 *  ?i ("integer") - the name says it all
 *  ?a ("array")   - complex placeholder for IN() operator (substituted with string of 'a','b','c' format, without parentesis)
 *  ?u ("update")  - понимает такие вещи как ["value"=>"value + 1"] в этом случае идет инкремент, если же просто ["value"=>"3"];
 *  ?p ("parsed")  - special type placeholder, for inserting already parsed statements without any processing, to avoid double parsing
 */
class PDODriver extends \PDO {

	public const ISOLATION_READ_UNCOMMITTED = "READ UNCOMMITTED";
	public const ISOLATION_REPEATABLE_READ  = "REPEATABLE READ";
	public const ISOLATION_READ_COMMITTED   = "READ COMMITTED";
	public const ISOLATION_SERIALIZABLE     = "SERIALIZABLE";

	protected array       $_hooks              = [];
	protected string      $_database           = "";
	protected DebugMode   $_debug_mode         = DebugMode::NONE;

	/**
	 * Статический конструктор из конфигурационного файла.
	 */
	public static function instance(Config\Connection $conn_conf, Config\Query $query_conf):static {

		$instance = new static($conn_conf->getDSN(), $conn_conf->user, $conn_conf->password, $conn_conf->options);

		$instance->_database           = $conn_conf->db_name;
		$instance->_debug_mode         = $query_conf->debug_mode;

		/** @var \BaseFrame\Database\Hook $hook */
		foreach ($query_conf->hooks as $hook) {
			$instance->_hooks[$hook->getDb()][$hook->getTable()][$hook->getAction()][$hook->getColumn()][] = $hook;
		}

		return $instance;
	}

	/**
	 * Класс для работы с базой данных SQL.
	 */
	public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null) {

		parent::__construct($dsn, $username, $password, $options);
	}

	/**
	 * Устанавливаем уровень транзакции.
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function setTransactionIsolationLevel(string $isolation_level):bool {

		$isolation_level_list = [
			static::ISOLATION_READ_UNCOMMITTED,
			static::ISOLATION_REPEATABLE_READ,
			static::ISOLATION_READ_COMMITTED,
			static::ISOLATION_SERIALIZABLE,
		];

		if (!in_array($isolation_level, $isolation_level_list)) {
			throw new QueryFatalException("Unknown isolation level = '{$isolation_level}', please use one of myPDObasic::ISOLATION_ constants");
		}

		$query  = "SET TRANSACTION ISOLATION LEVEL {$isolation_level};";
		$result = $this->query($query);

		return $result->errorCode() == \PDO::ERR_NONE;
	}

	/**
	 * Начинаем транзакцию.
	 */
	public function beginTransaction():bool {

		$this->_debug("BEGIN");
		return parent::beginTransaction();
	}

	/**
	 * Коммитим транзакцию (может быть удачно|нет)
	 */
	public function commit():bool {

		$this->_debug("COMMIT");
		return parent::commit();
	}

	/**
	 * Коммитим транзакцию (бросаем исключение если не вышло)
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function forceCommit():void {

		if ($this->commit() === false) {
			throw new QueryFatalException("Transaction commit failed");
		}
	}

	/**
	 * Отменяем транзакцию.
	 */
	public function rollback():bool {

		$this->_debug("ROLLBACK");
		return parent::rollBack();
	}

	/**
	 * Добавляет строку в таблицу, возвращает lastInsertId(), если вставка была успешной.
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function insert(string $table, array $insert, bool $is_ignore = true):false|string {

		if (!is_array($insert) || count($insert) < 1) {
			throw new QueryFatalException("INSERT DATA is empty!");
		}

		$prepared_query = $this->_prepareInsertQuery($table, [$insert], $is_ignore);
		$this->_debug($prepared_query->queryString);
		$prepared_query->execute();
		return $this->lastInsertId();
	}

	/**
	 * Вставляет массив значений в таблицу (возвращает количество вставленных строк).
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function insertArray(string $table, array $insert):int {

		if (!is_array($insert) || count($insert) < 1) {
			throw new QueryFatalException("INSERT DATA is empty!");
		}

		$prepared_query = $this->_prepareInsertQuery($table, $insert);
		$this->_debug($prepared_query->queryString);
		$prepared_query->execute();
		return $prepared_query->rowCount();
	}

	/**
	 * Пытаемся вставить строку в таблицу, если есть пересечение по constraint
	 * обновляет имеющуюся строку. Возвращает непонятное число.
	 *
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function insertOrUpdate(string $table, array $insert, array $update = null):int {

		if (!is_array($insert) || count($insert) < 1) {
			throw new QueryFatalException("INSERT DATA is empty!");
		}

		if ($update === null) {
			$update = $insert;
		}
		[$update_part, $update_params]   = $this->_prepareUpdateQueryPart($table, $update);
		$prepared_query = $this->_prepareInsertQuery($table, [$insert], update_part: $update_part, update_params: $update_params);

		$this->_debug($prepared_query->queryString);
		$prepared_query->execute();

		return $prepared_query->rowCount();
	}

	/**
	 * Пытаемся вставить данные в таблицу, если есть пересечение по constraint обновляет их.
	 * Возвращает непонятное число, которым лучше не пользоваться.
	 *
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function insertArrayOrUpdate(string $table, array $insert, array $update):int {

		[$update_part, $update_params]   = $this->_prepareUpdateQueryPart($table, $update);
		$prepared_query = $this->_prepareInsertQuery($table, $insert, update_part: $update_part, update_params: $update_params);

		$this->_debug($prepared_query->queryString);
		$prepared_query->execute();
		return $prepared_query->rowCount();
	}

	/**
	 * Обновляет записи в БД.
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function update(string $query, string $table, ...$params):int {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_debug($query);
		$prepared_query->execute();

		return $prepared_query->rowCount();
	}

	/**
	 * Удаляет записи из базы.
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function delete(string $query, string $table, ...$params):int {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_debug($query);
		$prepared_query->execute();
		return $prepared_query->rowCount();
	}

	/**
	 * Достает одну запись из таблицы. Если запись не найдена, вернет пустой массив.
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function getOne(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_debug($query);
		$prepared_query->execute();
		$result = $prepared_query->fetch();

		if (!is_array($result)) {
			return [];
		}

		[$result] = $this->_afterRead($table, [$result]);
		return $result;
	}

	/**
	 * Достает из базы несколько записей и возвращает их как массивы строк.
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function getAll(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_debug($query);
		$prepared_query->execute();
		$result = $prepared_query->fetchAll();

		if (!is_array($result)) {
			return [];
		}

		return $this->_afterRead($table, $result);
	}

	/**
	 * Возвращает массив значений первого столбца.
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function getAllColumn(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_debug($query);
		$prepared_query->execute();
		$result = $prepared_query->fetchAll();

		if (!is_array($result) || $result === []) {
			return [];
		}

		if (count($result[0]) != 1) {
			throw new QueryFatalException("expected 1 argument for column in $query");
		}

		$selected_columns = array_keys($result[0]);

		return array_column($this->_afterRead($table, $result), $selected_columns[0]);
	}

	/**
	 * Возвращает <b>одномерный</b> ассоциативный массив записей или пустой массив
	 * В запросе ожидается ровно два столбца. Индексы массива берутся из первого столбца,
	 * значения берутся из второго
	 *
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public function getAllKeyPair(string $query, string $table, ...$params):array {

		// подготавливаем запрос (очищаем его)
		$prepared_query = $this->_getPreparedQuery($query, $table, $params);

		$this->_debug($query);
		$prepared_query->execute();
		$result = $prepared_query->fetchAll();

		if (!is_array($result) || $result === []) {
			return [];
		}

		if (count($result[0]) != 2) {
			throw new QueryFatalException("expected 2 arguments for key->pair in $query");
		}

		$selected_columns = array_keys($result[0]);
		$result           = $this->_afterRead($table, $result);

		return array_combine(array_column($result, $selected_columns[0]), array_column($result, $selected_columns[1]));
	}

	/**
	 * Вызывает все хук-обработчики после чтения данных.
	 */
	protected function _afterRead(string $table, array $rows):array {

		// если хуков для чтения для таблицы нет, то ничего не делаем
		if (!isset($this->_hooks[$this->_database][$table][Hook\Action::READ->value])) {
			return $rows;
		}

		return $this->_callHooks($this->_hooks[$this->_database][$table][Hook\Action::READ->value], $rows);
	}

	/**
	 * Вызывает все хук-обработчики перед записью данных.
	 */
	protected function _beforeWrite(string $table, array $rows):array {

		// если хуков для записи для таблицы нет, то ничего не делаем
		if (!isset($this->_hooks[$this->_database][$table][Hook\Action::WRITE->value])) {
			return $rows;
		}

		return $this->_callHooks($this->_hooks[$this->_database][$table][Hook\Action::WRITE->value], $rows);
	}

	/**
	 * Вызывает все хук-обработчики для массива строк.
	 * @throws
	 */
	protected function _callHooks(array $table_hooks, array $rows):array {

		// ожидаем, что хуков в БД меньше, чем полей в таблице, что логично
		// поэтому проход начинаем от хуков, а не от строк, так первые два
		// цикла не выполнятся больше одного раза в большинстве случаев
		foreach ($table_hooks as $columns_hooks) {

			/** @var Hook $column_hook */
			foreach ($columns_hooks as $column_hook) {

				$column_name = $column_hook->getColumn();

				foreach ($rows as $i => $row) {

					// возможно тут можно как-то еще срезать и не ходить лишний раз,
					// вряд ли строки будут различаться набором колонок, но пока
					// пусть проверяет все строки для надежности
					if (!isset($row[$column_name])) {
						continue;
					}

					// перезаписываем значение в колонке функцией хука
					try {
						$rows[$i][$column_name] = $column_hook->exec($row[$column_name]);
					} catch (\Throwable $e) {
						$rows[$i][$column_name] = $column_hook->recover($row[$column_name], $e);
					}
				}
			}
		}

		return $rows;
	}

	/**
	 * Вытаскивает из select запроса имена запрошенных полей.
	 */
	protected function _parseSelectStatementColumns(string $raw_query):array {

		$matches = [];
		preg_match("#SELECT(.*?)FROM#ism", $raw_query, $matches);

		$chunks = explode(",", trim($matches[1]));
		$names  = [];

		foreach ($chunks as $chunk) {

			$exploded = preg_split("#\s+#", trim($chunk));
			$names[]  = trim($exploded[0], "`");
		}

		return $names;
	}

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

		// выполняем преобразование перед записью данных
		$row_list = $this->_beforeWrite($table, $row_list);

		foreach ($row_list as $row) {

			if (count($row) !== $columns_count) {
				throw new QueryFatalException("insert row column count dont match");
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
			$prepared_query->bindParam($last_index, $param, is_int($param) ? self::PARAM_INT : self::PARAM_STR);
		}

		// если есть параметры для апдейта, добавляем в запрос
		if ($update_params !== []) {

			// в PDO параметры биндятся указателями
			foreach ($update_params as &$param) {

				++$last_index;
				$prepared_query->bindParam($last_index, $param, is_int($param) ? self::PARAM_INT : self::PARAM_STR);
			}
		}

		return $prepared_query;
	}

	protected function _prepareInsertRow(array $row):string {

		$token_list = str_repeat("?,", count($row) - 1) . "?";

		return "($token_list),";
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
	protected function _getPreparedQuery(string $raw, string $table_name, array $raw_param_list):PDOStatement {

		$query      = "";
		$param_list = [];

		// защита от левака
		$raw_param_list = array_values($raw_param_list);

		// проверяем что имя таблицы обернуто в косые кавычки `
		$pos = strpos($raw, "`?p`");
		if ($pos === false) {
			throw new QueryFatalException("cant find table name, query $raw");
		}
	    $raw = substr_replace($raw, "`" . $this->_removeQuote($table_name) . "`", $pos, strlen("`?p`"));

		preg_match_all("(\?[siuap])", $raw, $matches);
		$raw_marker_list = $matches[0];
		$marker_list     = [];
		$param_list      = [];

		$param_count  = count($raw_param_list);
		$marker_count = count($raw_marker_list);

		if ($param_count != $marker_count) {
			throw new QueryFatalException("Number of args ($param_count) doesn\"t match number of placeholders ($marker_count) in [$raw]");
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

			$marker_list[] = match($marker) {
				"?p"       => $param,
				"?s", "?i" => "?",
				"?a"       => count($param) > 0 ? str_repeat("?,", count($param) - 1) . "?" : "NULL",
				"?u"       => $update_query_part
			};

			// для ?p параметр добавлять не надо
			if ($marker === "?p") {
				continue;
			}

			in_array($marker, ["?u", "?a"]) ? $param_list = array_merge($param_list, $param) : $param_list[] = $param;
		}

		// проверяем, что нигде в запросе не переданы числа (а только все через подготовленные выражения)
		if (preg_match("#[0-9]+#ism", preg_replace("#`.+?`#ism", "", $raw))) {
			throw new QueryFatalException("В запросе присуствуют цифры, которые не являются частью названия таблицы или полей!\nПРОВЕРЬТЕ: Все названия полей должны быть в косых ковычках, а любые значения только через переданные параметры.\n{$raw}");
		}

		// заменяем все плейсхолдерами
		$query = preg_replace_callback("(\?[siuap])", function(array $_) use (&$marker_list) { return (string) array_shift($marker_list);}, $raw);

		if (!inHtml(strtolower($query), "limit") || !inHtml(strtolower($query), "where")) {
			throw new QueryFatalException("WHERE or LIMIT not found on SQL: {$query}");
		}

		// подготавливаем запрос
		$prepared_query = $this->prepare($query);

		// приклеиваем параметры
		// указатель нужен, так как PDO приклеивает к запросу параметр именно по указателю
		foreach($param_list as $index => &$param) {
			$prepared_query->bindParam($index + 1, $param, is_int($param) ? self::PARAM_INT : self::PARAM_STR);
		}

		return $prepared_query;
	}

	// понимает такие вещи как ["value"=>"value + 1"] в этом случае идет инкремент, если же просто ["value"=>"3"];
	// @long
	protected function _prepareUpdateQueryPart(string $table, array $set):array {

		$temp = [];
		$param_list = [];

		[$set] = $this->_beforeWrite($table, [$set]);

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
	 * Пишем данные для отладки.
	 */
	protected function _debug(string $query):void {

		if ($this->_debug_mode === DebugMode::CLI) {
			console($query);
		}

		if ($this->_debug_mode === DebugMode::FILE) {
			debug($query);
		}
	}

	// удаляем кавычки из текста (нужно для названия столбцов)
	protected function _removeQuote(string $value):string {

		return str_replace(["\"", "`", "'"], "", $value);
	}
}

