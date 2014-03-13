<?php
/**
	Select an Account Period

	@copyright	2002 Edoceo, Inc
	@package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/

echo '<div class="bf c">';

echo radix_html_form::select('m', $_GET['m'], radix::$view->MonthList); //null,$this->data['month'],null,false); // Month
echo radix_html_form::select('y', $_GET['y'], radix::$view->YearList ); //null,$this->data['year'],null,false); // Year
echo radix_html_form::select('p', $_GET['p'], radix::$view->PeriodList); //null,$this->data['period'],null,false); // Period
echo radix_html_form::submit('c', 'View');

echo '<div class="bf c">';
echo '<label for="ex_close">&nbsp;' . radix_html_form::checkbox('ex_close', radix::$view->ExcludeClose, null, array('Checked'=>1,'unChecked'=>0)) . '&nbsp;Exclude Closing Transactions</label>';
echo '&nbsp;';
echo '<label for="ex_zeros">&nbsp;' . radix_html_form::checkbox('ex_zeros', radix::$view->ExcludeZeros, null, array('Checked'=>1,'unChecked'=>0)) . '&nbsp;Exclude Zero Balance Accounts</label>';
echo '</div>';

echo '</div>';
