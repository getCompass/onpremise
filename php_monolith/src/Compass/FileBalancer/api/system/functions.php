<?php

namespace Compass\FileBalancer;

// ------------------------------------------------
// содержатся вспомогательные функции системы
// ------------------------------------------------

####################################################
# region        Работа с API
####################################################

// получаем get параметр из запроса
// @mixed
function get(string $field, $default = null) {

	return $_GET[$field] ?? $default;
}

// получаем post параметр из запроса
// @mixed
function post(string $field, $default = null) {

	return $_POST[$field] ?? $default;
}

// получаем request параметр из запроса
// @mixed
function request(string $field, $default = null) {

	return $_REQUEST[$field] ?? $default;
}

// получаем cookie параметр из запроса
// @mixed
function cookie(string $field, $default = null) {

	return $_COOKIE[$field] ?? $default;
}

// возвращает число ограниченное min & max
function limit(int $value, int $min = null, int $max = null):int {

	$value = intval($value);
	if ($min !== null && $value < $min) {
		$value = $min;
	}
	if ($max !== null && $value > $max) {
		$value = $max;
	}

	return $value;
}

// возвращает device id
// на iOS меняется при переустановке приложения
// на android (на 25 февраля 2020 года) шлют очень редко (3 случая из 244 известных)
function getDeviceId():string {

	if (!isset($_SERVER["HTTP_X_BM_DEVICE_ID"])) {
		return "";
	}

	// обрабатываем device_id, обрезаем до максимального размера
	$device_id = formatString($_SERVER["HTTP_X_BM_DEVICE_ID"]);
	$device_id = mb_strimwidth($device_id, 0, 36);

	return $device_id;
}

// возварщает client_launch_uuid
function getClientLaunchUUID():string {

	// получаем заголовок
	$client_launch_uuid = $_SERVER["HTTP_CLIENT_LAUNCH_UUID"] ?? "";

	// обрезаем до макс размера
	$client_launch_uuid = mb_substr($client_launch_uuid, 0, 255);

	// форматируем
	return formatString($client_launch_uuid);
}

// возвращает айпи адрес того, кто выполняет текущий скрипт
function getIp():string {

	if (isCron()) {
		return "127.1.1.1";
	}

	if (isset($_SERVER["HTTP_X_REAL_IP"])) {
		return $_SERVER["HTTP_X_REAL_IP"];
	}

	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_X_FORWARDED_FOR"];
	}

	return $_SERVER["REMOTE_ADDR"] ?? "127.1.1.1";
}

// возвращает user agent пользователя
function getUa():string {

	if (!isset($_SERVER["HTTP_USER_AGENT"]) || $_SERVER["HTTP_USER_AGENT"] == "") {
		$_SERVER["HTTP_USER_AGENT"] = "robot";
	}

	$user_agent = formatString($_SERVER["HTTP_USER_AGENT"]);

	return $user_agent;
}

// true - если Ajax запрос
function isAjax():bool {

	return (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" || (isset($GLOBALS["ajax"]) && $GLOBALS["ajax"] === true));
}

// true - если крон
function isCron():bool {

	if (!defined("IS_CRON") || IS_CRON === false) {
		return false;
	}

	return true;
}

// true - если unit test
function isUtest():bool {

	if (!defined("CODECEPTION_TEST_RUNNING") || CODECEPTION_TEST_RUNNING === false) {
		return false;
	}

	return true;
}

// true - если работа из консоли
function isCLi():bool {

	if (php_sapi_name() == "cli") {
		return true;
	}

	return false;
}

// возвращает country_code текущего запроса
function getRequestCountryCode():string {

	$country_code = geoip_country_code_by_name(getIp());

	if (!$country_code) {
		return "ru";
	}

	return strtolower($country_code);
}

// true - если ip офисный
function isOfficeIp():bool {

	$ip_address = getIp();

	// получаем массив с IP адресами компании
	$allow_ip = getConfig("GLOBAL_OFFICE_IP");

	// проверяем наличие IP адреса среди указанных в конфигурации
	if (!in_array($ip_address, $allow_ip)) {
		return false;
	}

	return true;
}

// true - если это тестовый запуск (dry-run в параметрах скрипта)
function isDryRun():bool {

	global $argv;

	$arr = array_slice($argv, 1);
	foreach ($arr as $item) {

		$item = strtolower($item);
		if (inHtml($item, "dry-run") || inHtml($item, "dryrun")) {
			return true;
		}
	}

	return false;
}

/**
 * Проверяем, имеется ли аргумент в параметрах скрипта
 *
 * @param string $argument
 *
 * @return bool
 */
function isArgumentExist(string $argument):bool {

	global $argv;

	$arr = array_slice($argv, 1);
	foreach ($arr as $item) {

		$item = strtolower($item);
		if (inHtml($item, $argument)) {
			return true;
		}
	}

	return false;
}

/**
 * Получаем и возвращаем значение аргумента
 *
 * @param string $argument
 *
 * @return string
 */
function getArgumentValue(string $argument):string {

	global $argv;

	$args = array_slice($argv, 1);
	foreach ($args as $item) {

		// если не подходящий аргумент, то пропускаем
		if (!inHtml($item, $argument)) {
			continue;
		}

		// если нет значения, то сори
		if (!inHtml($item, "=")) {
			continue;
		}

		$temp = explode("=", $item);
		if (count($temp) < 2) {
			continue;
		}

		return $temp[1];
	}

	return "";
}

// получаем значение заголовка
function getHeader(string $key):string {

	// получаем заголовок
	$value = $_SERVER[$key] ?? "";

	// обрезаем
	$value = mb_substr($value, 0, 255);

	// форматируем
	return formatString($value);
}

# endregion
####################################################

####################################################
# region        ДЕБАГ И ОТОБРАЖЕНИЕ
####################################################

// форматирует аргументы в строку
function dd():string {

	$out  = [];
	$vars = func_get_args();
	foreach ($vars as $v) {
		$out[] = print_r($v, true);
	}

	$text = implode(" | ", $out);

	return $text;
}

// заменяет в строке выражения обернутые в {} на элементы массива по ключу
// пример: format("после замены это {value} значение будет равно одному",["value"=>1]);
function format(string $txt, array $replace):string {

	foreach ($replace as $key => $value) {
		//echo dd($key,$value);
		$txt = @str_replace("{" . $key . "}", $value, $txt);
		$txt = @str_replace("{" . strtolower($key) . "}", $value, $txt);
		$txt = @str_replace("{" . strtoupper($key) . "}", $value, $txt);
	}

	return $txt;
}

// выводит текст в консоль
function console():void {

	if (!isCLi()) {
		return;
	}

	$vars = func_get_args();
	foreach ($vars as $v) {

		$v = dd($v);

		echo "{$v}\n";
	}
}

# endregion
####################################################

####################################################
# region        РАБОТА СО ВРЕМЕНЕМ
####################################################

// количество недель прошедших с начала года
function weekNum(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return limit(intval(date("W", $time)));
}

// количество дней прошедших с начала года
function dayNum(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return limit(intval(date("z", $time)));
}

// время начала текущего дня
function dayStart(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return mktime(0, 0, 0, date("m", $time), date("d", $time), date("Y", $time));
}

// время начала текущей недели
function weekStart(int $time = null):int {

	if ($time === null) {
		$time = time();
	}
	$start = (date("w", $time) == 1) ? $time : strtotime("last monday", $time);

	return dayStart($start);
}

// время начала текущего месяца
function monthStart(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	$start = strtotime(date("Y-m", $time));
	return dayStart($start);
}

// время начала текущего часа
function hourStart(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return mktime(date("H", $time), 0, 0, date("m", $time), date("d", $time), date("Y", $time));
}

// сколько времени прошло от начала дня
function fromDayStart():int {

	return time() - mktime(0, 0, 0, date("m"), date("d"));
}

// через сколько кончится день
function tillDayEnd():int {

	return mktime(0, 0, 0, date("m"), date("d") + 1) - time();
}

// через сколько кончится завтрашний день
function tillTomorrowEnd():int {

	return mktime(0, 0, 0, date("m"), date("d") + 1) - time() + HOUR24;
}

# endregion
####################################################

####################################################
# region         КОНФИГИ
####################################################

// возвращает значение конфига
function getConfig(string $code):array {

	global $CONFIG;
	$code = strtoupper($code);
	if (isset($CONFIG[$code])) {
		return $CONFIG[$code];
	}

	$codes = explode("_", $code);
	$file  = strtolower($codes[0]);

	loadConfig($file);
	if (!isset($CONFIG[$code])) {
		return [];
	}

	return $CONFIG[$code];
}

// перезаписывает конфиг
// @mixed
function setConfig(string $code, $data):void {

	global $CONFIG;
	$code          = strtoupper($code);
	$CONFIG[$code] = $data;
}

// загружаем конфиг из файла конфигов /api/conf
function loadConfig(string $file):void {

	global $CONFIG;
	$_ = $CONFIG;
	$path = PATH_API . "/conf/" . $file . ".php";
	if (file_exists($path)) {
		include($path);
	}
}

// возвращает значение структуры
// @mixed
function getStructure(string $code) {

	global $STRUCTURE;
	$code = strtoupper($code);
	if (isset($STRUCTURE[$code])) {
		return $STRUCTURE[$code];
	}

	$codes = explode("_", $code);
	$file  = strtolower($codes[0]);

	loadStructure($file);
	if (!isset($STRUCTURE[$code])) {
		return [];
	}

	return $STRUCTURE[$code];
}

// загружаем структуру из файла структур /api/structure
function loadStructure(string $file):void {

	global $STRUCTURE;
	$_ = $STRUCTURE;
	$path = PATH_API . "/structure/" . $file . ".php";
	if (file_exists($path)) {
		include($path);
	}
}

// генерирует поле schemas для одного месяца в sharding.php
function makeMonthShardingSchemas(string $db_postfix, array $table_list, array $extra_merge_list = []):array {

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

// генерирует поле schemas для таблиц от 0 до hex (например ff)
function makeHexShardingSchemas(string $max_hex, array $table_list, array $extra_merge_list = []):array {

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

// генерирует поле schemas для таблиц от 0 до int
function makeIntShardingSchemas(int $from, int $to, array $table_list, array $extra_merge_list = []):array {

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

# endregion
####################################################

####################################################
# region         ШИФРОВАНИЕ
####################################################

// вернуть телефон со звездочками
function obfusticatePhone(string $phone_number):string {

	return substr($phone_number, 0, 2) . "********" . substr($phone_number, -2);
}

// вернуть email со звездочками
function obfusticateEmail(string $email):string {

	$parts = explode("@", $email);

	$output = substr($parts[0], 0, 1) . "*****" . substr($parts[0], -1);
	$output .= "@";
	$output .= substr($parts[1], 0, 1) . "***" . substr($parts[1], -3);

	return $output;
}

// вернуть массив отсортированный в едином формате для создания подписи
function sortArrayStable(array &$array, int $sort_flags = SORT_REGULAR):bool {

	$index = 0;
	foreach ($array as &$item) {
		$item = _convertObjectToArray($item);
		$item = [
			$index++,
			$item,
		];
	}
	$result = uasort($array, function(array $a, array $b) use ($sort_flags) {

		if ($a[1] == $b[1]) {
			return $a[0] - $b[0];
		}
		$set = _makeSet($a, $b);
		asort($set, $sort_flags);
		reset($set);
		return key($set);
	});
	foreach ($array as &$item) {
		$item = $item[1];
	}
	return $result;
}

// преобразуем объект в массив
// @mixed
function _convertObjectToArray($item) {

	if (is_object($item)) {
		$item = (array) $item;
	}

	return $item;
}

// формируем set
function _makeSet(array $a, array $b):array {

	return [
		-1 => $b[1],
		1  => $a[1],
	];
}

# endregion
####################################################

####################################################
# region         JSON
####################################################

// alias системной функции, так как здесь возможны модификации
// и важно чтобы все кодирование JSON шло через одну функцию
// @mixed
function toJson($input):string {

	return json_encode($input);
}

// раскодировать json
function fromJson(string $input):array {

	$info = json_decode($input, true, 512, JSON_BIGINT_AS_STRING);
	if (!is_array($info)) {
		return [];
	}

	return $info;
}

# endregion
####################################################

####################################################
# region         ПРОЧЕЕ
####################################################

// устанавливает константу, если такая еще не установлена
// @mixed
function ddefine(string $key, $value):void {

	if (!defined($key)) {
		define($key, $value);
	}
}

// выводит в консоль форматированный массив, чтобы его можно скопировать и вставить в РНР скрипт
function toPhpArray(array $list):string {

	$txt  = "array(\n";
	$ar_v = [];
	foreach ($list as $key => $value) {
		$key = trim($key);

		if (is_array($value)) {
			$value  = toPhpArray($value);
			$ar_v[] = "\t\"{$key}\"	=> {$value}";
		} else {
			$value  = trim($value);
			$ar_v[] = "\t\"{$key}\"	=> \"{$value}\"";
		}
	}

	$txt .= implode(",\n", $ar_v);
	$txt .= "\n)";

	return $txt;
}

// проверяет, есть ли искомое значение в переданной строке
function inHtml(string $html, string $str):bool {

	return substr_count($html, $str) ? true : false;
}

// собрать случайную строку
function generateRandomString(int $length = 10):string {

	$characters       = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$charactersLength = strlen($characters);
	$randomString     = "";
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}

	return $randomString;
}

// случайную сессию
function generateUUID(bool $type = true):string {

	$temp = bin2hex(openssl_random_pseudo_bytes(16));

	if (!$type) {
		return $temp;
	}

	$uuid = implode("-", [
		substr($temp, 0, 8),
		substr($temp, 8, 4),
		substr($temp, 12, 4),
		substr($temp, 16, 4),
		substr($temp, 20, 12),
	]);

	return $uuid;
}

// отдать ajax в браузер
// @mixed
function showAjax($output):void {

	if (!headers_sent()) {
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: application/json;charset=" . CONFIG_WEB_CHARSET);
	}

	$txt = is_array($output) ? toJson($output) : $output;
	echo $txt;
}

// возвращает 40 символьную длину
// @mixed
function getHash($salt = null):string {

	if ($salt === null) {
		$salt = random_int(256, 512);
	}

	return sha1(openssl_random_pseudo_bytes($salt));
}

// возвращает уникальный id
function getUniqId(int $length = 64) {

	return openssl_random_pseudo_bytes($length);
}

// оставляем только цифры
// @mixed
function formatInt($value):int {

	$value = trim($value);
	$value = str_replace(",", ".", $value);
	$value = preg_replace("#[^0-9\.-]*#ism", "", $value);
	return intval($value);
}

// удаляем все не UTF последовательности (вдоичный левак, битые символы и тп)
// https://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
// @mixed
function formatString($value):string {

	$regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;

	return trim(preg_replace($regex, "$1", $value));
}

// оставляем только телефон
// @mixed
function formatPhone($value):string {

	$value = trim(formatInt($value));

	if (substr($value, 0, 1) == "8") {
		$value = "7" . substr($value, 1);
	}

	return $value;
}

// форматируем только на целочисленный тип
// @mixed
function formatFloat($value):float {

	$value = trim($value);
	$value = str_replace(",", ".", $value);
	$value = preg_replace("#[^0-9\.-]*#ism", "", $value);
	return floatval($value);
}

// форматируем email
// @mixed
function formatEmail($value):string {

	$value = strtolower($value);
	return filter_var($value, FILTER_SANITIZE_EMAIL);
}

// форматируем hash
// @mixed
function formatHash($value):string {

	$value = preg_replace("#[^a-zA-Z0-9_\-=]*#ism", "", $value);
	return $value;
}

// debug
function debug(... $arr):void {

	$text = date(DATE_FORMAT_FULL_S, time()) . "\n";

	foreach ($arr as $value) {
		$text .= dd($value);
		$text .= "\n";
	}

	@file_put_contents(PATH_LOGS . "debug.log", $text, FILE_APPEND);
}

// получаем доменное имя
function getDomain():string {

	// получаем server_name
	$server_name = $_SERVER["SERVER_NAME"] ?? SERVER_NAME;

	// разбиваем по точке - например conversation.dev1.compass.ru
	$tmp = explode(".", $server_name);

	// получаем последние два элемента и делаем строку
	$domain_name = implode(".", array_slice($tmp, count($tmp) - 2));

	return $domain_name;
}

// проверяем с какого стройства зашел пользователь
function isMobile():bool {

	if (isset($_SERVER["HTTP_USER_AGENT"])) {

		// паттерн для проверки
		$mobile_agents = "!(android|iphone|phone|symbian|ipod|blackberry|webos)!i";

		// проверяем на соотвествие паттерну
		if (preg_match($mobile_agents, $_SERVER["HTTP_USER_AGENT"])) {

			return true;
		}
	}

	return false;
}

// превращает код вида 112233 в 112-233
function addConfirmCodeDash(string $confirm_code):string {

	return substr($confirm_code, 0, 3) . "-" . substr($confirm_code, 3, 3);
}

// получаем из массива список по ключу который можно вставить в ?a в sql запросе
function formatIn(array $list, string $key):array {

	return array_map(function(array $value) use ($key) {

		return $value[$key];
	}, $list);
}

// группируем массив по произвольному ключу
function groupByKey(array $array, string $key):array {

	$output = [];
	foreach ($array as $item) {
		$output[$item[$key]] = $item;
	}
	return $output;
}

// склоние в зависимости от количества чего либо ($one: 1 день, $two: 2 дня, $five: 5 дней)
function plural(int $number, string $one, string $two, string $five):string {

	$number = round($number);
	$number %= 100;
	if ($number >= 5 && $number <= 20) {
		return $five;
	}

	$number %= 10;

	if ($number == 1) {
		return $one;
	}
	if ($number >= 2 && $number <= 4) {
		return $two;
	}

	return $five;
}

# endregion
####################################################

####################################################
# region КОНСОЛЬ
####################################################

// красный
function redText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 31, $is_underline);
}

// зеленый
function greenText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 32, $is_underline);
}

// розовый
function purpleText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 35, $is_underline);
}

// желтый
function yellowText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 33, $is_underline);
}

// голубой
function blueText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 96, $is_underline);
}

// делаем текст произвольного цвета для консоли
function makeCliColorText(string $text, int $color_code = 0, bool $is_underline = false):string {

	$str = "\033[{$color_code}m{$text}\033[0m";

	if ($is_underline) {
		$str = "\033[{$color_code}m\e[4m{$text}\033[0m";
	}

	return $str;
}

# endregion
####################################################
