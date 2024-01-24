<?php

namespace Compass\Pivot;

/**
 * класс-агрегат для информации пользователя
 */
class Struct_User_Info {

	/**
	 * Struct_User_Info constructor.
	 *
	 * @param int    $user_id
	 * @param string $type
	 * @param int    $is_verified
	 * @param string $full_name
	 * @param string $avatar_file_map
	 * @param int    $avg_screen_time
	 * @param int    $total_action_count
	 * @param int    $avg_message_answer_time
	 * @param int    $avatar_color_id
	 * @param int    $disabled_at
	 */
	public function __construct(
		public int    $user_id,
		public string $type,
		public int    $is_verified,
		public string $full_name,
		public string $avatar_file_map,
		public int    $avg_screen_time,
		public int    $total_action_count,
		public int    $avg_message_answer_time,
		public int    $avatar_color_id,
		public int    $disabled_at,
	) {

	}

	// заполняем структуру
	public static function createStruct(Struct_Db_PivotUser_User $user_info):self {

		return new self(
			$user_info->user_id,
			Type_User_Main::getUserType($user_info->npc_type),
			self::_isUserVerified($user_info->npc_type),
			$user_info->full_name,
			$user_info->avatar_file_map,
			Type_User_Main::getAvgScreenTime($user_info->extra),
			Type_User_Main::getTotalActionCount($user_info->extra),
			Type_User_Main::getAvgMessageAnswerTime($user_info->extra),
			self::_getAvatarColorId($user_info->user_id, $user_info->extra),
			Type_User_Main::getProfileDisabledAt($user_info->extra),
		);
	}

	/**
	 *  Есть ли у пользователя галочка в карточке и интерфейсе
	 *
	 */
	protected static function _isUserVerified(int $npc_type):bool {

		return match ($npc_type) {

			Type_User_Main::NPC_TYPE_BOT => true,
			default                      => false,
		};
	}

	/**
	 *  Получить id цвета аватара
	 *
	 */
	protected static function _getAvatarColorId(int $user_id, array $extra):int {

		$avatar_color_id = Type_User_Main::getAvatarColorId($extra);
		return $avatar_color_id === 0 ? \BaseFrame\Domain\User\Avatar::getColorByUserId($user_id) : $avatar_color_id;
	}
}
