<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Action для генерации конфигов компании
 */
class Domain_Domino_Entity_Config {

	/** @var array список статусов компаний, для которых можно создавать конфиг с демоном */
	protected const _ALLOWED_TO_CREATE_MYSQL_CONFIG_STATUS_LIST = [
		Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE,
		Domain_Company_Entity_Company::COMPANY_STATUS_VACANT,
	];

	protected const _TIMESTAMP_FILE    = ".timestamp.json";
	protected const _DOMINO_HOSTS_FILE = ".domino_hosts.json";

	/**
	 * Сформировать секцию с mysql
	 *
	 * @param int                                             $status
	 * @param Struct_Db_PivotCompany_Company                  $company
	 * @param Struct_Db_PivotCompanyService_DominoRegistry    $domino
	 * @param Struct_Db_PivotCompanyService_PortRegistry|null $port
	 *
	 * @return Struct_Config_Company_Mysql|null
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function makeMysql(int                                             $status,
						   Struct_Db_PivotCompany_Company                  $company,
						   Struct_Db_PivotCompanyService_DominoRegistry    $domino,
						   Struct_Db_PivotCompanyService_PortRegistry|null $port = null):Struct_Config_Company_Mysql|null {

		// проверяем, что для переданных данных можно сгенерить конфиг
		try {
			static::_assert($status, $company, $domino, $port);
		} catch (\Exception $e) {

			Type_System_Admin::log("assert_company_port_make", [
				"company" => $company,
				"domino"  => $domino,
				"port"    => $port,
			]);

			throw $e;
		}

		if (is_null($port)) {

			// если порт не привязан, то конфиг базы данных генерируем пустым
			return null;
		}

		// в конфиг базы данных добавляем данные из порта
		$mysql_user = \BaseFrame\System\Crypt::decrypt(Domain_Domino_Entity_Port_Registry::getEncryptedMysqlUser($port->extra));
		$mysql_pass = \BaseFrame\System\Crypt::decrypt(Domain_Domino_Entity_Port_Registry::getEncryptedMysqlPass($port->extra));

		$mysql_host = $port->host !== "" ? $port->host : $domino->domino_id . "-" . $port->port;

		$host = ServerProvider::isOnPremise() ? $mysql_host : $domino->database_host;

		return new Struct_Config_Company_Mysql($host, $port->port, $mysql_user, $mysql_pass);
	}

	/**
	 * Сформировать секцию с тарифами
	 *
	 * @param \Tariff\Plan\MemberCount\SaveData $member_count_plan
	 *
	 * @return Struct_Config_Company_Tariff|null
	 */
	public static function makeTariff(\Tariff\Plan\MemberCount\SaveData $member_count_plan):Struct_Config_Company_Tariff|null {

		return new Struct_Config_Company_Tariff(new Struct_Config_Company_Tariff_PlanInfo(
			$member_count_plan
		));
	}

	/**
	 * Проверяем возможность создать конфиг для переданных данных.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _assert(int $status, Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_DominoRegistry $domino, Struct_Db_PivotCompanyService_PortRegistry|null $port = null):void {

		// проверяем, что домино совпадает
		if ($company->domino_id !== $domino->domino_id) {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("company domino is not equal to passed domino");
		}

		if (in_array($status, static::_ALLOWED_TO_CREATE_MYSQL_CONFIG_STATUS_LIST)) {

			// проверяем, что порт не пустой при привязке к компании
			if (is_null($port)) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("active company can not be bound with null port");
			}

			// проверяем, что для активной компании не пытаемся прикрутить сервсиный порт
			if (Domain_Domino_Entity_Port_Registry::isService($port)) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("active company can not be bound with service port");
			}

			// проверяем, что для активной компании не пытаемся прикрутить порт другой компании
			if ($port->company_id !== $company->company_id) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("port's company is not equal to passed company");
			}

			// проверяем, что для активной компании не пытаемся прикрутить порт компании
			if ($port->status !== Domain_Domino_Entity_Port_Registry::STATUS_ACTIVE) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("active company can not be bound with non-active port");
			}
		} else {

			// для неактивных компаний нельзя привязывать порт
			// неактивные компании не должны обслуживать запросы
			if (!is_null($port)) {
				throw new \BaseFrame\Exception\Domain\ParseFatalException("non-active company can't be bound with any port");
			}
		}
	}

	/**
	 * Перезаписывает конфигурационный файл для компании на диске.
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function update(int $company_id, Struct_Config_Company_Main $config, bool $need_force_update = false):void {

		$domino_path = self::_getDominoPath($config->domino_id);

		if (!file_exists($domino_path) && !mkdir($domino_path, 0644, true) && !is_dir($domino_path)) {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("Directory $domino_path was not created");
		}

		// генерим пути для хранения конфигов
		$php_config_path  = self::getCompanyPhpConfigPath($config->domino_id, $company_id);
		$json_config_path = self::getCompanyJsonConfigPath($config->domino_id, $company_id);

		// шаманим над типами, нам нужен и в виде массива и в виде json
		$json_config  = toJson($config);
		$array_config = fromJson($json_config);

		// если файлов не было - создаем
		if (!file_exists($php_config_path)) {

			if (!touch($php_config_path, 0) || !touch($json_config_path, 0)) {
				throw new \BaseFrame\Exception\Domain\ReturnFatalException("can't create config files: {$php_config_path}, {$json_config_path}");
			}
		} elseif (!$need_force_update) {

			$existing_config = include($php_config_path);

			// если существующий и новый конфиг идентичны,
			// то не нужно перезаписывать данные
			if (areValuesEqual($existing_config, $array_config)) {
				return;
			}
		}

		// генерим из него php file
		$config_php = "<?php" . PHP_EOL . "return " . var_export($array_config, true) . ";";

		// кладем в конфиг инфу и устанавливаем время изменения равному updated_at в базе
		file_put_contents($json_config_path, $json_config);
		file_put_contents($php_config_path, $config_php);

		// устанавливаем метку времени
		self::_setTimestampFile($domino_path, $config->domino_id);
		self::_setTimestampFile(DOMINO_CONFIG_PATH, $config->domino_id);
	}

	/**
	 * Вернуть конфиг компании
	 * @throws Domain_Company_Exception_ConfigNotExist
	 */
	public static function get(Struct_Db_PivotCompany_Company $company):Struct_Config_Company_Main {

		$domino_path     = self::_getDominoPath($company->domino_id);
		$php_config_path = $domino_path . "{$company->company_id}_company.php";

		if (!file_exists($php_config_path)) {
			throw new Domain_Company_Exception_ConfigNotExist("config dont exist");
		}

		// если нужно - инвалидируем opcache, чтобы получить свежий конфиг. invalidate сам поймет, нужно ли чистить кэш
		opcache_invalidate($php_config_path);

		$company_config = include($php_config_path);

		$output_config = new Struct_Config_Company_Main($company_config["status"], $company_config["domino_id"]);

		$mysql_config = isset($company_config["mysql"]) ? Struct_Config_Company_Mysql::fromConfig($company_config["mysql"]) : null;
		$output_config->setMysql($mysql_config);

		$tariff_config = isset($company_config["tariff"]) ? Struct_Config_Company_Tariff::fromConfig($company_config["tariff"]) : null;
		$output_config->setTariff($tariff_config);

		return $output_config;
	}

	/**
	 * Очистить конфиги домино
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino
	 *
	 * @return void
	 */
	public static function clear(Struct_Db_PivotCompanyService_DominoRegistry $domino):void {

		$domino_path = self::_getDominoPath($domino->domino_id);

		foreach (glob($domino_path . "/*") as $file) {

			if (is_file($file)) {
				unlink($file); // nosemgrep
			}
		}
	}

	/**
	 * Сбрасывает конфиг для компании.
	 */
	public static function invalidate(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_DominoRegistry $domino, bool $force = false):void {

		if (!$force && $company->domino_id === $domino->domino_id) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("can't invalidate config — company belong to domino");
		}

		// генерим пути для хранения конфигов
		$php_config_path  = self::getCompanyPhpConfigPath($domino->domino_id, $company->company_id);
		$json_config_path = self::getCompanyJsonConfigPath($domino->domino_id, $company->company_id);

		// nosemgrep
		if (file_exists($php_config_path) && !unlink($php_config_path)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("can't delete file {$php_config_path}");
		}

		// nosemgrep
		if (file_exists($json_config_path) && !unlink($json_config_path)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("can't delete file {$json_config_path}");
		}
	}

	/**
	 * получаем путь к конфигам доминошки
	 *
	 * @param string $domino_id
	 *
	 * @return string
	 */
	protected static function _getDominoPath(string $domino_id):string {

		return sprintf("%s%s_domino/", DOMINO_CONFIG_PATH, $domino_id);
	}

	/**
	 * получаем путь к php конфигу компании
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 *
	 * @return string
	 */
	#[\JetBrains\PhpStorm\Pure]
	public static function getCompanyPhpConfigPath(string $domino_id, int $company_id):string {

		$domino_path = self::_getDominoPath($domino_id);
		return $domino_path . "{$company_id}_company.php";
	}

	/**
	 * получаем путь к json конфигу компании
	 *
	 * @param string $domino_id
	 * @param int    $company_id
	 *
	 * @return string
	 */
	#[\JetBrains\PhpStorm\Pure]
	public static function getCompanyJsonConfigPath(string $domino_id, int $company_id):string {

		$domino_path = self::_getDominoPath($domino_id);
		return $domino_path . "{$company_id}_company.json";
	}

	/**
	 * Добавить хост домино для работы конфигов
	 *
	 * @param Struct_Db_PivotCompanyService_DominoRegistry $domino
	 *
	 * @return void
	 */
	public static function addDominoHost(Struct_Db_PivotCompanyService_DominoRegistry $domino):void {

		if (!file_exists(DOMINO_CONFIG_PATH . self::_DOMINO_HOSTS_FILE)) {

			file_put_contents(DOMINO_CONFIG_PATH . self::_DOMINO_HOSTS_FILE, "{}");
			chmod(DOMINO_CONFIG_PATH . self::_DOMINO_HOSTS_FILE, 0600);
		}

		$domino_hosts                     = fromJson(file_get_contents(DOMINO_CONFIG_PATH . self::_DOMINO_HOSTS_FILE));
		$domino_hosts[$domino->domino_id] = $domino->code_host;
		file_put_contents(DOMINO_CONFIG_PATH . self::_DOMINO_HOSTS_FILE, toJson($domino_hosts));
	}

	/**
	 * Установить метку времени последнего изменения файла конфига
	 *
	 * @param string $path
	 * @param string $domino_id
	 *
	 * @return void
	 */
	protected static function _setTimestampFile(string $path, string $domino_id):void {

		// если файлов не было - создаем
		if (!file_exists($path . self::_TIMESTAMP_FILE)) {
			file_put_contents($path . self::_TIMESTAMP_FILE, "{}");
		}

		$timestamp_arr             = fromJson(file_get_contents($path . self::_TIMESTAMP_FILE));
		$timestamp_arr[$domino_id] = time();
		file_put_contents($path . self::_TIMESTAMP_FILE, toJson($timestamp_arr));
	}
}