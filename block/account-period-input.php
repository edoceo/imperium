<?php
/**
	Select an Account Period

	@copyright	2002 Edoceo, Inc
	@package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\HTML\Form;

echo '<div class="bf c">';

echo Form::select('m', $_GET['m'], Radix::$view->MonthList); //null,$this->data['month'],null,false); // Month
echo Form::select('y', $_GET['y'], Radix::$view->YearList ); //null,$this->data['year'],null,false); // Year
echo Form::select('p', $_GET['p'], Radix::$view->PeriodList); //null,$this->data['period'],null,false); // Period
echo Form::submit('c', 'View');

echo '<div class="bf c">';
echo '<label for="xc">&nbsp;' . Form::checkbox('xc', 'true', ('true'==$_GET['xc'] ? array('checked'=>'checked') : null) ) . '&nbsp;Exclude Closing Transactions</label>';
echo '&nbsp;';
echo '<label for="xz">&nbsp;' . Form::checkbox('xz', 'true', ('true'==$_GET['xz'] ? array('checked'=>'checked') : null)) . '&nbsp;Exclude Zero Balance Accounts</label>';
echo '</div>';

echo '</div>';
