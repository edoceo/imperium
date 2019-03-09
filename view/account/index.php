<?php
/**
	Account Index View
	Displays a General Ledger
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;

$_ENV['title'] = 'General Ledger';

$x_kind = null;

//echo '<form action="" class="np" method="get">';
//echo Radix::block('account-period-input', array(
//	'm' => $this->Month,
//	'y' => $this->Year,
//	'p' => $this->Period,
//));
//echo '</form>';

// Search
// echo '<form action="' . Radix::link('/account/search') . '">';
// echo '<div>';
// echo '<input type="text" name="q" value="">';
// echo '<button value="search">Search</button>';
// echo '</div>';

echo '<div class="container">';
//echo '<p>Accounts for Period: ' . $this->date_alpha . ' - ' . $this->date_omega . '</p>';
echo '<table class="table table-hover">';

foreach ($this->AccountList as $item) {

    if ($x_kind != $item['kind']) {
        echo "<tr><th>Code</th><th>{$item['kind']}</th><th>Balance</th><th>Actions</th></tr>";
    }

    echo '<tr>';

    $indent = str_repeat('&nbsp;',substr_count($item['full_code'],'/'));

    echo '<td>' . $indent . '<a href="' . Radix::link('/account/ledger?id='.$item['id']) . '">' . html($item['full_code']) . '</td>';

    echo '<td>';
    echo '<a href="' . Radix::link('/account/ledger?id='.$item['id']) . '">';
    echo html($item['name']);
	if (strlen($item['account_tax_line_name'])) {
		echo " <span class='s'>({$item['account_tax_line_name']})</span>";
	}
	echo '</a>';
	echo '</td>';

    echo '<td class="r">' . number_format(abs($item['balance']), 2) . '</td>';
    // echo '<td class="r"><a href="' . Radix::link('/account/ledger?id=' . $item['id']) . '">' . $img_al . '</td>';

    echo '<td class="r">';
    echo ' <a class="btn btn-sm btn-outline-secondary" href="' . Radix::link('/account/journal?id=' . $item['id']) . '"><i class="fa fa-align-left"></i></a>';
    echo ' <a class="btn btn-sm btn-outline-secondary" href="' . Radix::link('/account/edit?id=' . $item['id']) . '"><i class="fa fa-check-square-o"></i></a>';
    echo '</td>';

    echo '</tr>';

    $x_kind = $item['kind'];
}

echo '</table>';
echo '</div>';
