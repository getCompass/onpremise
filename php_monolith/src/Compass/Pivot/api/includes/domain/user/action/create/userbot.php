<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Действие создание пользовательского бота.
 * КОд не отличается от обычного бота, поэтому просто унаследуем.
 */
class Domain_User_Action_Create_Userbot extends Domain_User_Action_Create_Bot {

	protected const _NPC_TYPE = Type_User_Main::NPC_TYPE_USER_BOT;

	/**
	 * Совершаем все необходимые действия после создания пользователя
	 *
	 * @param Struct_User_Action_Create_Store $data
	 *
	 * @return Struct_User_Action_Create_Store
	 * @throws \queryException
	 */
	public static function effect(Struct_User_Action_Create_Store $data):Struct_User_Action_Create_Store {

		if (!ServerProvider::isOnPremise()) {
			return $data;
		}

		// отправляем в premise-модуль событие о регистрации бота
		Gateway_Socket_Premise::userRegistered($data->user->user_id, $data->user->npc_type, 0);

		return $data;
	}
}
