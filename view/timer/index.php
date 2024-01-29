<?php
/**
 * Timer Create View
 * Input for for Creating a Named Timer
 */

use \Edoceo\Radix\HTML;

$_ENV['h1'] = 'Timers';

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
	foreach ($_SESSION['timer'] as $t) {

		$t1 = $t->time_omega;
		if (empty($t1)) {
			$t1 = new \DateTime();
		}

	?>
		<form action="/timer" method="post">
		<div class="row">
			<div class="col-md-4">
				<h3><?= __h($t->name) ?></h3>
			</div>
			<div class="col-md-2">
				<h3><?= __h($t->time_alpha->format(\DateTime::RFC3339)) ?></h3>
			</div>
			<div class="col-md-2">
				<h3><?= __h($t1->format(\DateTime::RFC3339)) ?></h3>
			</div>
			<div class="col-md-2">
				<?php
				$diff = $t->time_alpha->diff($t1);
				echo $diff->format('%r %a %H:%I:%S.%F');
				?>
			</div>
			<div class="col-md-2">
				<input name="timer-id" type="hidden" value="<?= __h($t->hash) ?>">
				<button class="btn btn-warning" name="a" value="timer-stop"><i class="fa-regular fa-circle-stop"></i> Stop</button>
				<button class="btn btn-danger" name="a" value="timer-delete"><i class="fa-regular fa-trash-can"></i> Delete</button>
			</div>
		</div>
		</form>
	<?php
	}
	?>
</section>

<pre>
<?php
var_dump($_SESSION['timer']);
?>
</pre>

</div>
