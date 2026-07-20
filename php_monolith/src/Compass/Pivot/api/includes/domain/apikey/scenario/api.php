<?php

namespace Compass\Pivot;

/**
 * Сценарии для api
 */
class Domain_Apikey_Scenario_Api
{
	// префикс для дефолтных файлов
	private const _DEFAULT_FILE_PREFIX = "apikey_template_avatar_";

	/**
	 * Получить шаблоны для API ключей
	 */
	public static function getTemplateList(): array
	{

		// получаем сам шаблон из go_auth
		$apikey_template_list = Domain_Apikey_Action_GetTemplateList::do();

		// получаем локализацию для описания apikey
		$description_locale_list = Domain_Apikey_Action_GetDescriptionLocalizationKeys::do($apikey_template_list);

		// получаем ключи аватарок из дефолтных файлов
		$dictionary_key_list = [];
		foreach ($apikey_template_list as $template) {
			$dictionary_key_list[self::_DEFAULT_FILE_PREFIX . $template->template_id] = $template->template_id;
		}

		$default_file_list = Gateway_Db_PivotSystem_DefaultFileList::getList(array_keys($dictionary_key_list));

		$default_file_key_list = [];
		foreach ($default_file_list as $key => $file) {
			$default_file_key_list[$dictionary_key_list[$key]] = $file->file_key;
		}

		return [$apikey_template_list, $default_file_key_list, $description_locale_list];
	}
}
