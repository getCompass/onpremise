<?php

namespace Compass\FileBalancer;

/**
 * Класс для поддержки legacy-кода
 *
 * Работает через заголовки, чтобы удобно рулить все в одном месте
 * И писать статистику по любому случаю legacy
 *
 * Для каждого случая:
 * 1. Создать таск в проекте учета API и прекрепить к таску labels - учет_legacy
 * 2. Все описать в API
 * 3. Дать задачи клиентам, выставить dependencies
 * 4. Прикрепить ссылку на таск в функции этого класса
 * 5. Во всех развилках использовать туду и ссылку на таск
 * 6. !!! Добавить статистику на старый и новый случай
 */
class Type_System_Legacy {

	/**
	 * метод getInfoForCrop версии v2
	 *
	 * @return bool
	 */
	public static function isGetInfoForCropV2():bool {

		$value = getHeader("HTTP_X_COMPASS_GET_INFO_FOR_CROP_V2");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}
		return false;
	}
}