<?php
/**
	Account Index View
	Displays a General Ledger
*/

$_ENV['title'] = 'General Ledger';

$x_kind = null;
$img_ed = img('/silk/1.3/chart_bar_edit.png','Edit');
$img_aj = img('/silk/1.3/chart_bar_edit.png','Journal');
$img_al = img('/silk/1.3/chart_bar_edit.png','Ledger');

echo '<form action="" class="np" method="get">';
echo radix::block('account-period-input');
echo '</form>';

echo '<form action="' . radix::link('/account/search') . '">';
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
    echo '<td>' . $indent . '<a href="' . radix::link('/account/ledger?id='.$item['id']) . '">' . html($item['full_code']) . '</td>';
    echo "<td>{$item['name']}";
	if (strlen($item['account_tax_line_name'])) {
		echo " <span class='s'>({$item['account_tax_line_name']})</span>";
	}
	echo '</td>';
    echo '<td class="r">' . number_format(abs($item['balance']),2) . '</td>';
    echo '<td class="r"><a href="' . radix::link('/account/ledger?id=' . $item['id']) . '">' . $img_al . '</td>';
    echo '<td class="r"><a href="' . radix::link('/account/journal?id=' . $item['id']) . '">' . $img_aj . '</td>';
    echo '<td class="r"><a href="' . radix::link('/account/edit?id=' . $item['id']) . '">' . $img_ed . '</td>';
    echo '</tr>';

    $x_kind = $item['kind'];
}

echo '</table>';
