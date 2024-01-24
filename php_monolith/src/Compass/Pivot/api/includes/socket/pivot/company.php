<?php

namespace Compass\Pivot;

/**
 * Методы для работы с самой компанией
 */
class Socket_Pivot_Company extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"ping",
		"getList",
	];

	/**
	 * Проверяем, существует ли компания (если вернется ОК, то значит компания существует, иначе handler просто не пустит)
	 *
	 */
	public function ping():array {

		return $this->ok();
	}

	/**
	 * получаем список компаний
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getList():array {

		$limit       = $this->post(\Formatter::TYPE_INT, "limit", 1000);
		$offset      = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$only_active = $this->post(\Formatter::TYPE_INT, "only_active", 1);

		try {
			$company_list = Domain_Crm_Scenario_Socket::getCompanyList($limit, $offset, $only_active == 1);
		} catch (cs_CompanyIncorrectLimit) {
			return $this->error(657, "Incorrect limit");
		} catch (cs_CompanyIncorrectMinOrder) {
			return $this->error(658, "Incorrect offset");
		}

		return $this->ok([
			"company_list" => (array) $company_list,
			"offset"       => (int) $offset,
		]);
	}
}