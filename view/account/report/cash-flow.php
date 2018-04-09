<?php
/**
	Account Statement Cash Flow

	presents revenues and expenses and resulting net income or loss for a specific period of time

	@see https://en.wikipedia.org/wiki/Cash_flow_statement
	@see https://en.wikipedia.org/wiki/IAS_7
	@see http://www.iasplus.com/en/standards/ias/ias7

	@package Edoceo Imperium
	@todo Bring this up to date with Zend Framework, has cruft from Imperium v600
	
	Account Statement Cash Flow Action
	Produces a Cash Flow Statement
	@todo this one has the most hacks, needs to be brought up from PHP -> Cake -> Zend models

*/

use Edoceo\Radix;

// Input Form
echo '<div class="print-hide">';
echo '<form>';
echo Radix::block('account-period-input');
echo '</form>';
echo Radix::block('account-period-arrow', $this->date_alpha);
echo '</div>';

echo '<table class="table">';

// @todo Break this down waaayyy better, look at the samples.
// @todo This might involve tagging each and every Journal Entry as to how it affects Cash Flow (if at all)
// @todo That could be annoying but not each Account can be explicitly tied to a Cash Flow line (or can they?)

echo '<tr><th class="l" colspan="3">Cash Flows from Operating Activities</th></tr>';
echo "<tr><td class='ti'>Cash Receipts from  Revenues:</td><td class='r'>".number_format($this->Revenues,2) . '</td></tr>';
echo "<tr><td class='ti'>Cash Payments for Expenses:</td><td class='r'>".number_format(abs($this->Expenses),2) . '</td></tr>';
//echo "<tr><td>Net Income:</td><td class='r'>".number_format($this->NetIncome,2)."</td><td>&nbsp;</td></tr>";
echo '<tr class="ro"><td class="b">Net Cash Flows from Operating Activities:</td><td class="r"><span class="u">' . number_format($this->Revenues + $this->Expenses,2) . '</span></td></tr>';
//echo '<tr><td colspan="3">&nbsp;</td></tr>';

echo '<tr><td colspan="3">&nbsp;</td></tr>';
echo '<tr><th class="l" colspan="3">Cash Flows from Investing Activities</th></tr>';

echo '<tr class="ro"><td class="b">Net Cash Flows from Investing Activities:</td><td class="r"><span class="u">' . number_format(0,2) . '</span></td></tr>';


echo '<tr><td colspan="3">&nbsp;</td></tr>';
echo '<tr><th class="l" colspan="3">Cash Flows from Financing Activities</th></tr>';

echo "<tr><td>Investments:</td><td class='r'>".number_format($this->Capital,2)."</td><td>&nbsp;</td></tr>";
echo "<tr><td>&nbsp;</td><td class='r'>".number_format($this->Capital + $this->NetIncome,2)."</td></tr>";


// todo: calculate owners drawings
echo "<tr><td>Drawings:</td><td>&nbsp;</td><td class='r'>".number_format($this->Drawings,2)."</td></tr>";
echo "<tr><td>Capital:</td><td>&nbsp;</td><td class='r'>".number_format($this->Capital + $this->NetIncome - $this->Drawings,2)."</td></tr>";


echo '<tr class="ro"><td class="b" colspan="2">Net Increase in Cash:</td><td class="r"><span class="u">' . number_format(0,2) . '</span></td></tr>';
echo '<tr class="ro"><td class="b" colspan="2">Cash, Beginning of Period:</td><td class="r"><span class="u">' . number_format(0,2) . '</span></td></tr>';
echo '<tr class="ro"><td class="b" colspan="2">Cash, End of Period:</td><td class="r"><span class="du">' . number_format(0,2) . '</span></td></tr>';

echo '</table>';

echo '<p>This statement prepared according to IAS7/2007</p>';
