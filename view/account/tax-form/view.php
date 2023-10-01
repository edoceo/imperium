<?php
/**
	Account Tax Forms View

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1013
*/

$_ENV['title'] = array('Account', 'Tax Forms', $this->Form['name'], $this->date_alpha_f);

echo '<form action="" class="np" method="get">';
echo '<div>';
echo radix_html_form::hidden('id', $_GET['id']);
echo radix::block('account-period-input');
echo '</div>';
echo '</form>';

echo '<table class="table">';

foreach ($this->LineList as $item) {
	// Skip Zero Balance
	if ($item['balance'] == 0) {
		//continue;
	}

	// Tax Line
	echo "<tr><th class='l' colspan='2'>" . $item['name'] . "</th><th class='r'>" . number_format($item['balance'],2) . "</th></tr>";
	// Accounts that lead to this conculsion
	foreach ($item['accounts'] as $a) {
		echo "<tr><td>&nbsp;</td><td>" . $a['name'] . "</td><td class='r'>" . number_format($a['balance'],2) . "</td></tr>";
	}
}
echo '</table>';
