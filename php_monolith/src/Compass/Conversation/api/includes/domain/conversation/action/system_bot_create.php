<?php

namespace Compass\Conversation;

/**
 * Action для создания дилаога с системным ботом
 */
class Domain_Conversation_Action_SystemBotCreate {

	/**
	 * выполняем действие по созданию диалога с системным ботом
	 *
	 * @throws \busException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws \paramException
	 * @throws \parseException
	 */
	public static function do(int $user_id):array {

		// получаем конфиг системного бота
		$system_bot = getConfig("BOT_SYSTEM");

		// создаем public диалог
		return Helper_Single::createSingleWithSystemBotIfNotExist($system_bot["user_id"], $user_id);
	}
}