<?php declare(strict_types=1);

namespace BaseFrame\Search;

use BaseFrame\Search\Config\Connection;

/**
 * Класс, реализующий работу с manticore-search хранилищем.
 */
class Manticore extends \PDO {

	protected const OPTION_CHECK_LIMIT = 1 << 0;
	protected const OPTION_CHECK_WHERE = 1 << 1;

	protected string $last_query        = "";
	protected float  $last_execution_at = 0.0;

	/**
	 * Статический конструктор для мантикоры.
	 * Создает подключение средствами pdo.
	 *
	 * @param Connection $config
	 * @return Manticore
	 */
	public static function instance(Config\Connection $config):self {

		// устанавливаем атрибуты подключения
		$opt = [\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION];

		// создаем pdo подключение к контейнеру мантикоры
		return new self("mysql:host={$config->host};port={$config->port}", "", "", $opt);
	}

	/**
	 * Выполняет вставку записей.
	 *
	 * @param string $index
	 * @param array  $insert_item_list
	 *
	 * @throws Exception\ExecutionException
	 */
	public function insert(string $index, array $insert_item_list):void {

		$query  = $this->_prepareInsertArrayList($index, $insert_item_list);
		$result = $this->query($query);

		if (!$result) {
			throw new Exception\ExecutionException("ошибка запроса \n{$query} \n{$this->errorInfo()[2]}");
		}
	}

	/**
	 * Выполняет выборку для указанного запроса.
	 *
	 * @param string $query
	 * @param array  $query_arguments
	 *
	 * @return array
	 * @throws Exception\ExecutionException
	 */
	public function select(string $query, array $query_arguments):array {

		$query  = $this->_prepareQuery($query, $query_arguments, self::OPTION_CHECK_WHERE | self::OPTION_CHECK_LIMIT);
		$result = $this->query($query);

		if (!$result) {
			throw new Exception\ExecutionException("ошибка запроса \n{$query} \n{$this->errorInfo()[2]}");
		}

		$output = $result->fetchAll();

		foreach ($output as $key => $row) {
			$output[$key] = array_filter($row, static fn(mixed $key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
		}

		return $output;
	}

	/**
	 * Выполняет обновление записи.
	 * Не обновляет индексированные данные, то есть строка для поиска не изменится!
	 *
	 * @param string $query
	 * @param array  $query_arguments
	 *
	 * @throws Exception\ExecutionException
	 * @noinspection DuplicatedCode
	 */
	public function update(string $query, array $query_arguments):void {

		$query  = $this->_prepareQuery($query, $query_arguments);
		$result = $this->query($query);

		if (!$result) {
			throw new Exception\ExecutionException("ошибка запроса \n{$query} \n{$this->errorInfo()[2]}");
		}
	}

	/**
	 * Выполняет обновление записи.
	 * Не обновляет индексированные данные, то есть строка для поиска не изменится!
	 *
	 * @param string $index
	 * @param array  $insert_item_list
	 *
	 * @throws Exception\ExecutionException
	 * @noinspection DuplicatedCode
	 */
	public function replace(string $index, array $insert_item_list):void {

		$query  = $this->_prepareReplaceArrayList($index, $insert_item_list);
		$result = $this->query($query);

		if (!$result) {
			throw new Exception\ExecutionException("ошибка запроса \n{$query} \n{$this->errorInfo()[2]}");
		}
	}

	/**
	 * Выполняет удаление записи.
	 *
	 * @param string $query
	 * @param array  $query_arguments
	 *
	 * @throws Exception\ExecutionException
	 * @noinspection DuplicatedCode
	 */
	public function delete(string $query, array $query_arguments):void {

		// подготавливаем запрос
		$query  = $this->_prepareQuery($query, $query_arguments);
		$result = $this->query($query);

		if (!$result) {
			throw new Exception\ExecutionException("ошибка запроса \n{$query} \n{$this->errorInfo()[2]}");
		}
	}

	/**
	 * Выполняет запрос.
	 * Обертка над стандартным методом с фиксацией времени исполнения.
	 *
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function query(string $query, int|null $fetch_mode = null, ...$fetch_mode_args):\PDOStatement|false {

		$this->_before($query);
		$result = parent::query($query, $fetch_mode, $fetch_mode_args);
		$this->_after();

		return $result;
	}

	# region protected

	/**
	 * Подготавливает массив для вставки в индекс.
	 *
	 * @param string $table
	 * @param array  $insert_item_list
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareInsertArrayList(string $table, array $insert_item_list):string {

		// проверяем, что входной массив можно использовать для вставки
		$this->_checkInsertItemList($insert_item_list);

		// формируем выражения для подстановки в запрос
		$keys_expression  = $this->_prepareKeyListForInsert($insert_item_list);
		$value_expression = $this->_prepareValueListForInsert($insert_item_list);

		$table = $this->_prepareTableArgument($table);

		// формируем сам запрос
		return "INSERT INTO {$table} {$keys_expression} VALUES {$value_expression}";
	}

	/**
	 * Подготавливает массив для вставки в индекс.
	 *
	 * @param string $table
	 * @param array  $replace_item_list
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareReplaceArrayList(string $table, array $replace_item_list):string {

		// проверяем, что входной массив можно использовать для вставки
		$this->_checkReplaceItemList($replace_item_list);

		// формируем выражения для подстановки в запрос
		$keys_expression  = $this->_prepareKeyListForInsert($replace_item_list);
		$value_expression = $this->_prepareValueListForInsert($replace_item_list);

		$table = $this->_prepareTableArgument($table);

		// формируем сам запрос
		return "REPLACE INTO {$table} {$keys_expression} VALUES {$value_expression}";
	}

	/**
	 * Проверяет, что входной массив можно использовать для вставки значений.
	 *
	 * @param array $to_insert_list
	 *
	 * @throws Exception\ExecutionException
	 */
	protected function _checkInsertItemList(array $to_insert_list):void {

		if (count($to_insert_list) === 0) {
			throw new Exception\ExecutionException("передан пустой список с данными для запроса");
		}

		// берем первый элемент за эталонный
		$reference = reset($to_insert_list);

		// ключи для вставки из элемента
		$keys = array_keys($reference);

		foreach ($to_insert_list as $compare) {

			if (!is_array($compare)) {
				throw new Exception\ExecutionException("для вставки передан не массив");
			}

			if (count($compare) !== count($keys)) {
				throw new Exception\ExecutionException("для вставки передан список несовместимых массивов");
			}

			$current_index = 0;

			foreach ($compare as $k => $_) {

				if ($k !== $keys[$current_index]) {
					throw new Exception\ExecutionException("для вставки передан список несовместимых массивов");
				}

				$current_index++;
			}
		}
	}

	/**
	 * Проверяет, что входной массив можно использовать для замены значений.
	 *
	 * @param array $to_replace_list
	 *
	 * @throws Exception\ExecutionException
	 */
	protected function _checkReplaceItemList(array $to_replace_list):void {

		if (count($to_replace_list) === 0) {
			throw new Exception\ExecutionException("передан пустой список с данными для запроса");
		}

		foreach ($to_replace_list as $replace_item) {

			if (!is_array($replace_item)) {
				throw new Exception\ExecutionException("для replace вызова передан неверный набор данных, ожидали массив");
			}

			if (!isset($replace_item["id"])) {
				throw new Exception\ExecutionException("для replace передан элемент без идентификатор документа");
			}

			if (count($replace_item) === 1) {
				throw new Exception\ExecutionException("для replace вызова передан только идентификатор");
			}
		}
	}

	/**
	 * Подготавливает выражение для вставки ключей.
	 *
	 * @param array $insert_item_list
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareKeyListForInsert(array $insert_item_list):string {

		$prepared_key_list = [];

		// берем первый элемент за эталонный и получаем из него ключи
		$reference = reset($insert_item_list);
		$key_list  = array_keys($reference);

		foreach ($key_list as $v) {
			$prepared_key_list[] = $this->_prepareKeyArgument($v);
		}

		// выражение для ключей
		return "(" . implode(", ", $prepared_key_list) . ")";
	}

	/**
	 * Подготавливает выражение для вставки значений.
	 *
	 * @param array $insert_item_list
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareValueListForInsert(array $insert_item_list):string {

		$value_expression_list = [];

		foreach ($insert_item_list as $insert_item) {

			$prepared_value_list = [];

			foreach ($insert_item as $value) {

				if (is_array($value)) {
					$prepared_value_list[] = count($value) > 0 ? self::_prepareValueListForInsert([$value]) : "()";
				} elseif (is_scalar($value)) {
					$prepared_value_list[] = is_string($value) ? $this->_prepareStringArgument($value) : $this->_prepareIntegerArgument($value);
				} else {
					throw new Exception\ExecutionException("передан non-scalar/non-array тип данных для запроса");
				}
			}

			$value_expression_list[] = "(" . implode(", ", $prepared_value_list) . ")";
		}

		// выражение для значений
		return implode(", ", $value_expression_list);
	}

	/**
	 * Выполняет подготовку запроса.
	 *
	 * @param string $raw_query
	 * @param array  $query_argument_list
	 * @param int    $options
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareQuery(string $raw_query, array $query_argument_list, int $options = self::OPTION_CHECK_WHERE):string {

		// разбиваем запрос на отдельные части
		$query_chunk_list = preg_split("~(\?as|\?an|\?au|\?[ktsi])~u", $raw_query, -1, PREG_SPLIT_DELIM_CAPTURE);

		// число аргументов и подстановок
		$query_argument_count = count($query_argument_list);
		$placeholder_count    = floor(count($query_chunk_list) / 2);

		if ($query_argument_count != $placeholder_count) {
			throw new Exception\ExecutionException("количество аргументов {$query_argument_count} не соответствует количество подстановок {$placeholder_count} в запросе [{$raw_query}]");
		}

		// проверяем, что нигде в запросе не переданы числа (а только все через подготовленные выражения)
		if (preg_match("#[0-9]+#im", preg_replace("#`.+?`#ism", "", $raw_query))) {
			throw new Exception\ExecutionException("в запросе присутствуют цифры, которые не являются частью названия таблицы или полей!\nПРОВЕРЬТЕ: Все названия полей должны быть в косых кавычках, а любые значения только через переданные параметры.\n{$raw_query}");
		}

		// итоговая строка запроса с подстановками
		$query = self::_prepareQueryString($query_chunk_list, $query_argument_list);

		// проверяем, что итоговая строка в норме
		self::_validateQueryString($query, $options);

		return $query;
	}

	/**
	 * Выполняет подготовку запроса.
	 *
	 * @param array $query_chunk_list
	 * @param array $query_argument_list
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 *
	 * @long switch-case
	 */
	protected function _prepareQueryString(array $query_chunk_list, array $query_argument_list):string {

		$output = "";

		foreach ($query_chunk_list as $k => $query_chunk) {

			if (($k % 2) == 0) {

				$output .= $query_chunk;
				continue;
			}

			$value = array_shift($query_argument_list);

			$query_chunk = match ($query_chunk) {
				"?s"  => $this->_prepareStringArgument($value),
				"?i"  => $this->_prepareIntegerArgument($value),
				"?k"  => $this->_prepareKeyArgument($value),
				"?t"  => $this->_prepareTableArgument($value),
				"?as" => $this->_prepareInStringArgument($value),
				"?an" => $this->_prepareInIntegerArgument($value),
				"?au" => $this->_prepareUpdateArrayArgument($value),
			};

			$output .= $query_chunk;
		}

		return $output;
	}

	/**
	 * Подготавливаем ключ для подстановки в запрос.
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareKeyArgument(string $value):string {

		$value = str_replace("\"", "", $value);
		$value = str_replace("'", "", $value);
		$value = str_replace("`", "", $value);

		if (mb_strlen($value) === 0) {
			throw new Exception\ExecutionException("значение для подстановки ключа не может быть пустым");
		}

		return "`$value`";
	}

	/**
	 * Подготавливаем название таблицы для подстановки в запрос.
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareTableArgument(string $value):string {

		return self::_prepareKeyArgument($value);
	}

	/**
	 * Подготавливаем строку для подстановки в запрос.
	 *
	 * @param ?string $value
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareStringArgument(string $value = null):string {

		if ($value === null) {
			return "NULL";
		}

		if (!is_string($value)) {
			throw new Exception\ExecutionException("строковые подстанвоки (?s, ?as, ?au mva) ожидают string значение, передано — " . gettype($value) . " given");
		}

		return $this->quote($value);
	}

	/**
	 * Подготавливаем целочисленное значение в виде строки для подстановки в запрос.
	 *
	 * @param int|float|null $value
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareIntegerArgument(int|float|null $value):string {

		if ($value === null) {
			return "NULL";
		}

		if (!is_numeric($value)) {
			throw new Exception\ExecutionException("целочисленные (?i, ?ai, ?au mva) подстанвоки ожидают ineger или float значение, передано — " . gettype($value) . " given");
		}

		if (is_float($value)) {
			return number_format($value, 0, ".", "");
		}

		return (string) intval($value);
	}

	/**
	 * Подготавливает строку из строк для формирование IN части запроса для строк.
	 * Мантикора чувствительная к типам в IN выражении, поэтому строки и числа нужно готовить по отдельности!
	 *
	 * @param array $data
	 *
	 * @return string
	 *
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareInStringArgument(array $data):string {

		if (count($data) === 0) {
			return "NULL";
		}

		$output = [];

		foreach ($data as $value) {
			$output[] = $this->_prepareStringArgument($value);
		}

		// преобразуем массив в строку
		return implode(", ", $output);
	}

	/**
	 * Подготавливает строку из целых чисел для формирование IN части запроса для строк.
	 * Мантикора чувствительная к типам в IN выражении, поэтому строки и числа нужно готовить по отдельности!
	 *
	 * @param array $data
	 *
	 * @return string
	 *
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareInIntegerArgument(array $data):string {

		if (count($data) === 0) {
			return "0";
		}

		$output = [];

		foreach ($data as $value) {
			$output[] = $this->_prepareIntegerArgument($value);
		}

		// преобразуем массив в строку
		return implode(", ", $output);
	}

	/**
	 * Подготавливает сроку для UPDATE запроса на основе массива.
	 * Мантикора чувствительная к типам поэтому строки, числа и mva нужно готовить по отдельности!
	 *
	 * @param array $data
	 *
	 * @return string
	 *
	 * @throws Exception\ExecutionException
	 */
	protected function _prepareUpdateArrayArgument(array $data):string {

		if (count($data) === 0) {
			throw new Exception\ExecutionException("передан пустой список для апдейт значения");
		}

		$key_expression_list   = [];
		$value_expression_list = [];

		foreach ($data as $key => $value) {

			// ключ форматируем в строку
			$key_expression_list[] = $this->_prepareKeyArgument($key);

			// мантикора любит строгие типы, поэтому нужно убедиться, что значение переводится в правильный тип
			$value_expression_list[] = $this->_parseUpdateArrayValue($value);
		}

		$output = [];

		// пробегаемся по массиву и формируем строку данных для апдейта
		foreach (array_combine($key_expression_list, $value_expression_list) as $key => $value) {
			$output[] = "{$key} = {$value}";
		}

		// преобразуем массив в строку
		return implode(", ", $output);
	}

	/**
	 * Парсит элементы для обновления с помощью массива данных.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 * @throws Exception\ExecutionException
	 */
	protected function _parseUpdateArrayValue(mixed $value):string {

		// для mva атрибутов возможны массивы
		if (is_array($value)) {

			// если массив пустой, то передаем пустой список
			if (count($value) === 0) {
				return "()";
			} elseif (is_int(reset($value))) {
				return "(" . $this->_prepareInIntegerArgument($value) . ")";
			} else {
				return "(" . $this->_prepareInStringArgument($value) . ")";
			}
		} else {

			return is_int($value) ? $this->_prepareIntegerArgument($value) : $this->_prepareStringArgument($value);
		}
	}

	/**
	 * Проверяет, что запрос на выходе имеет корректный формат.
	 *
	 * @param string $query
	 * @param int    $options
	 *
	 * @throws Exception\ExecutionException
	 */
	protected static function _validateQueryString(string $query, int $options):void {

		$matches = [];

		// проверяем, что запрос имеет корректный вид
		if (
			!preg_match_all("#SELECT.*?FROM\s+(.+?)\s+#ism", $query, $matches) &&
			!preg_match_all("#DELETE +FROM\s+(.+?)\s+#ism", $query, $matches) &&
			!preg_match_all("#UPDATE\s+(.+?)\s+#ism", $query, $matches) &&
			!preg_match_all("#INSERT.+?INTO\s+(.+?)\s+#ism", $query, $matches) &&
			!preg_match_all("#REPLACE.+?INTO\s+(.+?)\s+#ism", $query, $matches)
		) {
			throw new Exception\ExecutionException("сформированный запрос имеет некорректный формат [{$query}]");
		}

		// получаем таблицу из запроса
		$table = trim($matches[1][0]);

		// убеждаемся, что таблица экранирована
		if (!str_starts_with($table, "`") || !str_ends_with($table, "`")) {
			throw new Exception\ExecutionException("название таблицы не обернуто в косые кавычки -> ` <-, запрос: {$query}");
		}

		if (($options & self::OPTION_CHECK_LIMIT) > 0 && preg_match("#\s+limit\s+#", mb_strtolower($query)) !== 1) {
			throw new Exception\ExecutionException("не передан лимит в запрос: {$query}");
		}

		if (($options & self::OPTION_CHECK_WHERE) > 0 && preg_match("#\s+where\s+#", mb_strtolower($query)) !== 1) {
			throw new Exception\ExecutionException("не передано условие в запрос: {$query}");
		}
	}

	/**
	 * Выполняет логику до исполнения запроса.
	 */
	protected function _before(string $query):void {

		$this->last_query        = $query;
		$this->last_execution_at = microtime(true);
	}

	/**
	 * Выполняет логику после исполнения запроса.
	 */
	protected function _after():void {

		if (defined("MANTICORE_SEARCH_QUERY_LOG_ENABLED") && MANTICORE_SEARCH_QUERY_LOG_ENABLED === true) {

			$done_in = (microtime(true) - $this->last_execution_at) * 1000;
			\BaseFrame\Monitor\Core::log(sprintf("manticore-query %s executed in %.2f ms", $this->last_query, $done_in))->seal();
		}
	}

	# endregion protected
}