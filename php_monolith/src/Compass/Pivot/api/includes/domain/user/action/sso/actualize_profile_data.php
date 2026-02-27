<?php

namespace Compass\Pivot;

/**
 * класс описывает действие по актуализации информации в профиле пользователя на основе данных, полученных из SSO
 * @package Compass\Pivot
 */
class Domain_User_Action_Sso_ActualizeProfileData
{
	protected const _DEFAULT_AVATAR_MIME_TYPE = "image/jpeg";
	protected const _DEFAULT_AVATAR_FILE_NAME = "avatar.jpeg";
	protected const _BLOB_REGEX               = "/[^\x20-\x7E\t\r\n]/";

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
	public static function do(int $user_id, false | string $name, Domain_User_Action_Sso_ActualizeProfileData_AvatarAction $avatar_action, string $avatar_file_key, false | string $badge, false | string $role, false | string $description): void
	{

		// подготавливаем имя
		$name = Domain_User_Entity_Sanitizer::sanitizeProfileName($name);

		// подготавливаем avatar_file_map
		$avatar_file_map = match ($avatar_action) {
			Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::NO_ACTION,
			Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::CLEAR  => false,
			Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::CHANGE => Type_Pack_File::doDecrypt($avatar_file_key),
		};

		// обновляем информацию о пользователе на пивоте
		Domain_User_Action_UpdateProfile::do($user_id, $name, $avatar_file_map);

		// если требуется очистить аватар пользователя
		if ($avatar_action === Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::CLEAR) {
			Domain_User_Action_ClearAvatar::do($user_id);
		}

		// асинхронно обновляем данные на каждой из компаний пользователя
		// делаем это с небольшой задержкой, чтобы
		Type_Phphooker_Main::updateMemberInfoOnAllCompanies($user_id, time() + 10, $badge, $description, $role);
	}

	/**
	 * подготавливаем параметры для обновления аватара пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function prepareAvatarData(?string $sso_avatar_data, bool $is_empty_attributes_update_enabled = true): array
	{

		// если пришел null, то нет никакого маппинга – ничего делать не нужно
		if (is_null($sso_avatar_data)) {
			return [Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::NO_ACTION, ""];
		}

		// если пришла пустота – значит аватара у учетной записи нет
		if ($sso_avatar_data === "") {

			// если отключено обновление при пустом аттрибуте - не обновляем
			if (!$is_empty_attributes_update_enabled) {
				return [Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::NO_ACTION, ""];
			}

			return [Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::CLEAR, ""];
		}

		// иначе загружаем аватар
		try {
			$avatar_file_key = self::uploadSsoAvatar($sso_avatar_data);
		} catch (\cs_CurlError $e) {

			// логируем
			Type_System_Admin::log("sso_oidc", ["text" => "Не удалось загрузить аватар из учетной записи", "exception" => $e->getMessage(), "trace" => $e->getTraceAsString()]);

			// в таком случае ничего не делаем, чтобы не снести аватарку
			return [Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::NO_ACTION, ""];
		}

		return [Domain_User_Action_Sso_ActualizeProfileData_AvatarAction::CHANGE, $avatar_file_key];
	}

	/**
	 * загружаем аватар из sso
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_CurlError
	 */
	public static function uploadSsoAvatar(string $raw_avatar_data): string
	{

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
