<?php

namespace Compass\Pivot;

/**
 * Сценарии публичных документов для API
 */
class Domain_Document_Scenario_Api {

	/**
	 * получаем список публичных документов
	 *
	 * @throws cs_PublicDocumentNotFound
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	public static function getPublicDocuments(string $lang):array {

		$public_documents = Type_Lang_Document::getPublicDocuments($lang);
		if (count($public_documents) < 1) {
			throw new cs_PublicDocumentNotFound();
		}
		return $public_documents;
	}
}
