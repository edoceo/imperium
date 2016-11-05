<?php
/**
	Account Statements Income Statement View
	Displays an Income Statement for a given time period
*/

use Edoceo\Radix;


// Input Form
echo '<form>';
// echo $this->form('AccountStatement',array('action'=>$this->appurl.'/account.statement/income','class'=>'np'));
echo Radix::block('account-period-input');
echo '</form>';

$cr_total = $dr_total = 0;

// Revenues
echo '<table class="w">';
echo '<tr class="ro"><th class="l" colspan="3" >Revenues</td></tr>';
foreach ($this->RevenueAccountList as $a) {

    $uri = Radix::link('/account/ledger?' . http_build_query(array('id'=>$a['account_id'], 'd0'=>$this->date_alpha, 'd1'=>$this->date_omega)));

    echo '<tr class="rero">';
    //echo "<td style='padding-left: 2em;'>{$item->full_code}  {$item->account_name}</td>";
    echo '<td style="padding-left: 2em;"><a href="' . $uri . '">' . $a['full_code']. ' '. $a['full_name'] . '</a></td>';
    echo '<td>&nbsp;</td><td class="r">' . number_format($a['balance'],2) . '</td>';
    echo '</tr>';

    $cr_total += $a['balance'];
}
echo "<tr class='ro'><td class='b'>Total Revenue:</td><td>&nbsp;</td><td class='b r u'>".number_format($cr_total,2)."</td></tr>\n";
echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";

// Expenses
echo '<tr class="ro"><th class="l" colspan="3" >Expenses</td></tr>';
foreach ($this->ExpenseAccountList as $a) {

    $uri = Radix::link('/account/ledger?' . http_build_query(array('id'=>$a['account_id'], 'd0'=>$this->date_alpha, 'd1'=>$this->date_omega)));

    echo '<tr class="rero">';
    echo '<td style="padding-left: 2em;"><a href="'. $uri . '">' . $a['full_code']. ' '. $a['full_name'] . '</a></td>';
    echo "<td class='r'>" . number_format($a['balance'] * -1 , 2) . '</td><td>&nbsp;</td>';
    echo '</tr>';

    $dr_total += $a['balance'];
}
echo '<tr class="ro"><td class="b" colspan="2">Total Expenses:</td><td class="b r u">' . number_format($dr_total * -1,2)."</td></tr>";
echo '<tr><td colspan="3">&nbsp;</td></tr>';
echo '<tr class="ro"><td class="b" colspan="2" style="border-top:1px solid #333;">Net Income:</td><td class="b r" style="border-top:1px solid #333;">' . number_format($cr_total + $dr_total,2) . '</td></tr>';
echo '</table>';
