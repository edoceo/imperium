<?php
/**
	Account Form Index View
*/

if (!acl::may('/account/tax-form')) {
	radix_session::flash('fail', 'Access Denied');
	radix::redirect();
}

$_ENV['h1'] = $_ENV['title'] = array('Accounting','Tax Forms');

$res = radix_db_sql::fetchAll("select id,name from account_tax_form order by name");

echo '<p>Choose a Tax Form to Print!</p>';

echo '<ul>';
foreach ($res as $i=>$f) {
	echo '<li><a href="' . radix::link('/account/tax-form/view?id=' . $f['id']) . '">' . $f['name'] . '</a></li>';
}
echo '</ul>';

