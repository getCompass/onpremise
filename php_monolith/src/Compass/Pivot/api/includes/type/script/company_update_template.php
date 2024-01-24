<?php

namespace Compass\Pivot;

/**
 * Скрипт для обновления данных компании.
 * Чтобы скрипт отработал, нужно в проекте php_company в директории type/script/source написать скрипт по аналогии example.php из той же директории.
 * Имя скрипта (именно файла скрипта) затем передается как параметр аргумента --script-name
 *
 * Вся логика завернута под капот и не требует написания сокетов или еще чего-то.
 *
 * Использование:
 * php --script-name=<имя скрипта> --company-list=[1,2,3] --dry=1
 *
 * Допустимые параметры для вызова:
 * --script-name — имя исполняемого скрипта
 * --dry=1/0 — флаг запуска в режиме dry run, если передана 1, то скрипт не должен вносить изменений
 * --async=0/1 — поставить асинхронную задачу на исполнение, пивот не будет ждать окончания работы скрипта, игнорируется при --dry=1
 * --company-list=[1,2,3,...] — список ид компаний, для которых нужно вызвать скрипт
 * —-log-level=0/1/2 — уровень вывода сообщений в консоль, чем больше значение, тем больше инфы выдает
 * --ignore-errors=0/1 — игнорировать ошибки исполнения или же останавливать и запрашивать реакцию при ошибке обновления компании
 *
 * На вход можно подавать любой параметр и потом его отлавливать по имени Type_Script_InputParser::getArgumentValue("--my-arg", Type_Script_InputParser::TYPE_INT)
 */
abstract class Type_Script_CompanyUpdateTemplate {

	/** @var int маска для исполнения скрипта — вызов без изменений */
	protected const _DRY_MASK = 1 << 0;
	/** @var int маска для исполнения скрипта — асинхронный вызов */
	protected const _ASYNC_MASK = 1 << 1;

	/** @var int уровень логирования только ошибки */
	protected const _LOG_LEVEL_ERROR = 0;
	/** @var int уровень логирования ошибки и важная информация */
	protected const _LOG_LEVEL_IMPORTANCE = 1;
	/** @var int уровень логирования все */
	protected const _LOG_LEVEL_INFO = 2;

	/** @var bool драй ран */
	protected bool $_is_dry_run;
	/** @var bool допустимо ли асинхронное исполнение */
	protected bool $_is_async;
	/** @var int уровень логирования */
	protected int $_log_level;
	/** @var bool останавливать исполнение на ошибках или нет */
	protected bool $_is_ignore_errors;

	/** @var array список разрешенных для вызова ключей */
	protected const _SHARED_ALLOWED_SCRIPT_KEY_LIST = [
		"--help",
		"--dry",
		"--company-list",
		"--user-list",
		"--excluded-company-list",
		"--script-data",
		"--script-name",
		"--log-level",
		"--module-proxy",
		"--ignore-errors",
		"--include-free",
		"--y",
	];

	/** @var array список кастомных разрешенных ключей для скрипта */
	protected const _ALLOWED_SCRIPT_KEY_LIST = [
		"--async", // добавить в свой скрипт для разрешения асинхронного вызова
	];

	/** @var array список необходимых для вызова ключей */
	protected const _SHARED_REQUIRED_SCRIPT_KEY_LIST = [
		"--dry"         => "--dry flag is required",
		"--script-name" => "--script-name flag is required",
	];

	/** @var array список кастомных необходимых для вызова ключей */
	protected const _REQUIRED_SCRIPT_KEY_LIST = [

	];

	/**
	 * Script_Exec_Company_Update_Script constructor.
	 */
	final public function __construct() {

		if (!isCLi()) {
			throw new \RuntimeException(redText("scripts are allowed only in CLI mode"));
		}

		if (Type_Script_InputHelper::needShowUsage()) {

			static::showUsage();
			exit;
		}

		try {

			// составлены списки ключей для проверки
			$required_key_list = array_unique(array_merge(self::_SHARED_REQUIRED_SCRIPT_KEY_LIST, static::_REQUIRED_SCRIPT_KEY_LIST));
			$allowed_key_list  = array_unique(array_merge(self::_SHARED_ALLOWED_SCRIPT_KEY_LIST, static::_ALLOWED_SCRIPT_KEY_LIST));

			// проверяем, что все нужные ключи были переданы
			Type_Script_InputHelper::assertKeys($allowed_key_list, $required_key_list);

			// зафиксируем флаги исполнений, чтобы не бегать за ним потом
			$this->_is_dry_run       = Type_Script_InputHelper::isDry();
			$this->_is_async         = Type_Script_CompanyUpdateInputHelper::isAsync();
			$this->_is_ignore_errors = Type_Script_CompanyUpdateInputHelper::areErrorIgnored();

			// устанавливаем правила логирования
			$log_level        = Type_Script_CompanyUpdateInputHelper::getLogLevel();
			$this->_log_level = is_int($log_level) ? max($log_level, static::_LOG_LEVEL_ERROR) : static::_LOG_LEVEL_IMPORTANCE;

			// dry и async нельзя запускать вместе
			if ($this->_is_dry_run && $this->_is_async) {

				$this->_log(yellowText("async flag is ignored during dry run"));
				$this->_is_async = false;
			}
		} catch (\Exception $e) {

			console(redText("Ошибка исполнения: ") . $e->getMessage());
			die();
		}
	}

	/**
	 * Точка входа в скрипт.
	 */
	final public function run():void {

		$this->_preExec();

		try {

			$company_id_list          = Type_Script_CompanyUpdateInputHelper::getCompanyIdList();
			$excluded_company_id_list = Type_Script_CompanyUpdateInputHelper::getExcludeCompanyIdList();

			// допустимые статусы, по дефолту только активные
			$allowed_status_list = [Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE];

			// есть указан флаг, то еще и свободные
			// неактивные нельзя передать, поскольку они не фурычат
			if (Type_Script_CompanyUpdateInputHelper::needProcessFreeCompanies()) {
				$allowed_status_list[] = Domain_Company_Entity_Company::COMPANY_STATUS_VACANT;
			}

			Type_Script_CompanyUpdater::exec(fn(Struct_Db_PivotCompany_Company $company) => $this->_execWrapper($company), $allowed_status_list, $company_id_list, $excluded_company_id_list);
		} catch (\Exception $e) {
			console($e->getMessage());
		}
	}

	/**
	 * Обертка для вывода информации в консоль.
	 *
	 */
	final protected function _execWrapper(Struct_Db_PivotCompany_Company $company):void {

		try {

			static::_exec($company);
			$this->_log(blueText("company {$company->company_id}: done"), static::_LOG_LEVEL_INFO);
		} catch (\Exception $e) {

			$this->_log(sprintf("company %d: %s %s", $company->company_id, redText("error!"), $e->getMessage()), static::_LOG_LEVEL_ERROR);

			// стопам работу и ждем одобрения, если не проброшен флаг игнорирования ошибкок
			if (!$this->_is_ignore_errors && !Type_Script_InputHelper::assertConfirm("continue?")) {
				return;
			}
		}
	}

	/**
	 * Выводит информацию об использовании
	 */
	public function showUsage():void {

		console(yellowText("удаленный вызов скриптов в компаниях"));
		console(purpleText("необходимые флаги:"));
		console("--dry=[1/0]        — вызов скрипта в режиме изменения или нет");
		console("--script-name=name — имя удаленно вызываемого скрипта");
		console(greenText("допустимые флаги:"));
		console("--company-list=[1, 2, 51-100]          — ид компаний, которые нужно взять в работу");
		console("--excluded-company-list=[1, 2, 51-100] — ид компаний, которые не нужно брать в работу");
		console("--script-data=[my: data]               — произвольные данные для скрипта");
		console("--log-level=[1/2/3]                    — уровень логирования");
		console("--module-proxy=[php_conversation]      — куда проксировать вызов скрипта");
		console("--ignore-errors                        — флаг игнорирования ошибок исполнения, скрипт не будет останавливаться при возникновении ошибка в компании");
		console("--include-free                         — нужно ли обрабатывать свободные компании");
		console("--help                                 — показать эту справку");
	}

	/**
	 * Функция прехука, вызывается до исполнения логики.
	 * Тут можно запросить дополнительные данные из консоли.
	 */
	abstract protected function _preExec():void;

	/**
	 * Исполняющая функция.
	 * Если нужен особенный скрипт, то нужно в дочернем классе переопределить эту функцию.
	 *
	 * В эту функцию по очереди будут поступать все компании, которые удалось получить по идентификаторам.
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	abstract protected function _exec(Struct_Db_PivotCompany_Company $company):void;

	/**
	 * Формирует маску исполнения скрипта.
	 *
	 */
	final protected function _makeMask():int {

		$dry_mask   = $this->_is_dry_run ? static::_DRY_MASK : 0;
		$async_mask = $this->_is_async ? static::_ASYNC_MASK : 0;

		return $dry_mask | $async_mask;
	}

	/**
	 * Выводит информацию в консоль и файл.
	 * Игнорирует флаг тишины.
	 *
	 */
	final protected function _log(string $message, int $level = self::_LOG_LEVEL_IMPORTANCE):void {

		if ($this->_log_level >= $level) {

			Type_System_Admin::log("script-" . Type_Script_CompanyUpdateInputHelper::getScriptName(), $message);
			console($message);
		}
	}

	/**
	 * Выводит ошибки лога исполнения.
	 *
	 */
	protected function _writeErrorLog(Struct_Db_PivotCompany_Company $company, string $error_log):void {

		if (mb_strlen($error_log) === 0) {
			return;
		}

		$this->_log(redText("---------------------------------"), static::_LOG_LEVEL_ERROR);
		$this->_log(redText("company {$company->company_id}: execution error log"), static::_LOG_LEVEL_ERROR);
		$this->_log(redText("---------------------------------"), static::_LOG_LEVEL_ERROR);

		foreach (explode("\n", $error_log) as $log_line) {

			$this->_log(redText("company {$company->company_id}: {$log_line}"), static::_LOG_LEVEL_ERROR);
		}
	}

	/**
	 * Выводит лог исполнения.
	 *
	 */
	protected function _writeLog(Struct_Db_PivotCompany_Company $company, string $script_log):void {

		if (mb_strlen($script_log) === 0) {

			$this->_log(yellowText("company {$company->company_id}: execution log is empty!"), static::_LOG_LEVEL_INFO);
			return;
		}

		$this->_log("---------------------------------", static::_LOG_LEVEL_INFO);
		$this->_log("company {$company->company_id}: execution log", static::_LOG_LEVEL_INFO);
		$this->_log("---------------------------------", static::_LOG_LEVEL_INFO);

		foreach (explode("\n", $script_log) as $log_line) {

			$this->_log("company {$company->company_id}: {$log_line}", static::_LOG_LEVEL_INFO);
		}
	}
}
