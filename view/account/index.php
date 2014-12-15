<?php
/**
	Account Index View
	Displays a General Ledger
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\DB\SQL;

$_ENV['title'] = 'General Ledger';

$x_kind = null;

echo '<form action="" class="np" method="get">';
echo Radix::block('account-period-input');
echo '</form>';

// Search
echo '<form action="' . Radix::link('/account/search') . '">';
echo '<div>';
echo '<input type="text" name="q" value="">';
echo '<button value="search">Search</button>';
echo '</div>';

echo '<p><strong>Cash</strong> basis, reports for money collected when B&amp;O</p>';
echo '<p>Accounts for Period: ' . $this->AccountPeriod['date_alpha'] . ' - ' . $this->AccountPeriod['date_omega'] . '</p>';

echo '<table>';

foreach ($this->AccountList as $item) {

    if ($x_kind != $item['kind']) {
        echo "<tr><th>Code</th><th>{$item['kind']}</th><th>Balance</th><th colspan='3'>Actions</th></tr>";
    }

    echo '<tr class="rero">';

    $indent = str_repeat('&nbsp;',substr_count($item['full_code'],'/'));
    echo '<td>' . $indent . '<a href="' . Radix::link('/account/ledger?id='.$item['id']) . '">' . html($item['full_code']) . '</td>';
    echo "<td>{$item['name']}";
	if (strlen($item['account_tax_line_name'])) {
		echo " <span class='s'>({$item['account_tax_line_name']})</span>";
	}
	echo '</td>';
    echo '<td class="r">' . number_format(abs($item['balance']),2) . '</td>';
    // echo '<td class="r"><a href="' . Radix::link('/account/ledger?id=' . $item['id']) . '">' . $img_al . '</td>';
    echo '<td class="r"><a href="' . Radix::link('/account/journal?id=' . $item['id']) . '"><i class="fa fa-align-left"></i></td>';
    echo '<td class="r"><a href="' . Radix::link('/account/edit?id=' . $item['id']) . '"><i class="fa fa-check-square-o"></i></td>';
    echo '</tr>';

    $x_kind = $item['kind'];
}

echo '</table>';
