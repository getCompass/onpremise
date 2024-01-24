<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Атрибут, который определяет метод
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Type_Attribute_Api_Method {

	/** @var int метод доступен для вызова */
	const ALLOWED = 1 << 0;
	/** @var int метод доступен без авторизации */
	const ALLOWED_FOR_NON_AUTHORIZED = 1 << 1;

	/** @var int маска, определяющая доступ к методу */
	protected int $_mask = 0;

	/**
	 * Type_Attribute_Api_Controller constructor.
	 *
	 * @param int ...$args
	 */
	public function __construct(int ...$args) {

		foreach ($args as $value) {
			$this->_mask |= $value;
		}
	}

	/**
	 * Возвращает флаг доступности метода для вызова в качестве входной точки.
	 *
	 * @return bool
	 */
	public function isAllowed():bool {

		return ($this->_mask & $this::ALLOWED) > 0;
	}

	/**
	 * Возвращает флаг доступности метода для вызова в качестве входной точки.
	 *
	 * @return bool
	 */
	public function isAllowedWithoutAuth():bool {

		return ($this->_mask & $this::ALLOWED_FOR_NON_AUTHORIZED) > 0;
	}
}