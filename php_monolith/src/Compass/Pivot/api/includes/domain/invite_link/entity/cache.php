<?php

namespace Compass\Pivot;

/**
 * Класс для сохранения инвайт кодов в кеш и получения их оттуда
 */
class Domain_InviteLink_Entity_Cache {

	protected const _MEMCACHED_POSTFIX = __CLASS__;
	protected \mCache $mCache;

	// конструктор
	protected function __construct() {

		$this->mCache = ShardingGateway::cache();
	}

	/**
	 * инициализирует Singleton
	 */
	public static function init():Domain_InviteLink_Entity_Cache {

		if (isset($GLOBALS[__CLASS__])) {
			return $GLOBALS[__CLASS__];
		}

		// создаём объект, если еще не существует
		$GLOBALS[__CLASS__] = new Domain_InviteLink_Entity_Cache();
		return $GLOBALS[__CLASS__];
	}

	/**
	 * Устанавливаем значение по ключу
	 *
	 * @param int                              $user_id
	 * @param int                              $discount
	 * @param Struct_Db_PartnerData_InviteCode $invite_code_object
	 *
	 * @return void
	 */
	public function set(int $user_id, int $discount, Struct_Db_PartnerData_InviteCode $invite_code_object):void {

		$this->mCache->set($this->_getKeyMemCache($user_id, $discount), $invite_code_object, Domain_InviteLink_Entity_InviteLink::getDefaultTimeLifeCodeInCache());
	}

	/**
	 * Получаем значение по ключу
	 *
	 * @param int $user_id
	 * @param int $discount
	 *
	 * @return Struct_Db_PartnerData_InviteCode|false
	 */
	public function get(int $user_id, int $discount):Struct_Db_PartnerData_InviteCode|false {

		return $this->mCache->get($this->_getKeyMemCache($user_id, $discount));
	}

	##########################################################
	# region PROTECTED
	##########################################################

	/**
	 * Генерируем ключ для сохранения в кеш
	 *
	 * @param int $user_id
	 * @param int $discount
	 *
	 * @return string
	 */
	protected function _getKeyMemCache(int $user_id, int $discount):string {

		return $user_id . "_" . $discount . "_" . self::_MEMCACHED_POSTFIX;
	}

	# endregion
	##########################################################
}