<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Action для получения служебных данных для клиента V2
 */
class Domain_System_Action_GetStartDataV2 {

	// клиент по принадлежности к устройству
	protected const _CLIENT_MOBILE  = "mobile";
	protected const _CLIENT_DESKTOP = "desktop";

	// ключи видеоонбордингов
	protected const _ONBOARDING_VIDEO_KEY_LIST = [
		"general_conversation_welcome_video",
		"threads_welcome_video",
	];

	/**
	 * Получаем необходимые конфиги
	 *
	 * @throws Domain_System_Exception_EmojiKeywordsNotFound
	 * @throws Domain_System_Exception_VideoOnboardingNotFound
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\LocaleNotFound
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_IncorrectVersion
	 * @throws cs_PlatformNotFound
	 */
	public static function do(int $user_id, array $need_data_list):array {

		// проверяем, что в параметр need_data_list передана корректная структура
		self::_assertNeedDataListStructure($need_data_list);

		// готовим ответ
		$output_data = [];

		// получаем конфиг с эмоджи если необходимо
		$output_data = self::_getEmojiKeywordsIfNeed($need_data_list, $output_data);

		// получаем конфиг для видеоонбордингов
		$output_data = self::_getOnboardingVideosListIfNeed($need_data_list, $output_data);

		// получаем конфиг с константами
		$output_data = self::_getAppConfigListIfNeed($need_data_list, $user_id, $output_data);

		// получаем список доступных способов аутентификации
		$output_data["available_auth_method_list"]       = Domain_User_Entity_Auth_Config::getAvailableMethodList();
		$output_data["available_auth_guest_method_list"] = Domain_User_Entity_Auth_Config::getAvailableGuestMethodList();

		// получаем тип сервера
		$output_data["server_type"] = Domain_System_Entity_ServerType::getServerType();

		// получаем список приложений каталога
		$output_data["smart_app_short_suggested_list"] = Domain_SmartApp_Entity_SuggestedCatalog::getStartDataSuggestedCatalog();

		// получаем конфиг с фичами для приложения
		return self::_getFeatureListIfNeed($need_data_list, $user_id, $output_data);
	}

	/**
	 * Проверяем, что в параметр need_data_list передана корректная структура
	 */
	protected static function _assertNeedDataListStructure(array $need_data_list):void {

		// проверяем, что внутри need_data_list каждый элемент – это массив
		foreach ($need_data_list as $need_data) {

			// не массив – ругаемся
			if (!is_array($need_data)) {
				throw new \BaseFrame\Exception\Domain\LocaleNotFound("incorrect parameter value");
			}

			// пробегаемся по каждому запрошенному набору данных и проверяем, что передали локаль
			foreach ($need_data as $config_item) {

				// если передали хоть где то левак - возвращаем ошибку
				if (!isset($config_item["locale"]) || !is_string($config_item["locale"])) {
					throw new \BaseFrame\Exception\Domain\LocaleNotFound("locale not found");
				}
			}
		}
	}

	/**
	 * Получаем конфиг для эмоджи, если запрашивали
	 *
	 * @throws \BaseFrame\Exception\Domain\LocaleNotFound
	 * @throws cs_IncorrectVersion
	 * @throws Domain_System_Exception_EmojiKeywordsNotFound
	 * @throws ParamException
	 */
	protected static function _getEmojiKeywordsIfNeed(array $need_data_list, array $output_data):array {

		// если был конфиг в запросе
		if (isset($need_data_list["emoji_keywords_list"])) {

			if (!is_array($need_data_list["emoji_keywords_list"])) {
				throw new ParamException("passed bad param emoji_keywords_list");
			}

			// пишем конфиг
			$output_data = self::_tryGetKeywordsByLocale($output_data, $need_data_list["emoji_keywords_list"]);
		}

		return $output_data;
	}

	/**
	 * Получаем список ключевых слов если был
	 *
	 * @param array $output_data
	 * @param array $emoji_keywords_list
	 *
	 * @return array
	 * @throws Domain_System_Exception_EmojiKeywordsNotFound
	 * @throws \BaseFrame\Exception\Domain\LocaleNotFound
	 * @throws cs_IncorrectVersion
	 */
	protected static function _tryGetKeywordsByLocale(array $output_data, array $emoji_keywords_list):array {

		$emoji_keywords_config_list = [];

		// проходимся по всем запрощенным конфигам
		foreach ($emoji_keywords_list as $config) {

			$locale = (string) $config["locale"];

			\BaseFrame\System\Locale::assertAllowedLocale($locale);

			// если версию не передали - отдаем текущую версию эмодзи от сервера
			if (!isset($config["version"])) {
				$config["version"] = \Compass\Pivot\EMOJI_VERSION_LIST[$locale];
			}

			$lang    = \BaseFrame\System\Locale::getLang($locale);
			$version = (int) $config["version"];

			Domain_System_Entity_Validator::assertIncorrectVersion($version);

			$emoji_keywords_config_list["emoji_keywords_{$lang}_{$version}"] = [
				"locale"  => $locale,
				"version" => $version,
			];
		}

		// получаем ключи файлов
		$emoji_keywords_conf_json_list = Gateway_Db_PivotSystem_DefaultFileList::getList(array_keys($emoji_keywords_config_list));

		// если ничего не нашли - возвращаем ошибку
		if (count($emoji_keywords_conf_json_list) < 1) {
			throw new Domain_System_Exception_EmojiKeywordsNotFound("emoji keywords not found");
		}
		return self::_formatEmojiKeywordsList($output_data, $emoji_keywords_config_list, $emoji_keywords_conf_json_list);
	}

	/**
	 * Форматируем ответ с эмодзи
	 *
	 * @param array $output_data
	 * @param array $emoji_keywords_config_list
	 * @param array $emoji_keywords_conf_json_list
	 *
	 * @return array
	 * @throws Domain_System_Exception_EmojiKeywordsNotFound
	 */
	protected static function _formatEmojiKeywordsList(array $output_data, array $emoji_keywords_config_list, array $emoji_keywords_conf_json_list):array {

		foreach ($emoji_keywords_config_list as $key => $emoji_keywords_config) {

			if (!isset($emoji_keywords_conf_json_list[$key])) {
				throw new Domain_System_Exception_EmojiKeywordsNotFound("emoji keywords not found for $key");
			}

			// пишем ответ
			$emoji_item = [
				"locale"   => (string) $emoji_keywords_config["locale"],
				"version"  => (int) $emoji_keywords_config["version"],
				"file_key" => (string) $emoji_keywords_conf_json_list[$key]->file_key,
			];

			$output_data["emoji_keywords_list"][] = $emoji_item;
		}

		return $output_data;
	}

	/**
	 * Получить список конфигов для видеоонбордингов
	 *
	 * @param array $need_data_list
	 * @param array $output_data
	 *
	 * @return array
	 * @throws Domain_System_Exception_VideoOnboardingNotFound
	 * @throws \BaseFrame\Exception\Domain\LocaleNotFound
	 * @throws cs_PlatformNotFound
	 */
	protected static function _getOnboardingVideosListIfNeed(array $need_data_list, array $output_data):array {

		$processed_config_list = [];

		if (!isset($need_data_list["onboarding_videos_list"])) {
			return $output_data;
		}

		$onboarding_video_list = $need_data_list["onboarding_videos_list"];

		$platform = Type_Api_Platform::getPlatform();

		// в зависимости от платформы и языка получаем записи о файлах
		$client = match ($platform) {
			Type_Api_Platform::PLATFORM_ANDROID, Type_Api_Platform::PLATFORM_IOS, Type_Api_Platform::PLATFORM_IPAD => self::_CLIENT_MOBILE,
			default                                                                                                => self::_CLIENT_DESKTOP,
		};

		// проходимся по всем запрощенным конфигам
		foreach ($onboarding_video_list as $config) {

			$locale = (string) $config["locale"];

			\BaseFrame\System\Locale::assertAllowedLocale($locale);

			// разделения онбоардинга по версиям нет
			$lang    = \BaseFrame\System\Locale::getLang($locale);
			$version = 1;

			// если уже обрабатывали такой конфиг - пропускаем
			if (array_key_exists("{$lang}_{$version}", $processed_config_list)) {
				continue;
			}

			$processed_config_list["{$lang}_{$version}"] = true;

			$output_data["onboarding_videos_list"][] = self::_getOnboardingVideos($locale, $lang, $client, $version);
		}

		return $output_data;
	}

	/**
	 * Получить видео файлы для онбоардинга
	 *
	 * @param string $locale
	 * @param string $lang
	 * @param string $client
	 * @param int    $version
	 *
	 * @return array|false
	 * @throws Domain_System_Exception_VideoOnboardingNotFound
	 */
	protected static function _getOnboardingVideos(string $locale, string $lang, string $client, int $version):array|false {

		$file_keys = [];

		$output_video_list = self::_getOnboardingVideosFromDb(self::_ONBOARDING_VIDEO_KEY_LIST, $lang, $client);

		// если ничего не нашли - возвращаем ошибку
		if (count($output_video_list) < 1) {
			throw new Domain_System_Exception_VideoOnboardingNotFound("video not found");
		}

		// у каждого видео берем file_key для ответа
		foreach ($output_video_list as $key => $video) {
			$file_keys[$key] = $video->file_key;
		}

		return [
			"locale"    => $locale,
			"version"   => $version,
			"file_keys" => $file_keys,
		];
	}

	/**
	 * Получить видеоонбординги из базы данных
	 *
	 * @param array  $dictionary_key_list
	 * @param string $lang
	 * @param string $client
	 *
	 * @return array
	 * @long не разбить получение видео, объемный метод из за дозагрузки видео на дефолтном языке
	 */
	protected static function _getOnboardingVideosFromDb(array $dictionary_key_list, string $lang, string $client):array {

		$default_lang                  = \BaseFrame\System\Locale::getLang(\BaseFrame\System\Locale::LOCALE_RUSSIAN);
		$default_video_list            = [];
		$need_dictionary_key_list      = array_map(static fn(string $el) => $el . "_{$client}_{$lang}", $dictionary_key_list);
		$not_found_dictionary_key_list = [];
		$output_video_list             = [];

		$video_list = Gateway_Db_PivotSystem_DefaultFileList::getList($need_dictionary_key_list);

		foreach ($dictionary_key_list as $dictionary_key) {

			$expected_dictionary_key = $dictionary_key . "_{$client}_{$lang}";

			// если не нашли видео на языке оригинала - записываем ключ, чтобы потом запросить на дефолтном языке
			if (!isset($video_list[$expected_dictionary_key])) {

				$not_found_dictionary_key_list[] = $dictionary_key;
				continue;
			}

			$output_video_list[$dictionary_key] = $video_list[$expected_dictionary_key];
		}

		// если не нашли что-то на языке оригинала - отдаем на дефолтном языке
		if (count($not_found_dictionary_key_list) > 0) {

			$need_dictionary_key_list = array_map(static fn(string $el) => $el . "_{$client}_{$default_lang}", $not_found_dictionary_key_list);
			$default_video_list       = Gateway_Db_PivotSystem_DefaultFileList::getList($need_dictionary_key_list);
		}

		// проверяем, получили ли видео на дефолтном языыке
		foreach ($dictionary_key_list as $dictionary_key) {

			$expected_dictionary_key = $dictionary_key . "_{$client}_{$default_lang}";

			// если видео нашли на дефолтном языке - добавляем в список
			if (isset($default_video_list[$expected_dictionary_key])) {
				$output_video_list[$dictionary_key] = $default_video_list[$expected_dictionary_key];
			}
		}

		return $output_video_list;
	}

	/**
	 * Добавить app_config
	 *
	 * @param array $need_data_list
	 * @param int   $user_id
	 * @param array $output_data
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\LocaleNotFound
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	protected static function _getAppConfigListIfNeed(array $need_data_list, int $user_id, array $output_data):array {

		$processed_config_list = [];

		if (!isset($need_data_list["app_config_list"])) {
			return $output_data;
		}

		$app_config_list = $need_data_list["app_config_list"];

		$platform    = \BaseFrame\System\UserAgent::getPlatform();
		$app_name    = \BaseFrame\System\UserAgent::getAppName();
		$app_version = \BaseFrame\System\UserAgent::getAppVersion();

		// получаем конфиг приложения для пользователя
		$config = Domain_User_Entity_Feature::getAppConfigForUser($platform, $app_name, $user_id, $app_version);

		foreach ($app_config_list as $app_config) {

			$locale = $app_config["locale"];
			\BaseFrame\System\Locale::assertAllowedLocale($locale);

			$lang    = \BaseFrame\System\Locale::getLang($locale);
			$version = 1;

			// если уже обрабатывали такой конфиг - пропускаем
			if (array_key_exists("{$lang}_{$version}", $processed_config_list)) {
				continue;
			}

			$processed_config_list["{$lang}_{$version}"] = true;

			// пишем ответ
			$output_data["app_config_list"][] = self::_formatAppConfigList($locale, $version, $config);
		}

		return $output_data;
	}

	/**
	 * Форматируем ответ с константами приложения
	 *
	 * @param string $locale
	 * @param int    $version
	 * @param array  $config
	 *
	 * @return array
	 */
	protected static function _formatAppConfigList(string $locale, int $version, array $config):array {

		return [
			"locale"  => $locale,
			"version" => $version,
			"config"  => $config,
		];
	}

	/**
	 * Добавить app_config
	 *
	 * @param array $need_data_list
	 * @param int   $user_id
	 * @param array $output_data
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\LocaleNotFound
	 */
	protected static function _getFeatureListIfNeed(array $need_data_list, int $user_id, array $output_data):array {

		$processed_config_list = [];

		if (!isset($need_data_list["feature_list"])) {
			return $output_data;
		}

		$feature_list = $need_data_list["feature_list"];

		$platform    = \BaseFrame\System\UserAgent::getPlatform();
		$app_name    = \BaseFrame\System\UserAgent::getAppName();
		$app_version = \BaseFrame\System\UserAgent::getAppVersion();

		$feature_entity = new Domain_App_Entity_Feature_V2();

		// получаем конфиг приложения для пользователя
		$config = $feature_entity->getConfigForUser($platform, $app_name, $user_id, $app_version);

		foreach ($feature_list as $feature) {

			$locale = $feature["locale"];
			\BaseFrame\System\Locale::assertAllowedLocale($locale);

			$lang    = \BaseFrame\System\Locale::getLang($locale);
			$version = 1;

			// если уже обрабатывали такой конфиг - пропускаем
			if (array_key_exists("{$lang}_{$version}", $processed_config_list)) {
				continue;
			}

			$processed_config_list["{$lang}_{$version}"] = true;

			// пишем ответ
			$output_data["feature_list"][] = self::_formatFeatureList($locale, $version, $config);
		}

		return $output_data;
	}

	/**
	 * Форматируем ответ с константами приложения
	 *
	 * @param string $locale
	 * @param int    $version
	 * @param array  $config
	 *
	 * @return array
	 */
	protected static function _formatFeatureList(string $locale, int $version, array $config):array {

		return [
			"locale"  => $locale,
			"version" => $version,
			"config"  => $config,
		];
	}
}
