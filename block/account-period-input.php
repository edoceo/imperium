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

?>

<div class="form-inline" id="account-period-input">

<?php
echo Form::select('m', $m, Radix::$view->MonthList, array('class' => 'form-control'));
echo Form::select('y', $y, Radix::$view->YearList, array('class' => 'form-control'));
echo Form::select('p', $p, Radix::$view->PeriodList, array('class' => 'form-control'));
?>

<div class="form-check form-check-inline">
  <input class="form-check-input" name="xc" type="checkbox" id="xc" value="true">
  <label class="form-check-label" for="xc">Exclude Closing Transactions</label>
</div>

<div class="form-check form-check-inline">
  <input class="form-check-input" name="xz" type="checkbox" id="xz" value="true">
  <label class="form-check-label" for="xz">Exclude Zero Balance Accounts</label>
</div>

<button class="btn btn-outline-secondary" name="c" value="view">View</button>

</div>
