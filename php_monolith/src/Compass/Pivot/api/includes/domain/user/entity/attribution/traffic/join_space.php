<?php

namespace Compass\Pivot;

/**
 * Класс описывает работу атрибуции для трафика сотрудников/участников команды – такие пользователи приходят с ссылок-приглашений /join/
 */
class Domain_User_Entity_Attribution_Traffic_JoinSpace extends Domain_User_Entity_Attribution_Traffic_Abstract {

	/** @var string тип трафика */
	protected const _TRAFFIC_TYPE = "join_space";

	/** @var array результаты сравнения параметров регистрации и посещений /join/ страниц – для аналитики */
	protected array $_visit_parameters_comparing_result_map = [];

	/**
	 * выбираем самое подходящее посещение
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function chooseMatchedVisit():?Struct_Db_PivotAttribution_LandingVisit {

		// здесь храним наиболее совпавшее посещение и процент его совпадения
		$most_matched_visit      = null;
		$most_matched_percentage = 0;

		// пробегаемся по каждому посещению
		foreach ($this->_traffic_filtered_visit_list as $visit) {

			// если по времени не подходит
			if ($visit->visited_at < $this->_user_app_registration->registered_at - ATTRIBUTION_JOIN_SPACE_VISITS_MATCHING_PERIOD) {
				continue;
			}

			// определяем совпадение
			$matching_result = $this->_comparator->countMatchingPercent($this->_user_app_registration, $visit);

			// сохраняем результаты сравнения
			$this->_visit_parameters_comparing_result_map[$visit->visit_id] = $matching_result->parameter_comparing_result_list;

			// если ранее не было выбрано 100% совпадение (чтобы не перезаписывать ранее совпавшее на 100% приглашение)
			// и результат сравнения лучше чем у прошлых
			if ($most_matched_percentage !== 100 && $matching_result->matched_percentage > $most_matched_percentage) {

				$most_matched_percentage = $matching_result->matched_percentage;
				$most_matched_visit      = $visit;
			}
		}

		// определяем какое действие выполнит клиент
		$this->_client_action = $this->_resolveClientAction($most_matched_percentage, $most_matched_visit);

		// возвращаем совпавшее посещение только при 100%
		return $most_matched_percentage === 100 ? $most_matched_visit : null;
	}

	/**
	 * Определяем какое действие выполнит клиент
	 *
	 * @return int
	 */
	protected function _resolveClientAction(int $most_matched_percentage, null|Struct_Db_PivotAttribution_LandingVisit $most_matched_visit):int {

		// если результат совпадения – 100%
		if ($most_matched_percentage === 100) {
			return self::CLIENT_ACTION_OPEN_JOIN_LINK;
		}

		// проверим самое точное совпадение на кейс с открытием ввода ссылки приглашения
		if (!is_null($most_matched_visit) && self::_isOpenEnteringLinkCase($most_matched_visit)) {
			return self::CLIENT_ACTION_OPEN_ENTERING_LINK;
		}

		// иначе % совпадений мал
		return self::CLIENT_ACTION_OPEN_DASHBOARD;
	}

	/**
	 * Если это кейс открытия окна ввода ссылки-приглашения
	 *
	 * @return bool
	 */
	protected function _isOpenEnteringLinkCase(Struct_Db_PivotAttribution_LandingVisit $matched_visit):bool {

		// определяем совпадение
		$matching_result = $this->_comparator->countMatchingPercent($this->_user_app_registration, $matched_visit);

		// совпал ли ip-адрес
		$ip_is_equal = false;

		// количество совпавших параметров за исключением ip-адреса
		$matched_parameter_count = 0;

		// пробегаемся по всем сравниваемым параметрам
		foreach ($matching_result->parameter_comparing_result_list as $parameter_comparing_result) {

			// если параметр не совпал, то пропускаем
			if (!$parameter_comparing_result->is_equal) {
				continue;
			}

			// если это ip-адрес
			if ($parameter_comparing_result->parameter_name === Domain_User_Entity_Attribution_Comparator_Abstract::PARAMETER_IP_ADDRESS) {
				$ip_is_equal = true;
			} else {

				// иначе считаем кол-во совпавших параметров
				$matched_parameter_count++;
			}
		}

		// если совпал ip-адрес и 4 других параметра
		return $ip_is_equal && $matched_parameter_count >= 4;
	}

	/**
	 * Получаем результаты сравнения параметров регистрации и посещений /join/ страниц – для аналитики
	 *
	 * @return array
	 */
	protected function _getParametersComparingResult():array {

		return $this->_visit_parameters_comparing_result_map;
	}
}