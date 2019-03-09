<?php
/**
	Main Account Page
*/

use Edoceo\Radix\DB\SQL;

// Make Sure all Accounts have their Balance Updated

$sql = 'UPDATE account';
$sql.= ' SET balance = (';
$sql.= ' SELECT sum(amount) FROM account_ledger WHERE account.id = account_ledger.account_id';
$sql.= ')';

SQL::query($sql);
