<?php

namespace Compass\Pivot;

/**
 * Получить шаблоны интеграций для создания API ключей
 */
class Domain_Apikey_Action_GetTemplateList
{

	/**
	 * Выполняем
	 * 
	 * @return Struct_User_ApikeyTemplate[]
	 */
	public static function do(): array
	{

		return Gateway_Bus_Auth::getTemplateList();
	}
}
