<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для создания диалогов поддержки
 */
class Script_Create_Support_Conversation extends Type_Script_CompanyUpdateTemplate {

	/**
	 * Функция прехука, вызывается до исполнения логики.
	 * Тут можно запросить дополнительные данные из консоли.
	 */
	protected function _preExec():void {

		console(purpleText("executing script " . static::class));
	}

	/**
	 * Исполняющая функция.
	 * Если нужен особенный скрипт, то нужно в дочернем классе переопределить эту функцию.
	 *
	 * В эту функцию по очереди будут поступать все компании, которые удалось получить по идентификаторам.
	 * @long
	 */
	protected function _exec(Struct_Db_PivotCompany_Company $company):void {

		$script_name = "CreateSupportConversation";

		if ($this->_is_dry_run) {

			$this->_log(purpleText("{$script_name} dry-run call for company {$company->company_id}... "));
		}

		$user_id_list = Type_Script_InputParser::getArgumentValue("user-list", Type_Script_InputParser::TYPE_ARRAY, [], false);
		if (count($user_id_list) < 1) {

			// получаем информацию по пользователям
			$full_user_id_list = Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($company->company_id);
			$user_info_list    = Gateway_Bus_PivotCache::getUserListInfo($full_user_id_list);

			foreach ($user_info_list as $user_info) {

				// удалённых скипаем
				if (Type_User_Main::isDisabledProfile($user_info->extra)) {
					continue;
				}

				// исключаем ботов
				if (!Type_User_Main::isHuman($user_info->npc_type)) {
					continue;
				}
				$user_id_list[] = $user_info->user_id;
			}
		}
		$script_data["user_id_list"] = $user_id_list;

		[$script_log, $error_log] = Gateway_Socket_Company::execCompanyUpdateScript(
			$script_name,
			$script_data,
			$this->_makeMask(),
			$company->company_id,
			$company->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company->extra),
			["php_conversation"],
		);

		$this->_writeErrorLog($company, $error_log);
		$this->_writeLog($company, $script_log);
	}
}

(new Script_Create_Support_Conversation())->run();