<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Контроллер сокет методов для взаимодействия с
 * данными пользователя между pivot сервером и компаниями
 */
class Socket_Company_User extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getUserInfo",
		"getUserInfoList",
		"getBeforeRedesignUserInfo",
		"createInviteLink",
		"updateInviteLinkStatus",
	];

	/**
	 * Получает данные пользователя
	 *
	 * @post user_id_list
	 * @post company_id
	 */
	public function getUserInfo():array {

		// ищем пользователей для компании
		try {

			$user_info = Domain_User_Scenario_Socket::getUserInfo($this->user_id, $this->company_id);
		} catch (\cs_RowIsEmpty) {
			return $this->error(404, "user not found");
		}

		return $this->ok([
			"user_id"                 => (int) $user_info->user_id,
			"full_name"               => (string) $user_info->full_name,
			"avatar_file_key"         => (string) mb_strlen($user_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($user_info->avatar_file_map) : "",
			"avg_screen_time"         => (int) Type_User_Main::getAvgScreenTime($user_info->extra),
			"total_action_count"      => (int) Type_User_Main::getTotalActionCount($user_info->extra),
			"avg_message_answer_time" => (int) Type_User_Main::getAvgMessageAnswerTime($user_info->extra),
			"avatar_color_id"         => (int) Type_User_Main::getAvatarColorId($user_info->extra),
		]);
	}

	/**
	 * Возвращает данные о пользователе.
	 *
	 * @throws
	 */
	public function getUserInfoList():array {

		$user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		// ищем пользователей для компании
		try {

			$user_info_list = Domain_User_Scenario_Socket::getUserInfoList($user_id_list, $this->company_id);
		} catch (\cs_RowIsEmpty) {
			return $this->error(404, "user not found");
		}

		$output_user_info_list = [];
		foreach ($user_info_list as $user_info) {

			$avatar_color_id = Type_User_Main::getAvatarColorId($user_info->extra);
			$avatar_color_id = $avatar_color_id === 0
				? \BaseFrame\Domain\User\Avatar::getColorByUserId($user_info->user_id) :
				$avatar_color_id;

			$output_user_info_list[] = [
				"user_id"         => (int) $user_info->user_id,
				"full_name"       => (string) $user_info->full_name,
				"avatar_file_key" => (string) mb_strlen($user_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($user_info->avatar_file_map) : "",
				"avatar_color_id" => (int) $avatar_color_id,
			];
		}
		return $this->ok([
			"user_info_list" => (array) $output_user_info_list,
		]);
	}

	/**
	 * Возвращает данные о пользователе. ТОЛЬКО ДЛЯ СКРИПТОВ
	 * @return array
	 * @throws
	 */
	public function getBeforeRedesignUserInfo():array {

		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database("pivot_user_10m")->getOne($query, "user_list_1", $this->user_id, 1); // ТУТ СПЕЦИЛЬАНО ТАК так как нужно не зависеть от классов старых

		$extra = fromJson($row["extra"]);

		$mbti_type      = "";
		$badge_content  = "";
		$badge_color_id = 0;
		if (isset($extra["extra"])) {

			$mbti_type      = $extra["extra"]["mbti_type"] ?? "";
			$badge_content  = $extra["extra"]["badge"]["content"] ?? "";
			$badge_color_id = $extra["extra"]["badge"]["color_id"] ?? 0;
		}
		return $this->ok([
			"full_name"         => (string) $row["full_name"],
			"mbti_type"         => (string) $mbti_type,
			"badge_content"     => (string) $badge_content,
			"badge_color_id"    => (int) $badge_color_id,
			"short_description" => (string) $row["short_description"],
			"avatar_file_key"   => (string) mb_strlen($row["avatar_file_map"]) > 0 ? Type_Pack_File::doEncrypt($row["avatar_file_map"]) : "",
		]);
	}

	/**
	 * * создаем инвайт-ссылку
	 *
	 * @post company_id
	 *
	 * @throws \Exception
	 */
	public function createInviteLink():array {

		$status_alias = $this->post(\Formatter::TYPE_INT, "status_alias");

		$join_link_uniq = Domain_Company_Scenario_Socket::createJoinLink($this->company_id, $status_alias);

		return $this->ok([
			"invite_link_uniq" => (string) $join_link_uniq,
		]);
	}

	/**
	 * обновляем статус_алиас ссылки-инвайта
	 *
	 * @post invite_link_uniq
	 * @post status_alias
	 */
	public function updateInviteLinkStatus():array {

		$join_link_uniq = $this->post(\Formatter::TYPE_STRING, "invite_link_uniq");
		$status_alias   = $this->post(\Formatter::TYPE_INT, "status_alias");

		Domain_Company_Scenario_Socket::updateJoinLinkStatus($join_link_uniq, $status_alias);

		return $this->ok();
	}
}
