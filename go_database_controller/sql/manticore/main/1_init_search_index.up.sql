/* главная таблица для поиска */
CREATE TABLE IF NOT EXISTS `main_{space_id}`
(
    /* поля атрибуты, используются только int и int64 для экономии памяти */
    `user_id` bigint,                         /* идентификатор пользователя, для которого будет делаться выборка */
    `search_id` bigint,                       /* уникальный поисковый ключ сущности */
    `creator_id` bigint,                      /* создатель сущности */
    `type` integer,                           /* тип сущности */
    `attribute_mask` integer,                 /* маска атрибутов */
    `parent_id` bigint,                       /* прямой родитель */
    `group_by_conversation_parent_id` bigint, /* родитель для группировки по диалогам (нужно для подсчета совпадений в окошке диалогов) */
    `inherit_parent_id_list` multi64,         /* уникальные унаследованные ключи родителей */
    `parent_type_mask` bigint,                /* типы родителей */
    `updated_at` integer,                     /* дата актуальности сущности */
    /* поля с текстом для поиска */
    /* каждая сущность имеет маппинг атрибутов в эти поля */
    `field1` text indexed,
    `field2` text indexed,
    `field3` text indexed,
    `field4` text indexed
)
/* маппим атрибуты без mlock */
access_plain_attrs = 'mmap'
access_blob_attrs = 'mmap'
/* ограничение на ОЗУ чанк */
rt_mem_limit = '16MB'
/* wildcard поиск */
min_prefix_len = '3'
/* допустимые символы маппинги символов:
U+0401->U+0415 Ё->Е
U+0451->U+0435 ё->е
U+0406->U+0456,U+0456,U+0407->U+0457,U+0457,U+0490->U+0491,U+0491
*/
charset_table = '0..9, non_cjk, cjk, U+0406->U+0456, U+0456, U+0407->U+0457, U+0457, U+0490->U+0491, U+0491, U+0401->U+0415, U+0451->U+0435'
ignore_chars = 'U+003F, U+002A, U+0025'