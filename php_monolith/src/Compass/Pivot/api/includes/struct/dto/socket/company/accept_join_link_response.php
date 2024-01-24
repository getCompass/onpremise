<?php

namespace Compass\Pivot;

/**
 * класс DTO ответа socket-метода hiring.invitelink.accept
 */
class Struct_Dto_Socket_Company_AcceptJoinLinkResponse {

	public function __construct(
		public int    $inviter_user_id,
		public bool   $is_postmoderation,
		public int    $entry_option,
		public int    $user_space_role,
		public int    $user_space_permissions,
		public int    $entry_id,
		public int    $status,
		public string $company_push_token,
	) {
	}

	/**
	 * Собираем объект из ответа
	 *
	 * @return static
	 */
	public static function makeFromResponse(array $response):self {

		return new self(
			$response["inviter_user_id"],
			$response["is_postmoderation"],
			$response["entry_option"],
			$response["user_space_role"],
			$response["user_space_permissions"],
			$response["entry_id"],
			$response["status"],
			$response["token"],
		);
	}
}
