<?php
/**
 * Main Account Page
 */

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

// Make Sure all Accounts have their Balance Updated

$sql = 'UPDATE account';
$sql.= ' SET balance = (';
$sql.= ' SELECT sum(amount) FROM account_ledger WHERE account.id = account_ledger.account_id';
$sql.= ')';

SQL::query($sql);

$sql = <<<SQL
SELECT *
FROM account
-- WHERE parent_id IS NULL
ORDER BY full_code ASC, code ASC
SQL;

$this->AccountList = SQL::fetch_all($sql);
