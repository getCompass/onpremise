<?php

/**
 * класс для валидации данных
 */
class Entity_Validator {

	/**
	 * валидация имени профиля
	 *
	 * @param string $name
	 *
	 * @throws cs_InvalidProfileName
	 */
	public static function assertValidProfileName(string $name):void {

		if ($name === "") {
			throw new cs_InvalidProfileName();
		}
	}

	/**
	 * Получаем код страны по номеру телефона
	 *
	 * @param string $phone_number
	 *
	 * @return int
	 * @throws cs_InvalidPhoneNumber
	 */
	public static function getCountryCode(string $phone_number):int {

		$country_codes_config = getSystemConfig("FLAG_COUNTRY_LIST");
		$country_codes        = array_column($country_codes_config, "phone_code");

		$current_code = null;
		foreach ($country_codes as $country_code) {

			if (str_contains($phone_number, $country_code)) {

				$current_code = $country_code;
			}
		}

		if ($current_code === null) {
			throw new cs_InvalidPhoneNumber();
		}
		return $current_code;
	}

}
