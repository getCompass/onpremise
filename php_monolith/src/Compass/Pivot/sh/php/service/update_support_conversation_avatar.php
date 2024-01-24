<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для обновления avatar_file_map диалога поддержки в компаниях
 */
class Script_Update_Support_Conversation_Avatar extends Type_Script_CompanyUpdateTemplate {

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
	 */
	protected function _exec(Struct_Db_PivotCompany_Company $company):void {

		$script_name = "SetSupportConversationAvatar";

		if ($this->_is_dry_run) {

			$this->_log(purpleText("{$script_name} dry-run call for company {$company->company_id}... "));
		}

		// получаем файл и отправляем в компанию
		$support_file                   = Gateway_Db_PivotSystem_DefaultFileList::get("support_conversation_avatar");
		$script_data["avatar_file_map"] = Type_Pack_File::doDecrypt($support_file->file_key);

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

(new Script_Update_Support_Conversation_Avatar())->run();