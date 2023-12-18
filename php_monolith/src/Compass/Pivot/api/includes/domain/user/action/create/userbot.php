<?php

namespace Compass\Pivot;

/**
 * Действие создание пользовательского бота.
 * КОд не отличается от обычного бота, поэтому просто унаследуем.
 */
class Domain_User_Action_Create_Userbot extends Domain_User_Action_Create_Bot {

	protected const _NPC_TYPE = Type_User_Main::NPC_TYPE_USER_BOT;
}
