<?php

namespace Compass\Announcement;

/**
 * Контроллер для кастомной логики
 */
class Apiv1_Controller extends \BaseFrame\Controller\Api {

	/** @var bool флаг готовности меты */
	protected static bool $_is_meta_parsed = false;

	/** @var array мета-данные класса */
	protected static array $_cached_reflection_meta = [
		Type_Attribute_Api_Method::ALLOWED                    => [],
		Type_Attribute_Api_Method::ALLOWED_FOR_NON_AUTHORIZED => [],
	];

	/**
	 * получаем action
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	protected function _isHasMethod(string $action):bool {

		// получаем мету для определения
		$meta = $this::_getReflectionMeta();

		// проверяем общую доступность метода для вызова
		$allow_methods = $meta[Type_Attribute_Api_Method::ALLOWED];
		$allowed       = false;

		foreach ($allow_methods as $key => $value) {

			if (strtolower($action) == strtolower($key)) {

				$allowed = true;
				break;
			}
		}

		// если метод недоступен, то возвращаем false
		// если пользователь прошел авторизацию, то возвращаем доступ по методу
		if ($allowed === false || $this->user_id !== 0) {
			return $allowed;
		}

		// если метод доступен, но пользователь не авторизован,
		// то проверяем доступность метода для неавторизованных пользователей
		$allowed                      = false;
		$allow_not_authorized_methods = $meta[Type_Attribute_Api_Method::ALLOWED_FOR_NON_AUTHORIZED];

		foreach ($allow_not_authorized_methods as $key => $value) {

			if (strtolower($action) == strtolower($key)) {

				$allowed = true;
				break;
			}
		}

		return $allowed;
	}

	/**
	 * Возвращает отражение класса.
	 *
	 * @return array
	 */
	protected static function _getReflectionMeta():array {

		// если мета еще не распарсена, то парсим ее
		if (!static::$_is_meta_parsed) {

			$reflection = new \ReflectionClass(static::class);
			$methods    = $reflection->getMethods();

			foreach ($methods as $method) {

				foreach ($method->getAttributes(Type_Attribute_Api_Method::class) as $attribute) {

					/** @var Type_Attribute_Api_Method $attribute_instance */
					$attribute_instance = $attribute->newInstance();

					if ($attribute_instance->isAllowed()) {
						static::$_cached_reflection_meta[Type_Attribute_Api_Method::ALLOWED][$method->getName()] = true;
					}

					if ($attribute_instance->isAllowedWithoutAuth()) {
						static::$_cached_reflection_meta[Type_Attribute_Api_Method::ALLOWED_FOR_NON_AUTHORIZED][$method->getName()] = true;
					}
				}
			}

			// отмечаем, что мета готова
			static::$_is_meta_parsed = true;
		}

		return static::$_cached_reflection_meta;
	}
}