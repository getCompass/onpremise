<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер, отвечающий за функционал поиска в пространстве.
 */
class Apiv2_Search extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"findLocations",
		"findHits",
	];

	/**
	 * Выполняет поиск подходящих локаций.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Search\Exception\QueryException
	 * @throws ParamException
	 * @long
	 */
	public function findLocations():array {

		$type_list = $this->post(\Formatter::TYPE_ARRAY, "type_list", ["conversation"]);
		$query     = $this->post(\Formatter::TYPE_STRING, "query");
		$limit     = $this->post(\Formatter::TYPE_INT, "limit", 50);
		$offset    = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$limit  = min(200, max($limit, 1));
		$offset = max($offset, 0);

		$type_list = array_unique($type_list);

		try {

			$type_list = Domain_Search_Entity_LocationHandler::convertTypes($type_list);
		} catch (Domain_Search_Exception_IncorrectLocation $e) {
			throw new ParamException($e->getMessage());
		}

		try {

			[$search_result, $has_next] = Domain_Search_Scenario_Api::findLocations(
				$this->user_id, COMPANY_ID, $query, $type_list, $limit, $offset, $this->extra["space"]["is_restricted_access"]
			);
		} catch (Domain_Search_Exception_InvalidQuery|Domain_Search_Exception_IncorrectLocation $e) {
			throw new ParamException($e->getMessage());
		}

		// рекурсивно проходимся по всем результатам поиска и собираем пользователей
		$this->action->users(array_unique(static::_collectUserData($search_result)));

		return $this->ok([
			"query"         => $query,
			"location_list" => Apiv2_Format::searchLocationList($search_result),
			"has_next"      => (int) $has_next,
		]);
	}

	/**
	 * Выполняет поиск совпадений в указанной локации.
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\PaymentRequiredException
	 * @throws \BaseFrame\Search\Exception\QueryException
	 * @throws CaseException
	 * @long
	 */
	public function findHits():array {

		$query         = $this->post(\Formatter::TYPE_STRING, "query");
		$location_type = $this->post(\Formatter::TYPE_STRING, "location_type");
		$location_key  = $this->post(\Formatter::TYPE_STRING, "location_key");
		$limit         = $this->post(\Formatter::TYPE_INT, "limit", 50);
		$offset        = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$limit  = min(200, max($limit, 1));
		$offset = max($offset, 0);

		try {

			// конвертируем ключ локации
			$type_list     = Domain_Search_Entity_LocationHandler::convertTypes([$location_type]);
			$location_type = reset($type_list);

			// формируем локацию
			$location_key = Domain_Search_Entity_LocationHandler::fromApi($location_type, $location_key);
		} catch (Domain_Search_Exception_IncorrectLocation $e) {
			throw new ParamException($e->getMessage());
		}

		try {

			[$search_result, $total_hit_count, $has_next] = Domain_Search_Scenario_Api::findHits(
				$this->user_id, COMPANY_ID, $query, $location_type, $location_key,
				$limit, $offset, $this->extra["space"]["is_restricted_access"]
			);
		} catch (Domain_Search_Exception_InvalidQuery $e) {
			throw new ParamException($e->getMessage());
		} catch (Domain_Search_Exception_LocationDenied) {
			throw new CaseException(2239001, "Location not allowed for user");
		}

		// рекурсивно проходимся по всем результатам поиска и собираем пользователей
		$this->action->users(array_unique(static::_collectUserData($search_result)));

		return $this->ok([
			"query"           => $query,
			"hit_list"        => Apiv2_Format::searchHitList($search_result),
			"has_next"        => (int) $has_next,
			"hit_count"       => (int) $total_hit_count,
			"total_hit_count" => (int) $total_hit_count,
		]);
	}

	/**
	 * Парсит user_id из результатов поиска.
	 */
	private static function _collectUserData(array $arr):array {

		$output = $arr["_user_action_data"] ?? [];

		foreach ($arr as $el) {

			if (is_array($el)) {
				array_push($output, ...static::_collectUserData($el));
			}
		}

		return $output;
	}
}
