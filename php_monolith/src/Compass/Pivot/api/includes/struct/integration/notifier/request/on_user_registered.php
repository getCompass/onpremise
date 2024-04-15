<?php

namespace Compass\Pivot;

/** стурктура уведомления о регистрации пользователя, отправляемого в модуль интеграции */
class Struct_Integration_Notifier_Request_OnUserRegistered {

	public function __construct(
		public int    $user_id,
		public int    $auth_method,
		public string $registered_by_phone_number,
		public string $registered_by_mail,
		public string $join_link_uniq,
	) {
	}

	public function formatNotifyParameters():array {

		return [
			"user_id"                    => $this->user_id,
			"auth_method"                => self::_stringifyAuthMethod($this->auth_method),
			"registered_by_phone_number" => $this->registered_by_phone_number,
			"registered_by_mail"         => $this->registered_by_mail,
			"join_link_uniq"             => $this->join_link_uniq,
		];
	}

	protected static function _stringifyAuthMethod(int $auth_method):string {

		return match ($auth_method) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER => "phone_number",
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL         => "mail",
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_SSO              => "sso",
		};
	}
}