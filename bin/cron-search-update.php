#!/usr/bin/php -e
<?php
/**
    @file
    @brief Adds / Updates the ts_search fields on the database
    @note this is not fully functional yet

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

// CLI
require_once(dirname(dirname(__FILE__)) . '/lib/cli.php');
require_once(APP_ROOT . '/lib/Search.php');

Search::update();
