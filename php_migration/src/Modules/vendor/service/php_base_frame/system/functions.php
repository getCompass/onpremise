<?php

use BaseFrame\Path\PathProvider;
use BaseFrame\Server\ServerProvider;
use BaseFrame\Conf\ConfProvider;
use \BaseFrame\Conf\ConfBaseFrameProvider;

// ------------------------------------------------
// содержатся вспомогательные функции системы
// ------------------------------------------------

####################################################
# region        Работа с API
####################################################
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\File;

/**
 * получаем get параметр из запроса
 *
 * @param string $field
 * @param null   $default
 *
 * @return mixed|null
 * @mixed
 */
function get(string $field, $default = null) {

	return $_GET[$field] ?? $default;
}

/**
 * получаем post параметр из запроса
 *
 * @param string $field
 * @param null   $default
 *
 * @return mixed
 * @mixed
 */
function post(string $field, $default = null) {

	return $_POST[$field] ?? $default;
}

/**
 * получаем request параметр из запроса
 *
 * @param string $field
 * @param null   $default
 *
 * @return mixed|null
 * @mixed
 */
function request(string $field, $default = null) {

	return $_REQUEST[$field] ?? $default;
}

/**
 * получаем cookie параметр из запроса
 *
 * @param string $field
 * @param null   $default
 *
 * @return mixed|null
 * @mixed
 */
function cookie(string $field, $default = null) {

	return $_COOKIE[$field] ?? $default;
}

/**
 * возвращает число ограниченное min & max
 *
 * @param int      $value
 * @param int|null $min
 * @param int|null $max
 *
 * @return int
 */
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

/**
 * возвращает device id
 * на iOS меняется при переустановке приложения
 */
function getDeviceId():string {

	if (!isset($_SERVER["HTTP_X_BM_DEVICE_ID"])) {
		return "";
	}

	// обрабатываем device_id, обрезаем до максимального размера
	$device_id = formatString($_SERVER["HTTP_X_BM_DEVICE_ID"]);
	$device_id = trim($device_id);

	if (!checkUuid($device_id) && !checkGuid($device_id)) {
		return "";
	}

	return $device_id;
}

/**
 * возвращает локальное время пользователя
 */
function getLocalClientTime():array {

	if (!isset($_SERVER["HTTP_X_LOCAL_CLIENT_TIME"])) {
		return ["", "", ""];
	}

	// обрабатываем локальное время
	$local_time = formatString($_SERVER["HTTP_X_LOCAL_CLIENT_TIME"]);
	$tt         = explode(" ", $local_time);
	if (count($tt) != 3) {
		return ["", "", ""];
	}

	// некоторые клиенты могут прислать 7:15 вместо 07:15 - конвертируем
	$date_time      = DateTime::createFromFormat("H:i:s", $tt[1]);
	$formatted_time = $date_time->format("H:i:s");

	// возвращаем в формате: [дата, время, часовой пояс]
	// например: [05.07.2023, 11:32:45, +0400]
	return [$tt[0], $formatted_time, $tt[2]];
}

/**
 * возварщает client_launch_uuid
 *
 * @return string
 */
function getClientLaunchUUID():string {

	// получаем заголовок
	$client_launch_uuid = $_SERVER["HTTP_CLIENT_LAUNCH_UUID"] ?? "";

	// обрезаем до макс размера
	$client_launch_uuid = mb_substr($client_launch_uuid, 0, 255);

	// форматируем
	return formatString($client_launch_uuid);
}

/**
 * возвращает айпи адрес того, кто выполняет текущий скрипт
 *
 * @return string
 */
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

/**
 * сравнивает IP между собой (можно по принадлежности к подсети)
 *
 * @return bool
 */
function isIpEqual(string $ip1, string $ip2):bool {

	$ip_ar = explode(".", $ip1);

	$allow_ip_ar  = explode(".", $ip2);
	$allow_result = true;
	foreach ($allow_ip_ar as $key => $d) {

		if ($d == "*") {
			continue;
		}

		$d = intval($d);

		if ($d != intval($ip_ar[$key]) || intval($ip_ar[$key]) < 1 || $d < 1) {
			$allow_result = false;
		}
	}

	return $allow_result;
}

/**
 * возвращает user agent пользователя
 *
 * @return string
 */
function getUa():string {

	if (!isset($_SERVER["HTTP_USER_AGENT"]) || $_SERVER["HTTP_USER_AGENT"] == "") {
		$_SERVER["HTTP_USER_AGENT"] = "robot";
	}

	return formatString($_SERVER["HTTP_USER_AGENT"]);
}

/**
 * true - если Ajax запрос
 *
 * @return bool
 */
function isAjax():bool {

	return (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" || (isset($GLOBALS["ajax"]) && $GLOBALS["ajax"] == true));
}

/**
 * true - если крон
 *
 * @return bool
 */
function isCron():bool {

	if (!defined("IS_CRON") || IS_CRON == false) {
		return false;
	}

	return true;
}

/**
 * true - если работа из консоли
 *
 * @return bool
 */
function isCLi():bool {

	if (php_sapi_name() == "cli") {
		return true;
	}

	return false;
}

/**
 * возвращает country_code текущего запроса
 *
 * @return string
 */
function getRequestCountryCode():string {

	$country_code = geoip_country_code_by_name(getIp());

	if (!$country_code) {
		return "ru";
	}

	return strtolower($country_code);
}

/**
 * true - если ip офисный
 *
 * @return bool
 */
function isOfficeIp():bool {

	$ip_address = getIp();

	// получаем массив с IP адресами компании
	$allow_ip = ConfProvider::allowIp();

	// проверяем наличие IP адреса среди указанных в конфигурации
	if (!in_array($ip_address, $allow_ip)) {
		return false;
	}

	return true;
}

/**
 * true - если это тестовый запуск (dry-run в параметрах скрипта)
 *
 * @return bool
 */
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
 * получаем значение заголовка
 *
 * @param string $key
 *
 * @return string
 */
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

/**
 * форматирует аргументы в строку
 *
 * @return string
 */
function formatArgs():string {

	$out  = [];
	$vars = func_get_args();
	foreach ($vars as $v) {
		$out[] = print_r($v, true);
	}

	return implode(" | ", $out);
}

/**
 * заменяет в строке выражения обернутые в {} на элементы массива по ключу
 * пример: format("после замены это {value} значение будет равно одному",["value"=>1]);
 *
 * @param string $txt
 * @param array  $replace
 *
 * @return string
 */
function format(string $txt, array $replace):string {

	foreach ($replace as $key => $value) {

		//echo dd($key,$value);
		$txt = @str_replace("{" . $key . "}", $value, $txt);
		$txt = @str_replace("{" . strtolower($key) . "}", $value, $txt);
		$txt = @str_replace("{" . strtoupper($key) . "}", $value, $txt);
	}

	return $txt;
}

/**
 * выводит текст в консоль
 *
 * @return void
 */
function console():void {

	if (!isCli()) {
		return;
	}

	$vars = func_get_args();
	foreach ($vars as $v) {

		$v = formatArgs($v);

		echo "{$v}\n";
	}
}

# endregion
####################################################

####################################################
# region        РАБОТА СО ВРЕМЕНЕМ
####################################################

/**
 * номер года
 *
 * @param int|null $time
 *
 * @return int
 */
function yearNum(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return limit(intval(date("o", $time)));
}

/**
 * количество недель прошедших с начала года
 *
 * @param int|null $time
 *
 * @return int
 */
function weekNum(int $time = null):int {

	if ($time == null) {
		$time = time();
	}

	return limit(intval(date("W", $time)));
}

/**
 * количество дней прошедших с начала года
 *
 * @param int|null $time
 *
 * @return int
 */
function dayNum(int $time = null):int {

	if ($time == null) {
		$time = time();
	}

	return limit(intval(date("z", $time)));
}

/**
 * время начала текущего дня
 *
 * @param int|null $time
 *
 * @return int
 */
function dayStart(int $time = null):int {

	if ($time == null) {
		$time = time();
	}

	return mktime(0, 0, 0, date("m", $time), date("d", $time), date("Y", $time));
}

/**
 * время начала текущего дня (по Гринвичу)
 *
 * @param int|null $time
 *
 * @return int
 */
function dayStartOnGreenwich(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return gmmktime(0, 0, 0, gmdate("m", $time), gmdate("d", $time), gmdate("Y", $time));
}

/**
 * время начала текущей недели
 *
 * @param int|null $time
 *
 * @return int
 */
function weekStart(int $time = null):int {

	if ($time == null) {
		$time = time();
	}
	$start = (date("w", $time) == 1) ? $time : strtotime("last monday", $time);

	return dayStart($start);
}

/**
 * время начала текущей недели по UTC
 *
 * @param int|null $time
 *
 * @return int
 */
function weekStartOnGreenwich():int {

	$datetime = (new \DateTime())->setTimezone(new \DateTimeZone("UTC"));
	return $datetime->modify("Monday this week")->getTimestamp();
}

/**
 * время начала текущего месяца
 *
 * @param int|null $time
 *
 * @return int
 */
function monthStart(int $time = null):int {

	if ($time == null) {
		$time = time();
	}

	$start = strtotime(date("Y-m", $time));
	return dayStart($start);
}

/**
 * время начала месяца (по гринвичу)
 *
 * @param int|null $time
 *
 * @return int
 */
function monthStartOnGreenwich(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	$start = gmdate("Y-m", $time);
	$start = strtotime("{$start} gmt");
	return dayStartOnGreenwich($start);
}

/**
 * время конца месяца (по гринвичу)
 *
 * @param int|null $time
 *
 * @return int
 */
function monthEndOnGreenwich(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	$start = gmdate("Y-m-t", $time);
	$start = strtotime("{$start} gmt");
	return dayEndOnGreenwich($start);
}

/**
 * время начала текущей минуты
 *
 * @param int|null $time
 *
 * @return int
 */
function minuteStart(int $time = null):int {

	if ($time == null) {
		$time = time();
	}

	return mktime(date("H", $time), date("i", $time), 0, date("m", $time), date("d", $time), date("Y", $time));
}

/**
 * время начала текущего часа
 *
 * @param int|null $time
 *
 * @return int
 */
function hourStart(int $time = null):int {

	if ($time == null) {
		$time = time();
	}

	return mktime(date("H", $time), 0, 0, date("m", $time), date("d", $time), date("Y", $time));
}

/**
 * сколько времени прошло от начала дня
 *
 * @return int
 */
function fromDayStart():int {

	return time() - mktime(0, 0, 0, date("m"), date("d"));
}

/**
 * через сколько кончится день
 *
 * @return int
 */
function tillDayEnd():int {

	return mktime(0, 0, 0, date("m"), date("d") + 1) - time();
}

/**
 * через сколько кончится завтрашний день
 *
 * @return int
 */
function tillTomorrowEnd():int {

	return mktime(0, 0, 0, date("m"), date("d") + 1) - time() + HOUR24;
}

/**
 * Получить текущее время в милисекундах
 *
 * @return int
 */
function timeMs():int {

	return (int) (microtime(true) * 1000);
}

/**
 * определяем период начала и конца месяца
 *
 * @param int $month_start_at
 *
 * @return array
 */
function getPeriodByMonthStartAt(int $month_start_at):array {

	// добавляем время, чтобы уйти от границы начала месяца и не спутать даты
	$month_time_at = $month_start_at + DAY1;

	// определяем период для месяца
	$from_date_at = dayStart(monthStart($month_time_at));
	$to_date_at   = dayEnd(monthEnd($month_time_at));

	return [$from_date_at, $to_date_at];
}

/**
 * время конца текущего дня
 *
 * @param int|null $time
 *
 * @return int
 */
function dayEnd(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return mktime(23, 59, 59, date("m", $time), date("d", $time), date("Y", $time));
}

/**
 * время конца текущего дня (по Гринвичу)
 *
 * @param int|null $time
 *
 * @return int
 */
function dayEndOnGreenwich(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	return gmmktime(23, 59, 59, gmdate("m", $time), gmdate("d", $time), gmdate("Y", $time));
}

/**
 * время конца месяца
 *
 * @param int|null $time
 *
 * @return int
 */
function monthEnd(int $time = null):int {

	if ($time === null) {
		$time = time();
	}

	$start = date("Y-m-t", $time);
	$start = strtotime("{$start}");
	return dayEnd($start);
}

/**
 * получаем название дня недели (Monsday, Tuesday, Wednesday, ...)
 *
 * @param int|null $time
 *
 * @return string
 */
function getWeekDayString(int $time = null):string {

	if ($time === null) {
		$time = time();
	}

	return date("l", $time);
}

/**
 * получаем начало месяца по году и номеру месяца
 *
 * @param int $year
 * @param int $month
 *
 * @return int
 */
function monthBeginAtByYearAndMonth(int $year, int $month):int {

	$date = new DateTime();

	// устанавливаем нужную дату
	$date->setDate($year, $month, 1);

	// получаем первый день месяца
	$date->modify("first day of this month 00:00");

	// отдаем timestamp
	return $date->format("U");
}

/**
 * получаем конец месяца по году и номеру месяца
 *
 * @param int $year
 * @param int $month
 *
 * @return int
 */
function monthEndAtByYearAndMonth(int $year, int $month):int {

	$date = new DateTime();

	// устанавливаем нужную дату
	$date->setDate($year, $month, 1);

	// получаем последний день месяца
	$date->modify("last day of this month 23:59");

	// отдаем timestamp
	return $date->format("U");
}

/**
 * получаем номер первого дня выбранной недели и года
 *
 * @param int $year
 * @param int $week
 *
 * @return int
 */
function getWeekFirstDayNum(int $year, int $week):int {

	$date = new DateTime();
	$date->setTime(12, 0);

	// если первая неделя в году
	if ($week == 1) {

		$week_start_day = $date->modify("January {$year}")->format("z");
		return intval($week_start_day) + 1;
	}

	// устанавливаем неделю и год
	$date->setISODate($year, $week);

	$week_start_day = $date->format("z");
	return intval($week_start_day) + 1;
}

/**
 * получаем номер последнего дня выбранной недели и года
 *
 * @param int $year
 * @param int $week
 *
 * @return int
 */
function getWeekLastDayNum(int $year, int $week):int {

	$date = new DateTime();

	// если последняя неделя в году
	if ($week == 53) {

		$week_start_day = $date->modify("last day of December {$year}")->format("z");
		return intval($week_start_day) + 1;
	}

	// устанавливаем неделю и год
	$date->setISODate($year, $week);

	// устанавливаем метку на последний день недели
	$date->modify("this Sunday");

	// получаем количество прошедших дней
	$week_start_day = intval($date->format("z"));

	return $week_start_day + 1;
}

// время в микросекундах
function timeUs():int {

	return (int) (microtime(true) * 1000000);
}

// время в наносекундах
function timeNs():int {

	return (int) (microtime(true) * 1_000_000_000);
}

// микросекунды в секунды
function timeUsToSeconds(int $value):int {

	return intval($value / 1000000);
}

/**
 * Проверим, что валидная метка времени
 *
 * @param int $timestamp
 *
 * @return bool
 */
function isValidTimestamp(int $timestamp):bool {

	if ($timestamp > MAX_TIMESTAMP_VALUE || $timestamp < 0) {
		return false;
	}

	return true;
}

# endregion
####################################################

####################################################
# region         КОНФИГИ
####################################################

/**
 * возвращает значение конфига
 *
 * @param string $code
 *
 * @return array
 * @mixed
 */
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

/**
 * возвращает значение конфига
 *
 * @param string $code
 *
 * @return array
 * @mixed
 */
function getSystemConfig(string $code):array {

	global $CONFIG;
	$code = strtoupper($code);
	if (isset($CONFIG[$code])) {
		return $CONFIG[$code];
	}

	$codes = explode("_", $code);
	$file  = strtolower($codes[0]);

	loadSystemConfig($file);
	if (!isset($CONFIG[$code])) {
		return [];
	}

	return $CONFIG[$code];
}

/**
 * перезаписывает конфиг
 *
 * @param string $code
 * @param mixed  $data
 *
 * @mixed
 */
function setConfig(string $code, $data):void {

	global $CONFIG;
	$code          = strtoupper($code);
	$CONFIG[$code] = $data;
}

/**
 * загружаем конфиг из файла конфигов /api/conf
 *
 * @param string $file
 *
 * @return void
 */
function loadConfig(string $file):void {

	global $CONFIG;
	$_    = $CONFIG;
	$file = File::init(PathProvider::api() . "/conf/", $file . ".php");
	if ($file->isExists()) {
		include($file->getFilePath());
	}
}

/**
 * загружаем конфиг из файла конфигов /api/conf
 *
 * @param string $file
 *
 *
 * @return void
 */
function loadSystemConfig(string $file_path):void {

	global $CONFIG;
	$_ = $CONFIG;

	// проверяем путь для старой структуры проектов
	$file = File::init(PathProvider::root() . "api/modules/vendor/service/php_base_frame/conf/", $file_path . ".php");

	if ($file->isExists()) {

		include($file->getFilePath());
		return;
	}

	// проверяем путь для модульной структуры проектов
	$file = File::init(PathProvider::root() . "src/Modules/vendor/service/php_base_frame/conf/", $file_path . ".php");

	if ($file->isExists()) {
		include($file->getFilePath());
	}
}

/**
 * возвращает значение структуры
 *
 * @param string $code
 *
 * @return array|mixed
 * @mixed
 */
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

/**
 * загружаем структуру из файла структур /api/structure
 *
 * @param string $file
 *
 * @return void
 */
function loadStructure(string $file):void {

	$path = PathProvider::api() . "/structure/" . $file . ".php";
	if (file_exists($path)) {
		include($path);
	}
}

/**
 * генерирует поле schemas для одного месяца в sharding.php
 *
 * @param string $db_postfix
 * @param array  $table_list
 * @param array  $extra_merge_list
 *
 * @return array
 */
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

/**
 * генерирует поле schemas для таблиц от 0 до hex (например ff)
 *
 * @param string $max_hex
 * @param array  $table_list
 * @param array  $extra_merge_list
 *
 * @return array
 */
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

/**
 * вернуть email со звездочками
 *
 * @param string $email
 *
 * @return string
 */
function obfusticateEmail(string $email):string {

	$parts = explode("@", $email);

	$output = substr($parts[0], 0, 1) . "*****" . substr($parts[0], -1);
	$output .= "@";
	$output .= substr($parts[1], 0, 1) . "***" . substr($parts[1], -3);

	return $output;
}

/**
 * вернуть full_name со звездочками
 *
 * @param string $full_name
 *
 * @return string
 */
function obfuscateFullName(string $full_name):string {

	$parts = explode(" ", $full_name);

	$obfuscated_parts = [];
	foreach ($parts as $part) {

		// если строка 2 и менее символа, то так и добавляем ее
		if (mb_strlen($part) <= 2) {

			$obfuscated_parts[] = $part;
			continue;
		}

		$obfuscated_parts[] = mb_substr($part, 0, 1) . str_repeat("*", mb_strlen($part) - 2) . mb_substr($part, -1);
	}

	return implode(" ", $obfuscated_parts);
}

/**
 * Универсальняа функция для скрытия слов в строке
 */
function obfuscateWords(string $string, int $visible_count = 1):string {

	$string_length = mb_strlen($string);

	$delimiter = " ";
	$words = explode($delimiter, $string);

	$output = (string) array_reduce($words, static function(string $output, string $word) use ($visible_count, $delimiter) {

		$length = mb_strlen($word);
		$hidden_count = $length - ($visible_count * 2);

		return $output . mb_substr($word, 0, $visible_count)
			. ($hidden_count < 0 ? "" : str_repeat('*', $hidden_count))
			. mb_substr($word, ($visible_count * -1), $visible_count) . $delimiter;
	}, "");

	return mb_substr($output, 0, $string_length);
}

/**
 * вернуть массив отсортированный в едином формате для создания подписи
 *
 * @param array $array
 * @param int   $sort_flags
 *
 * @return bool
 */
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

/**
 * преобразуем объект в массив
 *
 * @param mixed $item
 *
 * @return array
 * @mixed
 */
function _convertObjectToArray($item) {

	if (is_object($item)) {
		$item = (array) $item;
	}

	return $item;
}

/**
 * формируем set
 *
 * @param array $a
 * @param array $b
 *
 * @return array
 */
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

/**
 * alias системной функции, так как здесь возможны модификации
 * и важно чтобы все кодирование JSON шло через одну функцию
 *
 * @param mixed $input
 *
 * @return string
 * @mixed
 */
function toJson($input):string {

	return json_encode($input);
}

/**
 * раскодировать json
 *
 * @param mixed $input
 *
 * @return array
 */
function fromJson(string $input):array {

	$info = json_decode($input, true, 512, JSON_BIGINT_AS_STRING);
	if (!is_array($info)) {
		return [];
	}

	return $info;
}

/**
 * проверяем передан ли нам json
 *
 * @param string $string
 *
 * @return bool
 */
function isJson(string $string):bool {

	json_decode($string);
	return json_last_error() == JSON_ERROR_NONE;
}

# endregion
####################################################

####################################################
# region         ПРОЧЕЕ
####################################################

/**
 * разбиваем текст на массив состоящий из слов, пробелов между ними, знаков пунктуации
 *
 * @return string[]
 */
function splitTextIntoWords(string $text):array {

	return preg_split("/(\b(?!\s)|(?<!\s)\b)/u", $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
}

/**
 * Убираем unicode/shortname эмодзи из текста
 *
 * @return string
 */
function removeEmojiFromText(string $text):string {

	// если текст пуст, то ничего не делаем
	if (mb_strlen($text) < 1) {
		return $text;
	}

	// получаем список смайлов из конфига
	$emoji_list      = \BaseFrame\Conf\Emoji::EMOJI_LIST;
	$emoji_flag_list = \BaseFrame\Conf\Emoji::EMOJI_FLAG_LIST;

	// список всех эмодзи (флаги + остальные)
	$merged_emoji_list = array_merge($emoji_list, $emoji_flag_list);

	// получаем юникоды всех эмодзи
	$unicode_emoji_list = array_keys($merged_emoji_list);

	// получаем все шортнеймы эмодзи
	$shortname_emoji_list = array_values($merged_emoji_list);

	// убираем из текста сообщения unicode эмодзи
	$text = str_replace($unicode_emoji_list, "", $text);

	// убираем из текста сообщения shortname эмодзи
	return str_replace($shortname_emoji_list, "", $text);
}

/**
 * Оставляем от input строки только буквы, фильтруя все остальное
 *
 * @return string
 */
function filterLetter(string $input):string {

	return preg_replace("/\W+/u", "", $input);
}

/**
 * Проверка корректности uuid
 *
 * @param string $uuid
 *
 * @return bool
 */
function isUuidValid(string $uuid):bool {

	return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid) === 1;
}

/**
 * устанавливает константу, если такая еще не установлена
 *
 * @param string $key
 * @param mixed  $value
 *
 * @return void
 * @mixed
 */
function ddefine(string $key, $value):void {

	if (!defined($key)) {
		define($key, $value);
	}
}

/**
 * выводит в консоль форматированный массив, чтобы его можно скопировать и вставить в РНР скрипт
 *
 * @param array $list
 *
 * @return string
 */
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

/**
 * проверяет, есть ли искомое значение в переданной строке
 *
 * @param string $html
 * @param string $str
 *
 * @return bool
 */
function inHtml(string $html, string $str):bool {

	return (bool) substr_count($html, $str);
}

/**
 * собрать случайную строку
 *
 * @param int $length
 *
 * @return string
 */
function generateRandomString(int $length = 10, bool $with_special_characters = false):string {

	$characters         = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$special_characters = "!@#$%^&*()";

	if ($with_special_characters) {
		$characters .= $special_characters;
	}

	$charactersLength = strlen($characters);
	$randomString     = "";
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[random_int(0, $charactersLength - 1)];
	}

	return $randomString;
}

/**
 * получить случайный User Agent
 *
 * @return string
 * @long много User Agent-ов
 */
function generateRandomUserAgent():string {

	// user agent к браузерам
	$user_agents = [
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 6.3; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 10.0; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:101.00) Gecko/20100101 Firefox/101.00",
		"Mozilla/5.0 (Windows NT 6.1; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 6.3; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 10.0; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0",
		"Mozilla/5.0 (Windows NT 6.1; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 6.3; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 10.0; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0",
		"Mozilla/5.0 (Windows NT 6.1; rv:98.0) Gecko/20100101 Firefox/98.0",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:98.0) Gecko/20100101 Firefox/98.0",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.45",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.67 Safari/537.36 OPR/87.0.4390.36",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/101.0.4951.64 Safari/537.36 OPR/87.0.4390.25",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.59",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",
		"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",
		"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",
		"Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",
		"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",
		"Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",
		"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.43 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 OPR/86.0.4363.50",

	];

	// забираем случайный элемент массива с user agent-ами
	return $user_agents[array_rand($user_agents, 1)];
}

/**
 * получить случайный uuid
 *
 * @return string
 */
function generateUUID():string {

	$data    = openssl_random_pseudo_bytes(16);
	$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
	$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10

	// если какой-нибудь молодой человек решит изменить функцию
	// трогай только верхний код
	// нижний защитит от ошибки сгенерировать uuid не RFC формату
	//	$regexp = "#^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$#";
	//	if (!preg_match($regexp, $uuid)) {
	//		throw new parseException("Generated bad uuid: {$uuid}");
	//	}

	return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split(bin2hex($data), 4));
}

/**
 * отдать ajax в браузер
 *
 * @param mixed $output
 *
 * @return void
 * @mixed
 */
function showAjax($output):void {

	if (!headers_sent()) {

		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-type: application/json;charset=" . ConfBaseFrameProvider::webCharset());
	}

	$txt = is_array($output) ? toJson($output) : htmlentities($output, ENT_NOQUOTES);
	echo $txt;
}

/**
 * возвращает случайную 40 символьную строку
 *
 * @param null $salt
 *
 * @return string
 * @throws Exception
 * @mixed
 */
function getHash($salt = null):string {

	if ($salt == null) {
		$salt = random_int(256, 512);
	}

	return sha1(openssl_random_pseudo_bytes($salt));
}

/**
 * возвращает уникальный id
 *
 * @param int $length
 *
 * @return string
 * @mixed
 */
function getUniqId(int $length = 64) {

	return openssl_random_pseudo_bytes($length);
}

/**
 * оставляем только цифры
 *
 * @param mixed $value
 *
 * @return int
 * @mixed
 */
function formatInt($value):int {

	$value = trim($value);
	$value = str_replace(",", ".", $value);
	$value = preg_replace("#[^0-9.-]*#ism", "", $value);

	return (int) $value;
}

/**
 * из float в int
 *
 * @param float $float
 *
 * @return int
 */
function floatToInt(float $float):int {

	return round($float * 1000);
}

/**
 * из int в float
 *
 * @param int $int
 *
 * @return float
 */
function intToFloat(int $int):float {

	return $int / 1000;
}

/**
 * удаляем все не UTF последовательности (вдоичный левак, битые символы и тп)
 * https://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
 *
 * @param mixed $value
 * @param bool  $is_trim
 *
 * @return string
 * @mixed
 */
function formatString($value, $is_trim = true):string {

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

	// если нужен trim
	if ($is_trim == true) {
		return trim(preg_replace($regex, "$1", $value));
	}

	return preg_replace($regex, "$1", $value);
}

/**
 * оставляем только телефон
 *
 * @param mixed $value
 *
 * @return string
 * @mixed
 */
function formatPhone($value):string {

	$value = trim(formatInt($value));

	if (substr($value, 0, 1) == "8") {
		$value = "7" . substr($value, 1);
	}

	return $value;
}

/**
 * форматируем только на целочисленный тип
 *
 * @param mixed $value
 *
 * @return float
 * @mixed
 */
function formatFloat($value):float {

	$value = trim($value);
	$value = str_replace(",", ".", $value);
	$value = preg_replace("#[^0-9.-]*#ism", "", $value);

	return (float) $value;
}

/**
 * форматируем email
 *
 * @param mixed $value
 *
 * @return string
 * @mixed
 */
function formatEmail($value):string {

	$value = strtolower($value);
	return filter_var($value, FILTER_SANITIZE_EMAIL);
}

/**
 * форматируем hash
 *
 * @param mixed $value
 *
 * @return string
 * @mixed
 */
function formatHash($value):string {

	$value = preg_replace("#[^a-zA-Z0-9_\-=]*#ism", "", $value);
	return $value;
}

/**
 * debug
 *
 * @param mixed ...$arr
 *
 * @return void
 */
function debug(...$arr):void {

	$text = date(DATE_FORMAT_FULL_S, time()) . "\n";

	foreach ($arr as $value) {

		$text .= formatArgs($value);
		$text .= "\n";
	}

	@writeToFile(PathProvider::logs() . "debug.log", $text);
}

/**
 * Запись в файл
 *
 * @param string $filename
 * @param string $data
 */
function writeToFile(string $filename, string $data):void {

	if (!file_exists($filename)) {

		$user = "www-data";

		$file = fopen($filename, "a");
		chgrp($filename, $user);
		chown($filename, $user);
		chmod($filename, octdec(644));
	} else {

		$file = fopen($filename, "a");
	}

	fwrite($file, $data);
	fclose($file);
}

/**
 * получаем доменное имя
 *
 * @return string
 */
function getDomain():string {

	// получаем server_name
	$server_name = $_SERVER["SERVER_NAME"];

	// разбиваем по точке - например conversation.dev1.compass.ru
	$tmp = explode(".", $server_name);

	// получаем последние два элемента и делаем строку
	return implode(".", array_slice($tmp, count($tmp) - 2));
}

/**
 * проверяем что запрос с мобильного устройства
 *
 * @return bool
 */
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

/**
 * Генерирует код подтверждения.
 */
function generateConfirmCode():string {

	/** @noinspection PhpUnhandledExceptionInspection */
	$code = random_int(100000, 999999);

	// если тестовый сервер
	if (isTestServer() || isStageServer()) {
		$code = 999999;
	}
	return sprintf("%06d", $code);
}

/**
 * превращает код вида 112233 в 112-233
 *
 * @param string $confirm_code
 *
 * @return string
 */
function addConfirmCodeDash(string $confirm_code):string {

	return substr($confirm_code, 0, 3) . "-" . substr($confirm_code, 3, 3);
}

/**
 * получаем из массива список по ключу который можно вставить в ?a в sql запросе
 *
 * @param array  $list
 * @param string $key
 *
 * @return array
 */
function formatIn(array $list, string $key):array {

	return array_map(function(array $value) use ($key) {

		return $value[$key];
	}, $list);
}

/**
 * группируем массив по произвольному ключу
 *
 * @param array  $array
 * @param string $key
 *
 * @return array
 */
function groupByKey(array $array, string $key):array {

	$output = [];
	foreach ($array as $item) {
		$output[$item[$key]] = $item;
	}
	return $output;
}

/**
 * склоние в зависимости от количества чего либо ($one: 1 день, $two: 2 дня, $five: 5 дней)
 *
 * @param int    $number
 * @param string $one
 * @param string $two
 * @param string $five
 *
 * @return string
 */
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

/**
 * Выпрямляет массив — многомерный любой вложенности превращает в одномерный.
 * Не сохраняет ключи.
 *
 * @param array $arr
 *
 * @return array
 */
function arrayFlat(array $arr):array {

	$output = [];

	// функция, которая делает сплющивание
	// сделано так, чтобы не возникало желания передать в array_flat второй агрумент
	$fn = function(array $arr, array &$output) use (&$fn):void {

		foreach ($arr as $v) {

			if (is_array($v)) {
				$fn($v, $output);
			} else {
				$output[] = $v;
			}
		}
	};

	// плющим массив
	$fn($arr, $output);

	return $output;
}

// приводим значения массива к int
function arrayValuesInt(array $array):array {

	$output = [];
	foreach ($array as $v) {
		$output[] = (int) $v;
	}

	return array_values($output);
}

/**
 * Возвращает массив, где каждому элементу сопоставлен ключ,
 * равный значению поля $column этого элемента.
 *
 * Если есть элементы с одинаковым значением $column,
 * то они будут перезаписаны в пордке элементов исходного массива.
 *
 * Если у элемента нет поля $column, элемент будет пропущен.
 */
function arrayRemap(array $arr_list, string $column):array {

	$output = [];

	foreach ($arr_list as $arr) {

		if (is_array($arr)) {

			if (!isset($arr[$column])) {
				continue;
			}

			$output[$arr[$column]] = $arr;
		} elseif (is_object($arr)) {

			if (!property_exists($arr, $column)) {
				continue;
			}

			$output[$arr->$column] = $arr;
		}
	}

	return $output;
}

/**
 * Сравнивает два значения на полное совпадение.
 * Не умеет сравнивать объекты.
 *
 * @param mixed $first
 * @param mixed $second
 *
 * @return bool
 * @mixed
 */
function areValuesEqual($first, $second):bool {

	// если массивы
	if (is_array($first) && is_array($second)) {

		array_multisort($first);
		array_multisort($second);

		return (serialize($first) === serialize($second));
	}

	// скаляры
	return $first === $second;
}

/**
 * конвертируем unix time в ISO8601 формат Y-m-d
 *
 * @param int $unix_time
 *
 * @return string
 */
function convertToISO88601YmD(int $unix_time):string {

	return date("Y-m-d", $unix_time);
}

/**
 * определяем, пуста ли строка
 *
 * @param string $text
 *
 * @return bool
 */
function isEmptyString(string $text):bool {

	return mb_strlen($text) < 1;
}

# endregion
####################################################

####################################################
# region КОНСОЛЬ
####################################################

/**
 * красный
 *
 * @param string $text
 * @param bool   $is_underline
 *
 * @return string
 */
function redText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 31, $is_underline);
}

/**
 * зеленый
 *
 * @param string $text
 * @param bool   $is_underline
 *
 * @return string
 */
function greenText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 32, $is_underline);
}

/**
 * розовый
 *
 * @param string $text
 * @param bool   $is_underline
 *
 * @return string
 */
function purpleText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 35, $is_underline);
}

/**
 * желтый
 *
 * @param string $text
 * @param bool   $is_underline
 *
 * @return string
 */
function yellowText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 33, $is_underline);
}

/**
 * голубой
 *
 * @param string $text
 * @param bool   $is_underline
 *
 * @return string
 */
function blueText(string $text, bool $is_underline = false):string {

	return makeCliColorText($text, 96, $is_underline);
}

/**
 * делаем текст произвольного цвета для консоли
 *
 * @param string $text
 * @param int    $color_code
 * @param bool   $is_underline
 *
 * @return string
 */
function makeCliColorText(string $text, int $color_code = 0, bool $is_underline = false):string {

	$str = "\033[{$color_code}m{$text}\033[0m";

	if ($is_underline) {
		$str = "\033[{$color_code}m\e[4m{$text}\033[0m";
	}

	return $str;
}

// подтверждение для консоли
function confirmCliFlow(string $message = null):void {

	if (!is_null($message)) {
		console($message);
	}

	$txt = "Press " . blueText("enter") . " to proceed...";
	console($txt);
	$answer = readline();

	if ($answer == "") {
		return;
	}

	console(redText("ABORT"));
	exit;
}

// просим ввести определенный текст, возвращаем true/false
function confirmCliFlowBoolean(string $message, string $value = ""):bool {

	$line = readline("{$message}: ");
	return $line == $value;
}

# endregion
####################################################

/**
 * Проверка запущено ли на тестовом сервере
 *
 * @return void
 * @throws parseException
 */
#[\JetBrains\PhpStorm\Deprecated]
function assertTestServer():void {

	ServerProvider::assertTest();
}

/**
 * Проверка запущено ли не на паблике
 *
 * @return void
 * @throws parseException
 */
#[\JetBrains\PhpStorm\Deprecated]
function assertNotPublicServer():void {

	if (ServerProvider::isProduction()) {
		throw new ParseFatalException("called is not production server");
	}
}

/**
 * проверяем на каком окружении находимся
 *
 * @return bool
 */
#[\JetBrains\PhpStorm\Deprecated]
function isTestServer():bool {

	if (ServerProvider::isTest()) {
		return true;
	}

	return false;
}

/**
 * Проверяем что это локалка
 *
 * @return bool
 */
#[\JetBrains\PhpStorm\Deprecated]
function isLocalServer():bool {

	if (ServerProvider::isLocal()) {
		return true;
	}

	return false;
}

/**
 * проверяем, что это стейдж сервер
 *
 * @return bool
 */
#[\JetBrains\PhpStorm\Deprecated]
function isStageServer():bool {

	if (ServerProvider::isStage()) {
		return true;
	}

	return false;
}

/**
 * проверяем на то что это тест
 *
 * @return bool
 */
function isBackendTest():bool {

	if (isset($_SERVER["HTTP_IS_BACKEND_TEST"]) && isTestServer()) {
		return true;
	}

	return false;
}

/**
 * проверяем нужна ли проверка на антиспам
 *
 * @return bool
 */
function isNeedAntispam():bool {

	if (isset($_SERVER["HTTP_IS_NEED_ANTISPAM"]) && $_SERVER["HTTP_IS_NEED_ANTISPAM"] == 1) {
		return true;
	}

	return false;
}

/**
 * получить случайный guid
 *
 * @return string
 */
function generateGUID():string {

	if (function_exists("com_create_guid") === true) {
		return trim(com_create_guid(), "{}");
	}

	return sprintf("%04X%04X-%04X-%04X-%04X-%04X%04X%04X", mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

/**
 * Проверим что соответсвует UUID 1 - 4 версий
 *
 * @param string $value
 *
 * @return bool
 */
function checkUuid(string $value):bool {

	if (preg_match("/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i", $value) == 1) {
		return true;
	}

	return false;
}

/**
 * Проверим что соответсвует GUID
 *
 * @param string $value
 *
 * @return bool
 */
function checkGuid(string $value):bool {

	if (preg_match("/^\{?[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}\}?$/i", $value) == 1) {
		return true;
	}

	return false;
}

/**
 * Получим регулярку UUID необходомого типа
 *
 * @param int $version_uuid
 *
 * @return string
 * @throws cs_InvalidUuidVersionException
 */
function matchUuid(int $version_uuid):string {

	return match ($version_uuid) {
		1 => "/^[0-9A-F]{8}-[0-9A-F]{4}-[1][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/",
		2 => "/^[0-9A-F]{8}-[0-9A-F]{4}-[2][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/",
		3 => "/^[0-9A-F]{8}-[0-9A-F]{4}-[3][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/",
		4 => "/^[0-9a-f]{8}-[0-9a-f]{4}-[4][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/",
		5 => "/^[0-9A-F]{8}-[0-9A-F]{4}-[5][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i",
		default => throw new cs_InvalidUuidVersionException(),
	};
}

/**
 * Возвращает трассировку ошибки.
 *
 * @param      $e
 * @param null $seen
 *
 * @return string
 * @long
 */
function traceError(Throwable $e, mixed $seen = null):string {

	$starter = $seen ? "Caused by: " : "";
	$result  = [];

	if (!$seen) {
		$seen = [];
	}

	$trace = $e->getTrace();
	$prev  = $e->getPrevious();

	$result[] = sprintf("%s%s: %s", $starter, get_class($e), $e->getMessage());

	$file = $e->getFile();
	$line = $e->getLine();

	while (true) {

		$current = "$file:$line";

		if (is_array($seen) && in_array($current, $seen)) {

			$result[] = sprintf(" ... %d more", count($trace) + 1);
			break;
		}

		$result[] = sprintf(" at %s%s%s(%s%s%s)",
			count($trace) && array_key_exists("class", $trace[0]) ? str_replace("\\", ".", $trace[0]["class"]) : "",
			count($trace) && array_key_exists("class", $trace[0]) && array_key_exists("function", $trace[0]) ? "." : "",
			count($trace) && array_key_exists("function", $trace[0]) ? str_replace("\\", ".", $trace[0]["function"]) : "(main)",
			$line === null ? $file : basename($file),
			$line === null ? "" : ":",
			$line === null ? "" : $line
		);

		if (is_array($seen)) {
			$seen[] = "$file:$line";
		}

		if (!count($trace)) {
			break;
		}

		$file = array_key_exists("file", $trace[0]) ? $trace[0]["file"] : "Unknown Source";
		$line = array_key_exists("file", $trace[0]) && array_key_exists("line", $trace[0]) && $trace[0]["line"] ? $trace[0]["line"] : null;

		array_shift($trace);
	}

	$result = join("\n", $result);

	if ($prev) {
		$result .= "\n" . traceError($prev, $seen);
	}

	return $result;
}

/**
 * Сделать первую букву заглавной
 *
 * @param string $value
 *
 * @return string
 */
function mb_ucfirst(string $value) {

	return mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1);
}
