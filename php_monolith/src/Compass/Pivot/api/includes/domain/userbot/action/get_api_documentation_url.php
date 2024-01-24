<?php

namespace Compass\Pivot;

/**
 * действие получения url для api-документатора для бота
 */
class Domain_Userbot_Action_GetApiDocumentationUrl {

	/**
	 * выполняем
	 */
	public static function do():string {

		return Domain_Userbot_Entity_Userbot::API_DOCUMENTATION_URL;
	}
}