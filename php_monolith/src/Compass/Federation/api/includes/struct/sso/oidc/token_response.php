<?php

namespace Compass\Federation;

/**
 * структура описывающая ответ от SSO провайдера при успешном запросе токена
 * @package Compass\Federation
 */
class Struct_Sso_Oidc_TokenResponse {

	public function __construct(
		public ?string $token_type,
		public ?int    $expires_in,
		public ?string $resource,
		public ?string $scope,
		public ?string $access_token,
		public ?string $refresh_token,
		public ?int    $refresh_token_expires_in,
		public ?string $id_token,
		public array   $user_info_data = [],
	) {
	}

	/**
	 * конвертируем ассоц. массив в структуру
	 *
	 * @return static
	 */
	public static function arrayToStruct(array $array):self {

		return new self(
			token_type: $array["token_type"] ?? "",
			expires_in: $array["expires_in"] ?? 0,
			resource: $array["resource"] ?? "",
			scope: $array["scope"] ?? "",
			access_token: $array["access_token"] ?? "",
			refresh_token: $array["refresh_token"] ?? "",
			refresh_token_expires_in: $array["refresh_token_expires_in"] ?? 0,
			id_token: $array["id_token"] ?? "",
			user_info_data: $array["user_info_data"] ?? [],
		);
	}

	/**
	 * записываем данные user_info
	 */
	public function setUserInfoData(array $user_info_data):void {

		$this->user_info_data = $user_info_data;
	}
}