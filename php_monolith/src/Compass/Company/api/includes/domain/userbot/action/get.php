<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\BlockException;

/**
 * Класс action для получения бота
 */
class Domain_Userbot_Action_Get {

	/**
	 * Выполняем действие
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \returnException
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 */
	public static function do(string $userbot_id, int $developer_user_id):array {

		// проверяем, может пользователь уже словил блокировку при попытке перебрать userbot_id
		Type_Antispam_User::assertKeyIsNotBlocked($developer_user_id, Type_Antispam_User::USERBOT_NOT_FOUND);

		try {
			$userbot = Domain_Userbot_Entity_Userbot::get($userbot_id);
		} catch (Domain_Userbot_Exception_UserbotNotFound) {

			// инкрементим блокировку по user_id пользователя
			Type_Antispam_User::throwIfBlocked($developer_user_id, Type_Antispam_User::USERBOT_NOT_FOUND);
			throw new Domain_Userbot_Exception_UserbotNotFound("userbot not found");
		}

		// получаем сингл чат с ботом если он есть
		[$single_conversation, $_] = Gateway_Socket_Conversation::getConversationCardList($developer_user_id, $userbot->user_id);

		return [$userbot, $single_conversation];
	}
}