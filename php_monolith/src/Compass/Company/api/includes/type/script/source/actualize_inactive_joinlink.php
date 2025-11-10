<?php

namespace Compass\Company;

/**
 * Скрипт для актуализации неактивных ссылок-приглашений
 *
 * Безопасен для повторного исполнения.
 *
 * @since 11.08.25
 */
class Type_Script_Source_ActualizeInactiveJoinlink extends Type_Script_CompanyUpdateTemplate {

	protected const _GET_LIMIT = 1000;

	/**
	 * Выполняем скрипт
	 *
	 * @param array $data
	 *
	 * @long
	 */
	public function exec(array $data):void {

		// получаем список неактивных ссылок
		$join_link_list = Domain_JoinLink_Action_GetInactiveList::do(self::_GET_LIMIT, 0, true);

		// фильтруем, получаем только те, что уже использованы и не имеют ограничений по времени
		$filtered_join_link_list = [];
		foreach ($join_link_list as $link) {

			if ($link->status != Domain_JoinLink_Entity_Main::STATUS_USED || $link->expires_at > 0) {
				continue;
			}

			$filtered_join_link_list[] = $link;
		}

		if (count($filtered_join_link_list) < 1) {

			$this->_log("В компании отсутствуют ссылки со статусом \"used\" и без ограничения по времени, company_id = " . COMPANY_ID);
			return;
		}

		// DRY-RUN
		if ($this::_isDry()) {

			$this->_log("DRY-RUN - Обновили время истечения для неактивных ссылок-приглашений, company_id = " . COMPANY_ID);
			return;
		}

		foreach ($filtered_join_link_list as $link) {

			$last_updated_at = $link->updated_at;

			$set = [
				"updated_at" => time(),
				"expires_at" => $last_updated_at,
			];
			Gateway_Db_CompanyData_JoinLinkList::set($link->join_link_uniq, $set);
		}

		$this->_log("Успешно обновили неактивные ссылки-приглашения, company_id = " . COMPANY_ID);
	}
}