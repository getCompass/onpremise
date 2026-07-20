<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use Compass\Pivot\Domain_System_Entity_Locale_Integration_Description as Description;

/**
 * Получить описание под локализацию для API ключей
 */
class Domain_Apikey_Action_GetDescriptionLocalizationKeys
{
	/**
	 * Выполняем
	 *
	 * @param Struct_User_ApikeyTemplate[] $apikey_template_list
	 *
	 * @throws ParseFatalException
	 */
	public static function do(array $apikey_template_list): array
	{

		$description_locale_list = [];
		foreach ($apikey_template_list as $template) {

			$description_locale = new Description($template->uniq_name);

			$description_locale_list[$template->template_id] = $description_locale->getLocaleResult();
		}

		return $description_locale_list;
	}
}
