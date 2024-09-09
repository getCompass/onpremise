<?php

namespace Compass\Pivot;

/**
 * класс описывает действие по актуализации информации в профиле пользователя на основе данных, полученных из SSO
 * @package Compass\Pivot
 */
class Domain_User_Action_Sso_ActualizeProfileData {

	protected const _DEFAULT_AVATAR_MIME_TYPE = "image/jpeg";
	protected const _DEFAULT_AVATAR_FILE_NAME = "avatar.jpeg";

	protected const _BLOB_REGEX = "/[^\x20-\x7E\t\r\n]/";

	/**
	 * актуализируем информацию в профиле пользователя на основе данных, полученных из SSO
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_FileIsNotImage
	 */
	public static function do(int $user_id, false|string $name, false|string $avatar_file_key, false|string $badge, false|string $role, false|string $description):void {

		// проверяем, передан ли аватар
		if ($avatar_file_key !== false && mb_strlen($avatar_file_key) == 0) {
			$avatar_file_key = false;
		}

		// подготавливаем имя
		$name = Domain_User_Entity_Sanitizer::sanitizeProfileName($name);

		// получаем avatar_file_map
		$avatar_file_map = $avatar_file_key;
		if ($avatar_file_key !== false) {
			$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);
		}

		// обновляем данные на пивоте
		Domain_User_Action_UpdateProfile::do($user_id, $name, $avatar_file_map, is_need_delete_avatar: true);

		// асинхронно обновляем данные на каждой из компаний пользователя
		// делаем это с небольшой задержкой, чтобы
		Type_Phphooker_Main::updateMemberInfoOnAllCompanies($user_id, time() + 10, $badge, $description, $role);
	}

	/**
	 * загружаем аватар из sso
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_CurlError
	 */
	public static function uploadSsoAvatar(string $raw_avatar_data):string {

		// если не передали аватар
		if (mb_strlen($raw_avatar_data) == 0) {
			return "";
		}

		// если это base64
		if (base64_decode($raw_avatar_data, true) !== false) {
			return Domain_User_Action_Avatar_Upload::uploadFileBase64Encoded(self::_DEFAULT_AVATAR_FILE_NAME, self::_DEFAULT_AVATAR_MIME_TYPE, $raw_avatar_data);
		}

		// если это ссылка
		if (filter_var($raw_avatar_data, FILTER_VALIDATE_URL) !== false) {
			return Domain_User_Action_Avatar_Upload::uploadFileUrl(self::_DEFAULT_AVATAR_FILE_NAME, $raw_avatar_data);
		}

		// если это blob
		if (preg_match(self::_BLOB_REGEX, $raw_avatar_data) !== false) {

			$base64_encoded_file_content = base64_encode($raw_avatar_data);
			return Domain_User_Action_Avatar_Upload::uploadFileBase64Encoded(self::_DEFAULT_AVATAR_FILE_NAME, self::_DEFAULT_AVATAR_MIME_TYPE, $base64_encoded_file_content);
		}

		// в крайнем случае обработать данные не смогли – вернем пустую строку
		return "";
	}
}