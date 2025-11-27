<?php

use BaseFrame\Exception\Gateway\BusFatalException;
use Compass\Pivot\cs_CompanyIsHibernate;
use Compass\Pivot\cs_UserNotFound;
use Compass\Pivot\Domain_Company_Action_Member_AddByRole;
use Compass\Pivot\Domain_Company_Entity_User_Member;
use Compass\Pivot\Domain_User_Entity_OnpremiseRoot;
use Compass\Pivot\Gateway_Db_PivotUser_CompanyList;
use Compass\Pivot\Gateway_Socket_Company;
use Compass\Pivot\Gateway_Socket_Exception_CompanyIsNotServed;
use Compass\Pivot\Gateway_Socket_Premise;
use Compass\Pivot\Type_Script_InputParser;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

// user_id дефолтного root-пользователя
$default_root_user_id = Domain_User_Entity_OnpremiseRoot::getUserId();

// получаем user_id
$user_id = Type_Script_InputParser::getArgumentValue("--user-id", Type_Script_InputParser::TYPE_INT, 0);

// проверяем существование пользователя
try {
	$user_info = \Compass\Pivot\Gateway_Bus_PivotCache::getUserInfo($user_id);
} catch (cs_UserNotFound|BusFatalException) {

	console(redText("Ошибка! Пользователь с переданным --user-id: {$user_id} не найден"));
	exit(1);
} catch (BaseFrame\Exception\Request\EndpointAccessDeniedException $e) {

	console(redText("Ошибка! Не удалось получить информацию о пользователе [{$e->getMessage()}]"));
	exit(1);
}

// проверяем, что это не пользователь-бот
if ($default_root_user_id == $user_id) {

	console(redText("Пользователь уже является администратором"));
	exit(1);
}

// проверяем, что это не пользователь-бот
if (!\Compass\Pivot\Type_User_Main::isHuman($user_info->npc_type)) {

	console(redText("Ошибка! Передан некорректный --user-id"));
	exit(1);
}

console("Скрипт выдаст привилегии пользователю {$user_info->full_name} [user_id: {$user_info->user_id}] с паузой в 30 секунд");
console(yellowText("За время паузы, пожалуйста, проверьте, что привилегии выдаются нужному пользователю! В случае ошибки остановите исполнение скрипта, прервав команду в консоли"));
sleep(30);

// делаем рутом
Domain_User_Entity_OnpremiseRoot::setUserId($user_id);
console(greenText("Сделали главным пользователем"));

// выдаем все права на premise
Gateway_Socket_Premise::setPermissions(["premise_administrator" => 1, "premise_accountant" => 1], $default_root_user_id, $user_id);
console(greenText("Выдали права управления лицензией"));

// получаем список активных компаний
$active_company_list = \Compass\Pivot\Gateway_Db_PivotCompany_CompanyList::getActiveList();

// получаем все компании пользователя
$user_company_list    = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);
$user_company_id_list = array_map(
	static fn($item) => $item->company_id,
	$user_company_list
);

// проходимся по каждой компании и обновляем данные об участнике
foreach ($active_company_list as $company) {

	try {

		// добавляем пользователя в компанию, если ранее не состоял
		if (!in_array($company->company_id, $user_company_id_list)) {

			Domain_Company_Action_Member_AddByRole::do(
				$user_id,
				Domain_Company_Entity_User_Member::ROLE_MEMBER,
				$company,
				\BaseFrame\System\Locale::getLocale(),
			);

			console(greenText("Добавили пользователя в компанию {$company->name}"));
		}
	} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
	}

	// ждем пока пользователь добавится
	sleep(3);

	try {

		// выдаем все права в компании
		Gateway_Socket_Company::setPermissions($company, $user_id, [
			"group_administrator"              => 1,
			"bot_management"                   => 1,
			"message_delete"                   => 1,
			"member_profile_edit"              => 1,
			"member_invite"                    => 1,
			"member_kick"                      => 1,
			"space_settings"                   => 1,
			"administrator_management"         => 1,
			"administrator_statistic_infinite" => 1,
		]);

		console(greenText("Выдали права администратора в компании {$company->name}"));
	} catch (Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate) {
	}
}
