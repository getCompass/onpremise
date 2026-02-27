<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для создания новой компании
 */
class Domino_CreateTeam
{
	protected const _INITIAL_TARIFF_MEMBER_LIMIT  = 1000;
	protected const _INITIAL_TARIFF_DAYS_DURATION = 365;

	protected string $_company_name;

	/**
	 * Запускаем работу скрипта
	 */
	public function run(string $company_name): void
	{

		$user_id = Domain_User_Entity_OnpremiseRoot::getUserId();

		if ($user_id === -1) {
			console("Не смогли найти root пользователя.");
			exit(1);
		}

		try {

			// проверяем что такой пользователь зарегистрирован
			Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {

			console("Не смогли найти пользователя с user_id = $user_id");
			exit(1);
		}

		try {

			// создаем команду
			$user_company_api = Domain_Company_Scenario_Api::create(
				$user_id,
				Domain_Company_Entity_Company::ALLOW_AVATAR_COLOR_ID_LIST[array_rand(Domain_Company_Entity_Company::ALLOW_AVATAR_COLOR_ID_LIST)],
				$company_name,
				generateUUID(),
				false
			);

			$company = Gateway_Db_PivotCompany_CompanyList::getOne($user_company_api->company_id);
			$domino  = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company->domino_id);
			$port    = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($domino->domino_id, $company->company_id);
			console("company_id=$user_company_api->company_id;port=$port->port");

			// устанавливаем стартовый тарифный план
			$alteration = Domain_SpaceTariff_Plan_MemberCount_Product_ActivateDefault::makeAlteration(self::_INITIAL_TARIFF_MEMBER_LIMIT, self::_INITIAL_TARIFF_DAYS_DURATION);
			Domain_SpaceTariff_Action_AlterMemberCount::run($user_id, $user_company_api->company_id, \Tariff\Plan\BaseAction::METHOD_FORCE, $alteration);
		} catch (\paramException) {

			console(redText("Данный пользователь не может создать команду"));
			(new self())->start();
		}
	}

	/**
	 * Получаем параметры для скрипта
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 */
	public function start(): void
	{

		// если прислали аргумент --help
		if (Type_Script_InputHelper::needShowUsage()) {

			console("Данный скрипт создает команду ");
			console("Запустите скрипт без флага --help, чтобы начать");

			exit(0);
		}

		$input_company_name = Type_Script_InputParser::getArgumentValue("--name", default: "", required: false);

		try {

			if (!isset($this->_company_name)) {

				$company_name = $input_company_name !== "" ? $input_company_name : readline("Введите имя создаваемой команды (Например: BestTeam): ");

				// проверяем имя пользователя
				$company_name = Domain_Company_Entity_Sanitizer::sanitizeCompanyName($company_name);
				Domain_Company_Entity_Validator::assertIncorrectName($company_name);

				$this->_company_name = $company_name;
			}
		} catch (cs_CompanyIncorrectName) {

			console(redText("Передано некорректное имя команды"));
			$input_company_name ? exit(1) : $this->start();
			return;
		}

		$this->run($this->_company_name);
	}
}

try {
	(new Domino_CreateTeam())->start();
} catch (\Exception $e) {

	console(redText("Не смогли создать команду. Ошибка: " . $e->getMessage()));
	exit(1);
}
