<?php

namespace Compass\Speaker;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Domain\Member\Struct\Short;

/**
 * Действие для инкремента количества действий
 */
class Domain_User_Action_IncActionCount {

	protected const _CALLS = "calls";

	/**
	 * Инкрементим количество звонков
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function incCall(int $user_id, string $conversation_map, Short $user_info = null):void {

		self::_send($user_id, $conversation_map, self::_CALLS, $user_info);
	}

	/**
	 * Отправляем
	 *
	 * @param int        $user_id
	 * @param string     $conversation_map
	 * @param string     $action
	 * @param Short|null $user_info
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \returnException
	 */
	protected static function _send(int $user_id, string $conversation_map, string $action, Short $user_info = null):void {

		if ($user_id < 1) {
			return;
		}

		// если не передали - получаем
		if (is_null($user_info)) {

			$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
			$user_info      = $user_info_list[$user_id];
		}

		// инкрементим количество действий
		$is_human = Type_User_Main::isHuman($user_info->npc_type);
		Gateway_Bus_Rating_Main::incActionCount($user_id, $conversation_map, $action, $is_human);
	}
}