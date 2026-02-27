<?php

namespace Compass\Pivot;

/**
 * задача класса работать с конфигом каталога приложений
 */
class Domain_SmartApp_Entity_SuggestedCatalog
{
	public const CATEGORY_POPULAR            = "popular";
	public const CATEGORY_OFFICE_APPS        = "office_apps";
	public const CATEGORY_MAIL               = "mail";
	public const CATEGORY_CALENDARS          = "calendars";
	public const CATEGORY_TEAM_COLLABORATION = "team_collaboration";
	public const CATEGORY_CRM_SYSTEMS        = "crm_systems";
	public const CATEGORY_VIDEO_CONFERENCING = "video_conferencing";
	public const CATEGORY_MESSENGERS         = "messengers";
	public const CATEGORY_DEVELOPMENT        = "development";
	public const CATEGORY_FILE_STORAGE       = "file_storage";
	public const CATEGORY_AI_SERVICES        = "ai_services";
	public const CATEGORY_ACCOUNTING_AND_EDO = "accounting_and_edo";
	public const CATEGORY_HR_SERVICES        = "hr_services";
	public const CATEGORY_ANALYTICS          = "analytics";
	public const CATEGORY_OTHER              = "other";

	/**
	 * Получаем весь конфиг
	 */
	public static function getSuggestedCatalog(): array
	{

		return self::_getCatalogConfig();
	}

	/**
	 * Получаем урезанный конфиг для getStartData
	 */
	public static function getStartDataSuggestedCatalog(): array
	{

		$smart_app_suggested_list = self::_getCatalogConfig();

		$output = [];
		foreach ($smart_app_suggested_list as $item) {

			$suggested_item = Struct_Domain_SmartApp_SuggestedItem::rowToStruct($item);
			$output[]       = [
				"catalog_item_id"           => $suggested_item->catalog_item_id,
				"is_need_custom_user_agent" => $suggested_item->is_need_custom_user_agent,
			];
		}

		return $output;
	}

	/**
	 * Получаем весь конфиг
	 */
	public static function getCategoryLocalization(): array
	{

		return self::_getLocalizationConfig();
	}

	/**
	 * Получаем дефолтную аватарку для приложения
	 */
	public static function getDefaultSmartAppAvatar(): string
	{

		try {
			$default_file = Gateway_Db_PivotSystem_DefaultFileList::get("smart_app_default_avatar");
		} catch (\cs_RowIsEmpty) {
			return "";
		}

		return $default_file->file_key;
	}

	/**
	 * Получаем аватарку для приложения из каталога
	 */
	public static function getCatalogSmartAppAvatar(int $catalog_item_id): string
	{

		try {
			$default_file = Gateway_Db_PivotSystem_DefaultFileList::get("smart_app_avatar_{$catalog_item_id}");
		} catch (\cs_RowIsEmpty) {

			// если не нашли аватар для приложения из каталога - отдаем дефолтный
			return self::getDefaultSmartAppAvatar();
		}

		return $default_file->file_key;
	}

	/**
	 * Получить содержимое конфиг-файла
	 */
	protected static function _getCatalogConfig(): array
	{

		$key = self::class . "_catalog";

		// поскольку содержимое конфиг-файла не может поменяться нагорячую
		// то ничего не мешает положить его в глобальную переменную
		if (isset($GLOBALS[$key])) {
			return $GLOBALS[$key];
		}

		$GLOBALS[$key] = getConfig("SMARTAPPS_SUGGESTED_CATALOG");
		return $GLOBALS[$key];
	}

	/**
	 * Получить содержимое конфиг-файла
	 */
	protected static function _getLocalizationConfig(): array
	{

		$key = self::class . "_category_localization";

		// поскольку содержимое конфиг-файла не может поменяться нагорячую
		// то ничего не мешает положить его в глобальную переменную
		if (isset($GLOBALS[$key])) {
			return $GLOBALS[$key];
		}

		$GLOBALS[$key] = getConfig("SMARTAPPS_CATEGORY_LOCALIZATION");
		return $GLOBALS[$key];
	}
}
