<?php

namespace BaseFrame\Http\Header;

/**
 * Обработчик для золовка идентификатора запроса.
 */
class RequestId extends Header {

	protected const _HEADER_KEY = "X_REQUEST_ID"; // ключ хедера

	/**
	 * @inheritDoc
	 */
	public function getValue(bool $generate_if_empty = true):string {

		parent::getValue();

		// если заголовок не передан и нужно сгенерировать, то генерируем
		if ($this->_value === "" && $generate_if_empty) {
			return $this->_value = "generated:" . generateUUID();
		}

		return $this->_value;
	}
}