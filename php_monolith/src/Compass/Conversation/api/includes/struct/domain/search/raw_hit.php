<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура хранимых данных совпадения.
 * Такие объекты достаются из поискового движка.
 *
 * @property Struct_Domain_Search_AppEntity[]            $inherit_parent_list
 * @property Struct_Domain_Search_RawHitNestedLocation[] $nested_location_list
 * @property Struct_Domain_Search_RawHit[]               $extra
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_RawHit {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public int                                     $user_id,
		public int                                     $creator_user_id,
		public Struct_Db_SpaceSearch_EntitySearchIdRel $entity_rel,
		public array                                   $inherit_parent_list,
		public int                                     $updated_at,
		public int                                     $field_mask,
		public array                                   $nested_location_list = [],
		public array                                   $extra = [],
	) {

		// nothing
	}
}
