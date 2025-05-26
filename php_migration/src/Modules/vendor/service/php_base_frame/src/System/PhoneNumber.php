<?php

namespace BaseFrame\System;

use BaseFrame\Conf\Country;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;

/**
 * Класс для работы с телефонными номерами
 */
class PhoneNumber {

	private const _MIN_NUMBER_LENGTH = 6; // минимальный размер номера телефона (без плюса)
	private const _MAX_NUMBER_LENGTH = 14; // максимальный размер номера телефона (без плюса)

	private string $_number; // значение номера телефона
	private string $_country_prefix; // префикс страны
	private string $_country_code; // код страны в ISO-639-1

	/**
	 * Конструктор
	 *
	 * @param string $phone_number
	 *
	 * @throws InvalidPhoneNumber
	 */
	public function __construct(string $phone_number) {

		$this->_number = $this->_validate($phone_number);
	}

	/**
	 * Валидировать номер телефона
	 *
	 * @param string $phone_number
	 *
	 * @return string
	 * @throws InvalidPhoneNumber
	 */
	private function _validate(string $phone_number):string {

		// нормализуем номер телефона
		$phone_number = $this->_normalize($phone_number);

		// проверяем, что это что-то похожее на телефонный номер
		if (!preg_match("/^[+][0-9]{" . self::_MIN_NUMBER_LENGTH . "," . self::_MAX_NUMBER_LENGTH . "}\z/", $phone_number)) {
			throw new InvalidPhoneNumber("invalid international phone number");
		}

		return $phone_number;
	}

	/**
	 * Приводим номер в нормальный стандартизированный вид
	 *
	 * @param string $phone_number
	 *
	 * @return string
	 */
	private function _normalize(string $phone_number):string {

		// если номер начинается на 89, то заменяем его на +79
		if (str_starts_with($phone_number, "89")) {
			$phone_number = substr_replace($phone_number, "+79", 0, 2);
		}

		// если номер не начинается с "+", то добавляем
		if (!str_starts_with($phone_number, "+")) {
			$phone_number = "+" . $phone_number;
		}

		return str_replace([" ", ".", "-", "(", ")"], "", $phone_number);
	}

	/**
	 * Вернуть нормализованный номер телефона
	 *
	 * @return string
	 */
	public function number():string {

		return $this->_number;
	}

	/**
	 * Получить префикс страны
	 *
	 * @param bool $with_plus_sign
	 *
	 * @return string
	 */
	public function countryPrefix(bool $with_plus_sign = true):string {

		if (!isset($this->_country_prefix)) {
			$this->_getCountryCode();
		}

		return $with_plus_sign ? $this->_country_prefix : str_replace("+", "", $this->_country_prefix);
	}

	/**
	 * Получить код страны в ISO-639-1
	 *
	 * @return string
	 */
	public function countryCode():string {

		if (!isset($this->_country_code)) {
			$this->_getCountryCode();
		}

		return $this->_country_code;
	}

	/**
	 * Получить код страны по номеру телефона
	 *
	 * @return void
	 */
	private function _getCountryCode():void {

		// загружаем конфиг со странами
		$country_config = Country::load();

		// варианты: country_code => <кол-во совпавших символов>
		$variant_list = [];
		foreach ($country_config as $country_code => $config_item) {
			$variant_list = array_merge($variant_list, $this->_findPhonePrefix($country_code, $config_item));
		}

		if ($variant_list === []) {

			$this->_country_prefix = "";
			$this->_country_code   = "";

			return;
		}

		// возвращаем вариант с самым большим совпадением
		uasort($variant_list, static function(array $a, array $b) {

			return $b["length"] <=> $a["length"];
		});

		$this->_country_prefix = current($variant_list)["phone_prefix"];
		$this->_country_code   = key($variant_list);
	}

	/**
	 * Составляем список совпадений с длиной совпадения
	 *
	 * @param string $country_code
	 * @param array  $config_item
	 *
	 * @return array
	 */
	private function _findPhonePrefix(string $country_code, array $config_item):array {

		$variant_list = [];

		foreach ($config_item["phone_prefix_list"] as $phone_prefix) {

			// записываем в варианты
			if (str_starts_with($this->_number, $phone_prefix)) {

				$variant_list[$country_code] = [
					"length"       => mb_strlen($phone_prefix),
					"phone_prefix" => $phone_prefix,
				];
			}
		}

		return $variant_list;
	}

	/**
	 * Обфусцировать номер
	 *
	 * @return string
	 */
	public function obfuscate():string {

		return substr($this->_number, 0, 3) . "*******" . substr($this->_number, -2);
	}

	/**
	 * Вернуть последние цифры номера
	 * @return string
	 */
	public function lastDigits():string {

		return mb_substr($this->_number, -2);
	}
}