<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс, который содержит логику по выборку провайдера для отправки новых смс
 */
class Type_Sms_Provider_Choose {

	/**
	 * Выбираем подходящего провайдера для отправки смс
	 *
	 * @return string|Gateway_Sms_Provider_Abstract
	 * @mixed
	 */
	public static function doAction(string $phone_number, array $excluded_provider_id_list = []) {

		// выбираем список тех провайдеров, которые умеет работать с переданным номером телефона
		$provider_id_list = self::_getProviderIdListByPhoneNumber($phone_number);

		// выбираем из пула провайдеров тех, которые доступны в данный момент
		$available_provider_list = self::_getAvailableByList($provider_id_list);

		// исключаем провайдеры, которые нужно убрать из выборки
		$filtered_provider_list = array_diff_key($available_provider_list, array_flip($excluded_provider_id_list));

		// если после исключения провайдеров больше вариантов не осталось
		// то используем, то что имели до исключения
		if (count($filtered_provider_list) == 0) {
			$filtered_provider_list = $available_provider_list;
		}

		// выбираем из списка провайдеров тех, которые шлют смс с повышенным приоритетом (если такие есть) на переданный номер телефона
		// если таких нет, то в массив запишется $available_provider_list
		$finally_provider_list = self::_filterByHighPriorityIfExist($phone_number, $filtered_provider_list);

		// если доступных провайдеров нет, то ругаемся
		if (count($finally_provider_list) == 0) {
			throw new cs_SmsNoAvailableProviders();
		}

		// случайно выбираем провайдера с учетом весов каждого из них
		$provider = self::_getRandomProvider($finally_provider_list);

		if (!isset(Type_Sms_Provider::ASSOC_GATEWAY_CLASS_BY_ID[$provider->provider_id])) {
			throw new ParseFatalException(__METHOD__ . ": class handler not defined for provider: " . $provider->provider_id);
		}

		return Type_Sms_Provider::ASSOC_GATEWAY_CLASS_BY_ID[$provider->provider_id];
	}

	/**
	 * выбираем список тех провайдеров, которые умеет работать с переданным номером телефона
	 *
	 */
	protected static function _getProviderIdListByPhoneNumber(string $phone_number):array {

		// получаем ассоциативный массив с соотношением $phone_code => [$provider_id, $provider_id] из конфига смс провайдеров
		$assoc_provider_list_by_phone_code = Type_Sms_Config::convertToAssocByPhoneCode();

		// ищем совпадения по коду номера телефона
		$matched_phone_code_list = self::_getMatchedPhoneCodeListByPhoneNumber($phone_number, array_keys($assoc_provider_list_by_phone_code));

		// для каждого совпадения получаем provider_id и складываем в массив для ответа
		$output = [];
		foreach ($matched_phone_code_list as $matched_phone_code) {
			$output = array_merge($output, $assoc_provider_list_by_phone_code[$matched_phone_code]);
		}

		return $output;
	}

	/**
	 * Находим те коды номеров телефона, которые совпадают с переданным номером
	 *
	 */
	protected static function _getMatchedPhoneCodeListByPhoneNumber(string $phone_number, array $phone_code_list):array {

		$output = [];
		foreach ($phone_code_list as $phone_code) {

			// если код номера телефона не в начале, то бежим дальше
			if (strpos($phone_number, $phone_code) !== 0) {
				continue;
			}

			$output[] = $phone_code;
		}

		return $output;
	}

	/**
	 * Получить список доступных провайдеров из списка провайдеров
	 *
	 * @return Struct_PivotSmsService_Provider[]
	 */
	protected static function _getAvailableByList(array $provider_id_list):array {

		$provider_list = Gateway_Db_PivotSmsService_ProviderList::getListById($provider_id_list);

		$output = [];
		foreach ($provider_list as $provider) {

			if ($provider->is_available == 0) {
				continue;
			}

			$output[$provider->provider_id] = $provider;
		}

		return $output;
	}

	/**
	 * выбираем из списка провайдеров тех, которые шлют смс с повышенным приоритетом (если такие есть) на переданный номер телефона
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _filterByHighPriorityIfExist(string $phone_number, array $provider_list):array {

		// проверяем наличие провайдеров с высоким приоритетом для данного номера
		$high_priority_provider_list = [];
		foreach ($provider_list as $provider_id => $provider_info) {

			// получаем массив кодов сотовых операторов, которые провайдер обслуживает с повышенным приоритетом
			$high_priority_phone_code_list = Type_Sms_Config::getHighPriorityPhoneCodeList($provider_id);

			// бежим по каждому такому коду и ищем совпадения
			foreach ($high_priority_phone_code_list as $high_priority_phone_code) {

				// если код номера телефона не в начале, то бежим дальше
				if (strpos($phone_number, $high_priority_phone_code) !== 0) {
					continue;
				}

				$high_priority_provider_list[$provider_id] = $provider_info;
			}
		}

		// если не нашли провайдеры с высоким приоритетом, то возвращаем все ранее определенные провайдеры
		if (count($high_priority_provider_list) < 1) {
			return $provider_list;
		}

		// иначе возвращаем провайдеры с высоким приоритетом
		return $high_priority_provider_list;
	}

	/**
	 * Случайно выбирает провайдера с учетом размера весов каждого из них
	 *
	 * @param Struct_PivotSmsService_Provider[] $provider_list [$provider_id => Struct_PivotSmsService_Provider]
	 *
	 */
	protected static function _getRandomProvider(array $provider_list):Struct_PivotSmsService_Provider {

		// получаем веса каждого провайдера
		$weight_values_by_provider_id = [];
		foreach ($provider_list as $provider) {
			$weight_values_by_provider_id[$provider->provider_id] = Type_Sms_Provider_Extra::getRelevantGrade($provider->extra);
		}

		// пытаемся достать случайного с учетом весов провайдеров
		$rand = mt_rand(1, (int) array_sum($weight_values_by_provider_id));
		foreach ($weight_values_by_provider_id as $provider_id => $relevant_grade) {

			$rand -= $relevant_grade;
			if ($rand <= 0) {
				return $provider_list[$provider_id];
			}
		}

		// иначе отдаем случайного провайдера
		return $provider_list[mt_rand(0, count($provider_list) - 1)];
	}
}