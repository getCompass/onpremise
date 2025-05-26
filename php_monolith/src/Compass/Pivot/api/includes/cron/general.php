<?php

namespace Compass\Pivot;

// ---------------------------------------------
// РАБОЧИЕ КЛАССЫ
// ---------------------------------------------
use BaseFrame\Server\ServerProvider;

/**
 * команды каждую минуту
 */
class Cron_General_MinuteHandler {

	/**
	 * run in worker
	 * @long
	 */
	public static function work():void {

		console("-- [minuteHandler] start --");

		self::observeSmsConfig();

		// обсервим тарифные планы пространств
		$sharding_key_list = Gateway_Db_PivotCompany_Main::getExistingShardList();

		foreach ($sharding_key_list as $sharding_key) {
			Domain_Space_Entity_Tariff_PlanObserver::observe($sharding_key);
		}

		// если не production окружение, то завершаем
		if (!ServerProvider::isProduction() || ServerProvider::isOnPremise()) {
			return;
		}

		// проверяем количество оставшихся вакантных компаний
		Domain_Domino_Action_CheckVacantCount::do();

		$extra = Type_System_Datastore::get("general_analytics");
		if (!isset($extra["need_work"])) {

			$input = "today 23:55:00";
			$date  = strtotime($input);
			$extra = [
				"need_work" => $date,
			];
			Type_System_Datastore::set("general_analytics", $extra);
			return;
		}

		// время пришло
		if (time() > ($extra["need_work"] - 4 * 60)) {

			$input              = "today+1day 23:55:00";
			$date               = strtotime($input);
			$extra["need_work"] = $date;
			Type_System_Datastore::set("general_analytics", $extra);

			$day_start = dayStart();
			Cron_General_Utils::collectStatLegacy($day_start);
			Cron_General_Utils::sendStat($day_start);
		}
	}

	/**
	 * Обозревает api/conf/sms.php на наличие новых провайдеров
	 */
	public static function observeSmsConfig():void {

		// пробегаемся по каждому провайдеру
		$config = Type_Sms_Config::get();

		// проверим, что все провайдеры имеются в базе
		$config_provider_id_list = array_keys($config);
		$db_provider_list        = Gateway_Db_PivotSmsService_ProviderList::getListById($config_provider_id_list);

		// получаем всех провайдеров и проверяем, что они существуют в базе
		foreach ($config as $provider_id => $provider_config_item) {

			// проверяем, что для провайдера прописан gateway
			if (!isset(Type_Sms_Provider::ASSOC_GATEWAY_CLASS_BY_ID[$provider_id])) {

				// отправляем в мониторинг
				Type_System_Admin::log(__CLASS__, __METHOD__ . ": для provider_id [{$provider_id}] не создан gateway-класс");
				continue;
			}

			// проверяем, имеется ли запись в базе
			if (isset($db_provider_list[$provider_id])) {
				continue;
			}

			// иначе создаем
			Type_Sms_Provider::create($provider_id);
		}
	}
}

/**
 *
 */
class Cron_General_Utils {

	protected static string $_company_count_by_day_table_name = "company_count_by_day";
	protected static string $_action_count_by_day_table_name  = "action_count_by_day";

	/**
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @long
	 */
	public static function collectStat():void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$company_count = Gateway_Db_PivotCompany_CompanyList::getOwnedCompanyCount();
		$active_count  = Gateway_Db_PivotCompany_CompanyList::getActiveCount();
		$free_count    = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getVacantCount();

		Gateway_Bus_CollectorAgent::init()->set("row72", $company_count);
		Gateway_Bus_CollectorAgent::init()->set("row73", $active_count);
		Gateway_Bus_CollectorAgent::init()->set("row74", $free_count);
	}

	/**
	 * Собираем стату
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @long
	 */
	public static function collectStatLegacy(int $day_start):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// количество компаний
		$company_count = Gateway_Db_PivotCompany_CompanyList::getOwnedCompanyCount();
		$insert        = [
			"day_at" => $day_start,
			"count"  => $company_count,
		];
		ShardingGateway::database("pivot_system")->insertOrUpdate(self::$_company_count_by_day_table_name, $insert);

		// количество действий
		$action_count = 0;
		$limit_count  = 10000;
		$offset       = 0;
		while (true) {

			$company_list  = Gateway_Db_PivotCompany_CompanyList::getActiveList($limit_count, $offset);
			$company_count = count($company_list);
			$offset        += $company_count;

			foreach ($company_list as $company_row) {

				if ($company_row->status != Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
					continue;
				}

				// отправляем сокет запрос в компанию на удаление
				$private_key = Domain_Company_Entity_Company::getPrivateKey($company_row->extra);
				try {
					$action_count += Gateway_Socket_Company::getActionCount($company_row->company_id, $company_row->domino_id, $private_key);
				} catch (Gateway_Socket_Exception_CompanyIsNotServed|\cs_SocketRequestIsFailed|cs_CompanyIsHibernate) {
					continue;
				}
			}

			if ($company_count < $limit_count) {
				break;
			}
		}

		$insert = [
			"day_at" => $day_start,
			"count"  => $action_count,
		];
		ShardingGateway::database("pivot_system")->insertOrUpdate(self::$_action_count_by_day_table_name, $insert);
	}

	/**
	 * отправляем статистику
	 *
	 * @long собираем данные для графиков
	 */
	public static function sendStat(int $day_start):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$company_id                   = 2;
		$created_company_count_chart  = self::_getStatByDay(self::$_company_count_by_day_table_name, $day_start);
		$active_company_count_chart   = self::_getActiveSpaceChartData($day_start - DAY14, $day_start);
		$user_count_chart             = self::_getOnlineUsersStatByDay($day_start);
		$action_count_chart           = self::_getStatByDay(self::$_action_count_by_day_table_name, $day_start);
		$total_conference_count_chart = Domain_Analytic_Entity_General::getTotalConferenceMetricsChartData($day_start - DAY14, $day_start);

		$free_count = Gateway_Db_PivotCompanyService_CompanyInitRegistry::getVacantCount();
		$params     = [
			"created_company_count_chart" => toJson($created_company_count_chart),
			"active_company_count_chart"  => toJson($active_company_count_chart),
			"user_count_chart"            => toJson($user_count_chart),
			"action_count_chart"          => toJson($action_count_chart),
			"created_company_count"       => $created_company_count_chart[0][1],
			"active_company_count"        => $active_company_count_chart[0][1],
			"total_company_count"         => $created_company_count_chart[0][1] + $free_count,
			"user_count"                  => $user_count_chart[0][1],
			"action_count"                => $action_count_chart[0][1],
			"conference_count"            => $total_conference_count_chart[0][1],
			"conference_count_chart"      => toJson($total_conference_count_chart),
		];

		// генерим картинку
		$curl = new \Curl();
		$curl->setTimeout(60);
		$curl->post(sprintf("https://%s/index.php", STAT_GRAPH_IMAGE_GENERATOR_DOMAIN), $params);

		// получаем url картинки
		$image_url = sprintf("https://%s/files/%d.jpeg", STAT_GRAPH_IMAGE_GENERATOR_DOMAIN, dayStart());

		// получаем запись с компанией
		$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);

		// загружаем файл
		$url            = Gateway_Socket_PivotFileBalancer::getNodeForUpload(3);
		$entrypoint_url = self::_getDominoEntryPoint($company->domino_id);
		$file_key       = Gateway_Socket_FileNode::uploadFile($url, $image_url, "stat_for_" . time() . ".jpeg", $company_id, $entrypoint_url);

		// отправляем сообщение с файлом
		Gateway_Socket_Company::sendMessageWithFile(AUTH_BOT_USER_ID, $file_key, $company_id, $company->domino_id, Domain_Company_Entity_Company::getPrivateKey($company->extra));
	}

	// получаем статистику за день
	protected static function _getStatByDay(string $table_name, int $day_start):array {

		$query = "SELECT * FROM `?p` WHERE day_at <= ?i ORDER by day_at DESC LIMIT ?i";
		$list  = ShardingGateway::database("pivot_system")->getAll($query, $table_name, $day_start, 14);

		$min_day = 0;
		$output  = [];
		foreach ($list as $row) {

			$min_day  = $row["day_at"];
			$output[] = [
				$row["day_at"], $row["count"],
			];
		}
		$need_add = 7 - count($output);
		if ($need_add == 0) {
			return $output;
		}
		for ($i = 0; $i <= $need_add; $i++) {

			$min_day  = $min_day - DAY1;
			$output[] = [
				$min_day, 0,
			];
		}
		return $output;
	}

	/**
	 * Получаем данные по активным командам для заполнения чарта
	 *
	 * @return array
	 */
	protected static function _getActiveSpaceChartData(int $date_from, int $date_to):array {

		// получаем статистику
		$row_data = Gateway_Socket_CollectorServer::getRowByDate("pivot", "row73", "day", $date_from, $date_to);

		// сортируем временные метки по убыванию
		usort($row_data, function(array $a, array $b) {

			return $b["date"] <=> $a["date"];
		});

		// собираем ответ
		$output = [];
		foreach ($row_data as $graph) {

			$output[] = [
				$graph["date"], $graph["value"],
			];
		}

		return $output;
	}

	//
	protected static function _getOnlineUsersStatByDay(int $day_start):array {

		$query = "select day_at, count(user_id) as count from `?p` WHERE day_at <= ?i group by day_at order by day_at DESC LIMIT ?i";
		$list  = ShardingGateway::database("pivot_system")->getAll($query, "online_user_by_day_all", $day_start, 14);

		$user_count_chart = [];
		foreach ($list as $row) {

			$user_count_chart[] = [
				$row["day_at"], $row["count"],
			];
		}

		return $user_count_chart;
	}

	/**
	 * получаем url
	 *
	 * @param string $domino
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	protected static function _getDominoEntryPoint(string $domino):string {

		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");

		if (!isset($domino_entrypoint_config[$domino])) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company not served");
		}

		return $domino_entrypoint_config[$domino]["private_entrypoint"];
	}
}

/**
 * команды каждые 5 минут
 */
class Cron_General_Minute5Handler {

	/**
	 * run 5 min worker
	 */
	public static function work():void {

		console("-- [minute5Handler] start --");

		try {

			Domain_Company_Scenario_Cron::tierObserve(time());
			Domain_SpaceTariff_Scenario_Cron::cronObserve();
		} catch (\GetCompass\Userbot\Exception\Request\UnexpectedResponseException $e) {

			// время для бекапов с 5:00 до 5:05, когда кроны не работают и сообщение отправится после
			if (date("G") == 5 && intval(date("i")) < 5) {
				return;
			}

			throw $e;
		}
	}
}

/**
 * команды каждые 15 минут
 */
class Cron_General_Minute15Handler {

	/**
	 * run 15 min worker
	 */
	public static function work():void {

		console("-- [minute15Handler] start --");
	}
}

/**
 * команды каждые 30 минут
 */
class Cron_General_Minute30Handler {

	/**
	 * run 30 min worker
	 */
	public static function work():void {

		console("-- [minute30Handler] start --");
	}
}

/**
 * команды каждый час
 */
class Cron_General_HourHandler {

	/**
	 * run hour worker
	 */
	public static function work():void {

		console("-- [hourHandler] start --");

		Cron_General_Utils::collectStat();

		// на проде шлем статистику для бизнеса каждый час
		if (ServerProvider::isProduction() || ServerProvider::isStage()) {
			self::_sendBusinessStat();
		}

		Domain_User_Action_LookForUnusedSmsActions::run(time(), Domain_User_Action_LookForUnusedSmsActions::HOUR_CHECK);
		Domain_User_Action_LookForSmsPhoneCodeStats::run(time(), HOUR1);

		// мониторинг баланса CDN у Selectel только на паблике
		if (ServerProvider::isProduction()) {
			Type_System_Monitoring::balanceSelectelCdnForImap();
		}

		// сбрасываем в коллектор данные по все статусам пользователей один раз в день
		if (!ServerProvider::isTest() && (date("G") == 3)) {

			// шлем аналитику по всем компаниям
			Domain_Analytic_Action_SendSpaceStatusLog::do();

			// шлем аналитику по всем пользователям
			Domain_Analytic_Action_SendAccountStatusLog::do();
		}
	}

	/**
	 * Шлем статистику для бизнеса
	 */
	protected static function _sendBusinessStat():void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// если текущий час не кратен 4, то скипаем
		if (date("G") % 4 != 0) {
			return;
		}

		// если полночь
		if (date("G") == 0) {

			// отправляем большой отчет
			Domain_Analytic_Action_SendGeneralAnalytics::sendBigReport(dayStart(time() - DAY1));
		} else {

			// иначе отправляем отчет с начала текущего дня
			Domain_Analytic_Action_SendGeneralAnalytics::sendShortReport(dayStart(), time());
		}
	}
}

/**
 * команды каждое утро в 4мск
 */
class Cron_General_Morning4AmHandler {

	/**
	 * run morning 4am worker
	 */
	public static function work():void {

		console("-- [Morning4AmHandler] start --");
		Domain_Rating_Action_UpdateUserRating::run();
		Domain_Rating_Action_ClearRawRating::run();
		Domain_User_Entity_Attribution::clearOldData();
	}
}

/**
 * команды каждый день
 */
class Cron_General_DayHandler {

	/**
	 * run day worker
	 */
	public static function work():void {

		console("-- [DayHandler] start --");
		Domain_User_Action_LookForUnusedSmsActions::run(time(), Domain_User_Action_LookForUnusedSmsActions::DAY_CHECK);
		Domain_User_Action_LookForSmsPhoneCodeStats::run(time(), DAY1);
	}
}

# ==============================================================================
# SYSTEM MODULE
# ==============================================================================

/**
 * крон для выполнения команд через определенное время
 */
class Cron_General extends \Cron_Default {

	protected string $bot_name     = "general";
	protected int    $memory_limit = 50;

	/**
	 * Cron_General constructor.
	 */
	function __construct() {

		global $argv;
		if (isset($argv[1]) && $argv[1] === "clear") {

			console("Datastore clear");
			Type_System_Datastore::set($this->_getKey("1min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("5min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("15min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("30min"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("hour"), ["need_work" => 0]);
			Type_System_Datastore::set($this->_getKey("day"), ["need_work" => 0]);
			console("Datastore cleared");
			die();
		}

		parent::__construct();
	}

	/**
	 * run work
	 *
	 * @throws \Exception
	 */
	public function work():void {

		console("BEGIN WORK ...");

		// каждую 1 минуту
		$this->_doMinute1Work();

		// каждые 5 минут
		$this->_doMinute5Work();

		// каждые 15 минут
		$this->_doMinute15Work();

		// каждые пол часа
		$this->_doMinute30Work();

		// каждый час
		$this->_doHour1Work();

		// каждую полночь
		$this->_doDay1Work();

		// каждое утро в 4мск
		$this->_doMorning4AmWork();

		$sleep = random_int(10, 30);
		console("END WORK ... sleep {$sleep} sec");
		$this->sleep($sleep);
	}

	// выполняем ежеминутные команды и обновляем need_work в базе
	protected function _doMinute1Work():void {

		$key  = $this->_getKey("1min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			Type_System_Datastore::set($key, ["need_work" => time() + 60]);
			Cron_General_MinuteHandler::work();
		}
	}

	// выполняем команды каждые 5 минут и обновляем need_work в базе
	protected function _doMinute5Work():void {

		$key  = $this->_getKey("5min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			Type_System_Datastore::set($key, ["need_work" => time() + 60 * 5]);
			Cron_General_Minute5Handler::work();
		}
	}

	// выполняем команды каждые 15 минут и обновляем need_work в базе
	protected function _doMinute15Work():void {

		$key  = $this->_getKey("15min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			Type_System_Datastore::set($key, ["need_work" => time() + 60 * 15]);
			Cron_General_Minute15Handler::work();
		}
	}

	// выполняем команды каждые 30 минут и обновляем need_work в базе
	protected function _doMinute30Work():void {

		$key  = $this->_getKey("30min");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			Type_System_Datastore::set($key, ["need_work" => time() + 60 * 30]);
			Cron_General_Minute30Handler::work();
		}
	}

	// выполняем команды каждый час и обновляем need_work в базе
	protected function _doHour1Work():void {

		$key  = $this->_getKey("hour");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			// выравнивание более мелкие работы если надо
			Type_System_Datastore::set("5min", ["need_work" => hourStart() + 60 * 5]);
			Type_System_Datastore::set("15min", ["need_work" => hourStart() + 60 * 15]);
			Type_System_Datastore::set("30min", ["need_work" => hourStart() + 60 * 30]);

			// в следующий час
			Type_System_Datastore::set($key, ["need_work" => hourStart() + 60 * 60]);

			// выполняем команды
			Cron_General_HourHandler::work();
		}
	}

	// выполяем команды каждый день и обновляем need_work в базе
	protected function _doDay1Work():void {

		$key  = $this->_getKey("day");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			Type_System_Datastore::set($key, ["need_work" => dayStart() + 60 * 60 * 24]);
			Cron_General_DayHandler::work();
		}
	}

	// выполяем команды каждое утро в 4мск и обновляем need_work в базе
	protected function _doMorning4AmWork():void {

		$key  = $this->_getKey("morning4am");
		$temp = Type_System_Datastore::get($key);
		if (!isset($temp["need_work"]) || $temp["need_work"] < time()) {

			Type_System_Datastore::set($key, ["need_work" => dayStart() + DAY1 + (60 * 60 * 4)]);
			Cron_General_Morning4AmHandler::work();
		}
	}

	// формируем первичный ключ для запроса в базу
	protected function _getKey(string $key):string {

		return CODE_UNIQ_VERSION . "_" . $key;
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "pivot_" . parent::_resolveBotName();
	}
}
