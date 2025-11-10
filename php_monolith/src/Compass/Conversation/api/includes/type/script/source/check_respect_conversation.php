<?php

namespace Compass\Conversation;

/**
 * Class Type_Script_Source_CheckRespectConversation
 */
class Type_Script_Source_CheckRespectConversation extends Type_Script_CompanyUpdateTemplate {

	/** @var array данные об исполнении */
	protected array $_log = [];

	/**
	 * Точка входа в скрипт.
	 */
	public function exec(array $data, int $mask = 0):void {

		$group_key_name = Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME;
		$value          = Domain_Conversation_Action_Config_Get::do($group_key_name);

		$company_id = COMPANY_ID;

		if (isset($value["value"]) && mb_strlen($value["value"]) > 0) {
			$this->_log(greenText("Есть чат в пространстве {$company_id}"));
		} else {
			$this->_log(redText("Нет чата Спасибо в пространстве {$company_id}"));
		}
	}
}
