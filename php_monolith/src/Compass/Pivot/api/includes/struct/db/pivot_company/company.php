<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_company.company_list_{1}
 */
class Struct_Db_PivotCompany_Company {

	/**
	 * Struct_Db_PivotCompany_Company constructor.
	 *
	 * @param int    $company_id
	 * @param int    $is_deleted
	 * @param int    $status
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $deleted_at
	 * @param int    $avatar_color_id
	 * @param int    $created_by_user_id
	 * @param int    $partner_id
	 * @param string $domino_id
	 * @param string $name
	 * @param string $url
	 * @param string $avatar_file_map
	 * @param array  $extra
	 */
	public function __construct(
		public int    $company_id,
		public int    $is_deleted,
		public int    $status,
		public int    $created_at,
		public int    $updated_at,
		public int    $deleted_at,
		public int    $avatar_color_id,
		public int    $created_by_user_id,
		public int    $partner_id,
		public string $domino_id,
		public string $name,
		public string $url,
		public string $avatar_file_map,
		public array  $extra
	) {

		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");

		if (!isset($domino_entrypoint_config[$domino_id])) {
			return;
		}

		$this->url = str_replace(
			["{company_id}", "{domino_id}", "{domino_url}"],
			[(string) $company_id, $domino_id, $domino_entrypoint_config[$domino_id]["public_url"]],
			$domino_entrypoint_config[$domino_id]["template_public_company_url"]
		);
	}

	/**
	 * Формирует ссылку для редиректа ссылок в чате.
	 */
	public function getPublicRedirectUrl():string {

		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");

		if (!isset($domino_entrypoint_config[$this->domino_id])) {
			return "{$this->url}/redirect";
		}

		if (!isset($domino_entrypoint_config[$this->domino_id]["template_redirect_company_url"])) {
			return "{$this->url}/redirect";
		}

		return str_replace(
			["{company_id}", "{domino_id}", "{domino_url}"],
			[(string) $this->company_id, $this->domino_id, $domino_entrypoint_config[$this->domino_id]["public_url"]],
			$domino_entrypoint_config[$this->domino_id]["template_redirect_company_url"]
		);
	}
}
