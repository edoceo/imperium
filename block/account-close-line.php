<?php
/**
 * A Line Entry in the Account Close Page
 */

use Edoceo\Radix;

$line = $data['line'];

$link = Radix::link('/account/ledger?' . http_build_query(array(
	'id' => $line['id'],
	'd0' => $data['date_alpha'],
	'd1' => $data['date_omega'],
)));

echo '<tr class="rero">';
echo '<td><strong>' . $line['full_code'] . '</strong>&mdash;<a href="' . $link . '">' . $line['name'] . '</a></td>';

// Kind?
echo '<td class="c">';
switch ($line['kind']) {
case 'A':
	echo 'Adjusting';
	break;
case 'C':
	echo 'Closing';
	break;
case 'N':
	echo 'Normal';
	break;
}
echo '</td>';

// Credit or Debit
if ($line['balance'] > 0) {
	echo '<td class="r">' . number_format($line['balance'] * -1,2) . '</td>'; // cr
	echo '<td>&nbsp;</td>'; // dr
} else {
	echo '<td>&nbsp;</td>'; // cr
	echo '<td class="r">' . number_format($line['balance'] * -1,2) . '</td>'; // dr
}

echo '</tr>';
