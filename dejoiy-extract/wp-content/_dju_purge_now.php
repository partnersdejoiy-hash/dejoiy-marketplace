<?php
define('ABSPATH', dirname(__DIR__, 2) . '/');
require_once(ABSPATH . 'wp-load.php');

if (function_exists('litepeed_purge_all')) {
    litepeed_purge_all();
    echo "Purged via function";
} elseif (class_exists('LiteSpeed_Cache')) {
    LiteSpeed_Cache::purge('purge_all');
    echo "Purged via class";
} else {
    echo "No LiteSpeed purge function found. Manual purge needed.";
    echo "\nGo to: LiteSpeed Cache > Toolbox > Purge All";
}
