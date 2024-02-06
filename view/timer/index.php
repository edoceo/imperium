<?php
/**
 * Timer Create View
 * Input for for Creating a Named Timer
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

use \Edoceo\Radix\HTML;

$_ENV['h1'] = 'Timers';

$timer_list = $_SESSION['timer'];
uasort($timer_list, function($a, $b) {
	return ($a->time_alpha > $b->time_alpha);
});

?>

<form action="/timer" method="post">

<div class="container">

<div class="input-group">
	<span class="input-group-text">Name:</span>
	<input class="form-control" name="timer-name" value="">
	<button class="btn btn-primary" name="a" value="timer-save">
		<i class="fas fa-save"></i> Save
	</button>
</div>

</form>

<section class="mb=t-4">
	<h2>Active Timers</h2>
	<?php
	foreach ($timer_list as $t) {

		$t1 = $t->time_omega;
		if (empty($t1)) {
			$t1 = new \DateTime();
		}

		$diff = $t->time_alpha->diff($t1);

		?>

		<form action="/timer" method="post">
		<div class="row">
			<div class="col-md-4">
				<input name="timer-id" type="hidden" value="<?= __h($t->hash) ?>">
				<h3><?= __h($t->name) ?></h3>
			</div>
			<div class="col-md-2">
				<?= __h($t->time_alpha->format('D Y-m-d H:i')) ?>
			</div>
			<div class="col-md-2">
				<?= __h($t1->format('D Y-m-d H:i')) ?>
			</div>
			<div class="col-md-2">
				<?= $diff->format('%r %a %H:%I:%S.%F') ?>
			</div>
			<div class="col-md-2 text-end">
				<div class="btn-group">
					<?php
					if (empty($t->time_omega)) {
					?>
						<button class="btn btn-warning" name="a" value="timer-stop"><i class="fa-regular fa-circle-stop"></i> Stop</button>
					<?php
					}
					?>
					<button class="btn btn-danger" name="a" value="timer-delete"><i class="fa-regular fa-trash-can"></i> Delete</button>
				</div>
			</div>
		</div>
		</form>
	<?php
	}
	?>
</section>

</div>
