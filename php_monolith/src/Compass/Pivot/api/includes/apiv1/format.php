<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для форматирования сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго за отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv1_Format {

	// массив для преобразования внутреннего типа статус пользователя компании во внешний
	protected const _USER_COMPANY_STATUS_SCHEMA = [
		Domain_Company_Entity_User_Member::ACTIVE_USER_COMPANY_STATUS     => "active_member",
		Domain_Company_Entity_User_Member::FIRED_USER_COMPANY_STATUS      => "fired",
		Domain_Company_Entity_User_Member::NOT_MEMBER_USER_COMPANY_STATUS => "not_member",
		Domain_Company_Entity_User_Member::DELETED_COMPANY_STATUS         => "deleted_company",
	];

	/**
	 * Форматируем данные о флагах стран
	 *
	 * @param array $country_flag_list
	 *
	 * @return array
	 */
	public static function countryFlagList(array $country_flag_list):array {

		// подводим под формат
		$formatted_country_flag_list = [];
		foreach ($country_flag_list as $v) {
			$formatted_country_flag_list[] = self::_makeFlagOutput($v);
		}

		return [
			"country_flag_list" => (array) $formatted_country_flag_list,
		];
	}

	/**
	 * Формируем массив flag
	 *
	 * @param array $flag
	 *
	 * @return array
	 */
	protected static function _makeFlagOutput(array $flag):array {

		return [
			"phone_code"              => (string) $flag["phone_code"],
			"flag_emoji_short_name"   => (string) $flag["flag_emoji_short_name"],
			"country_name_dictionary" => (array) self::_makeCountryNameDictionary($flag["country_name_dictionary"]),
			"country_code"            => (string) $flag["country_code"],
		];
	}

	/**
	 * Собираем словарь названий стран на разных языках
	 *
	 * @param array $country_name_dictionary
	 *
	 * @return array
	 */
	protected static function _makeCountryNameDictionary(array $country_name_dictionary):array {

		$formatted_country_name_dictionary = [];
		foreach ($country_name_dictionary as $lang => $country_name) {
			$formatted_country_name_dictionary[(string) $lang] = (string) $country_name;
		}

		return $formatted_country_name_dictionary;
	}

	/**
	 * форматирует ответ для метода doStart
	 */
	public static function doStart(array $app_config, string $lang, int $server_time, int $time_zone, string $ws_token, string $ws_url, string $billing_url,
						 array $notification_preferences, array $call_preferences, array $userbot_preferences, string $client_connection_token, string $captcha_public_key):array {

		return [
			"server_time"              => (int) $server_time,
			"time_zone"                => (int) $time_zone,
			"app_config"               => (object) $app_config,
			"lang"                     => (string) $lang,
			"ws_token"                 => (string) $ws_token,
			"ws_url"                   => (string) $ws_url,
			"billing_url"              => (string) $billing_url,
			"notification_preferences" => (object) $notification_preferences,
			"call_preferences"         => (object) $call_preferences,
			"userbot_preferences"      => (object) $userbot_preferences,
			"partner_url"              => (string) PARTNER_URL,
			"client_connection_token"  => (string) $client_connection_token,
			"captcha_public_key"       => (string) $captcha_public_key,
		];
	}

	/**
	 * форматируем список компаний юзера
	 *
	 * @param array $company_list
	 * @param int   $min_order
	 *
	 * @return array
	 */
	public static function userCompanyList(array $company_list, int $min_order):array {

		$output = [];
		foreach ($company_list as $company) {
			$output[] = self::formatUserCompany($company);
		}

		return [
			"company_list" => (array) $output,
			"min_order"    => (int) $min_order,
		];
	}

	/**
	 * Форматируем список компаний
	 *
	 * @return array
	 */
	public static function companyList(array $company_list):array {

		$output = [];
		foreach ($company_list as $company) {
			$output[] = self::formatUserCompany($company);
		}

		return [
			"company_list" => (array) $output,
		];
	}

	/**
	 * форматируем компанию юзера
	 *
	 * @param Struct_User_Company $company
	 *
	 * @return array
	 */
	public static function userCompany(Struct_User_Company $company):array {

		return [
			"company" => self::formatUserCompany($company),
		];
	}

	/**
	 * форматируем компании юзера
	 *
	 * @param Struct_User_Company $company
	 *
	 * @return array
	 */
	public static function formatUserCompany(Struct_User_Company $company):array {

		return [
			"company_id"        => (int) $company->company_id,
			"client_company_id" => (string) $company->client_company_id,
			"name"              => (string) $company->name,
			"avatar_color_id"   => (int) $company->avatar_color_id,
			"status"            => (string) $company->status,
			"order"             => (int) $company->order,
			"creator_user_id"   => (int) $company->created_by_user_id,
			"member_count"      => (int) $company->member_count,
			"guest_count"       => (int) $company->guest_count,
			"url"               => (string) $company->url,
			"created_at"        => (int) $company->created_at,
			"updated_at"        => (int) $company->updated_at,
			"avatar_file_key"   => (string) isEmptyString($company->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($company->avatar_file_map),
			"data"              => (array) self::_getUserCompanyData($company),
		];
	}

	/**
	 * получаем дополнительные данные для компании
	 * @long - большая валидация data
	 */
	protected static function _getUserCompanyData(Struct_User_Company $company):array {

		$data = [
			"inviter_user_id"          => 0,
			"inviter_full_name"        => "",
			"inviter_avatar_file_key"  => "",
			"inviter_avatar_color"     => "",
			"approved_user_id"         => 0,
			"approved_full_name"       => "",
			"approved_avatar_file_key" => "",
			"approved_avatar_color"    => "",
		];

		if (isset($company->data["inviter_user_id"])) {
			$data["inviter_user_id"] = (int) $company->data["inviter_user_id"];
		}
		if (isset($company->data["inviter_full_name"])) {
			$data["inviter_full_name"] = (string) $company->data["inviter_full_name"];
		}
		if (isset($company->data["inviter_avatar_file_key"])) {
			$data["inviter_avatar_file_key"] = (string) $company->data["inviter_avatar_file_key"];
		}
		if (isset($company->data["inviter_avatar_color"])) {
			$data["inviter_avatar_color"] = (string) $company->data["inviter_avatar_color"];
		}
		if (isset($company->data["approved_user_id"])) {
			$data["approved_user_id"] = (int) $company->data["approved_user_id"];
		}
		if (isset($company->data["approved_full_name"])) {
			$data["approved_full_name"] = (string) $company->data["approved_full_name"];
		}
		if (isset($company->data["approved_avatar_file_key"])) {
			$data["approved_avatar_file_key"] = (string) $company->data["approved_avatar_file_key"];
		}
		if (isset($company->data["approved_avatar_color"])) {
			$data["approved_avatar_color"] = (string) $company->data["approved_avatar_color"];
		}
		return $data;
	}

	/**
	 * форматируем публичные документы
	 *
	 * @param array $public_documents
	 *
	 * @return array
	 */
	public static function publicDocuments(array $public_documents):array {

		$output = [];
		foreach ($public_documents as $document) {
			$output[] = self::_formatPublicDocument($document);
		}

		return $output;
	}

	/**
	 * форматируем ответ для начавшейся смены номера телефона
	 *
	 * @param Domain_User_Entity_ChangePhone_Story    $story
	 * @param Domain_User_Entity_ChangePhone_SmsStory $sms_story
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function changePhoneProcessStage1(
		Domain_User_Entity_ChangePhone_Story $story,
		Domain_User_Entity_ChangePhone_SmsStory $sms_story
	):array {

		return [
			"change_phone_story_map" => (string) $story->getStoryMap(),
			"next_resend"            => (int) $sms_story->getNextResend(),
			"available_attempts"     => (int) $sms_story->getAvailableAttempts(),
			"expire_at"              => (int) $story->getExpiresAt(),
		];
	}

	/**
	 * форматируем ответ для второго этапа смены номера
	 *
	 * @param Domain_User_Entity_ChangePhone_SmsStory $sms_story
	 *
	 * @return array
	 */
	public static function changePhoneProcessStage2(
		Domain_User_Entity_ChangePhone_SmsStory $sms_story
	):array {

		return [
			"next_resend"        => (int) $sms_story->getNextResend(),
			"available_attempts" => (int) $sms_story->getAvailableAttempts(),
		];
	}

	/**
	 * форматируем ответ для переотправки смс при смене номера
	 *
	 * @param Domain_User_Entity_ChangePhone_SmsStory $sms_story
	 *
	 * @return array
	 */
	public static function changePhoneResendSms(
		Domain_User_Entity_ChangePhone_SmsStory $sms_story
	):array {

		return [
			"next_resend" => (int) $sms_story->getNextResend(),
		];
	}

	/**
	 * форматируем ответ для данных о номере телефона
	 *
	 * @param \BaseFrame\System\PhoneNumber $phone_number_obj
	 *
	 * @return array
	 */
	public static function phoneNumberData(\BaseFrame\System\PhoneNumber $phone_number_obj):array {

		return [
			"country_code" => (string) $phone_number_obj->countryPrefix(false),
			"last_digits"  => (string) $phone_number_obj->lastDigits(),
			"phone_mask"   => (string) $phone_number_obj->obfuscate(),
		];
	}

	/**
	 * приводим документ к формату
	 *
	 * @param Struct_Config_Lang_Document $public_document
	 *
	 * @return array
	 */
	protected static function _formatPublicDocument(Struct_Config_Lang_Document $public_document):array {

		return [
			"name"        => $public_document->name,
			"title"       => $public_document->title,
			"description" => $public_document->description,
			"file_key"    => $public_document->file_key,
			"file_url"    => $public_document->file_url,
		];
	}

	/**
	 * форматируем статус пользователя в компании
	 *
	 * @throws \parseException
	 */
	public static function formatUserCompanyStatus(int $user_status):string {

		if (!isset(self::_USER_COMPANY_STATUS_SCHEMA[$user_status])) {
			throw new ParseFatalException("unknown user_status = {$user_status}");
		}

		return self::_USER_COMPANY_STATUS_SCHEMA[$user_status];
	}

	/**
	 * формируем ответ для служебных данных клиента
	 */
	public static function formatStartData(array $start_data):array {

		$output = [];

		// отдаем конфиг эмоджи если есть
		$output["emoji_keywords_list"] = isset($start_data["emoji_keywords_list"]) ? (array) $start_data["emoji_keywords_list"] : [];

		// отдаем видео-онбординг для чата наймы и увольнения
		$output["hiring_conversation_welcome_video"] = isset($start_data["hiring_conversation_welcome_video"])
			? (array) $start_data["hiring_conversation_welcome_video"] : [];

		// отдаем видео-онбординг для главного чата
		$output["general_conversation_welcome_video"] = isset($start_data["general_conversation_welcome_video"])
			? (array) $start_data["general_conversation_welcome_video"] : [];

		// отдаем видео-онбординг для тредов
		$output["threads_welcome_video"] = isset($start_data["threads_welcome_video"])
			? (array) $start_data["threads_welcome_video"] : [];

		// отдаем конфиг приложения
		$output["app_config"] = isset($start_data["app_config"])
			? (array) $start_data["app_config"] : [];

		return $output;
	}
}

