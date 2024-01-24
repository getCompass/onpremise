<?php declare(strict_types=1);

namespace Compass\Conversation;

$space_id         = readline("space id: ");
$conversation_key = readline("conversation key: ");

define("MANTICORE_SEARCH_QUERY_LOG_ENABLED", false);
putenv("COMPANY_ID=$space_id");

require_once __DIR__ . "/../../../../../../start.php";
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");
set_time_limit(0);

$conversation_map = \CompassApp\Pack\Conversation::doDecrypt($conversation_key);
Domain_Search_Entity_Conversation_Task_Reindex::queueList([$conversation_map]);
