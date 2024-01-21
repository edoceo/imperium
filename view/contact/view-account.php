<?php
/**
 *
 */

use Edoceo\Radix;
use Edoceo\Radix\Layout;

?>
<section>
<h2>Accounts:</h2>
<?php
if (empty($this->Account['id'])) {
?>
	<form action="<?= Radix::link('/contact/save?c=' . $this->Contact['id']) ?>" autocomplete="off" method="post">
	<div>
	<p>Receiveable: <button class="btn btn-secondary" name="a" title="Create new Account for this Contact" style="margin:4px;" type="submit" value="create-account">Create</button>
	</div>
	</form>
<?php
} else {
	echo '<a href="' . Radix::link('/account/ledger?' . http_build_query(array('id' => $this->Account['id']))) . '">Account</a>:';
	echo '<span style="font-size: 22px; line-height: 32px;">';
	echo '$' . number_format($this->Account['balance'],2);
	// echo '<input id="account" style="width:20em;" type="text" value="' . $this->Account['full_name'] . '">';
	// echo '<input id="account_id" name="account_id" type="hidden" value="' . $this->Account['id'] . '">';
	echo '</span>';
}
?>
</section>

<?php
ob_start();
?>
<script type='text/javascript'>
$('#account').autocomplete({
    source: "<?= Radix::link('/account/ajax?a=account'); ?>",
    change: function(event, ui) {
        if (ui.item) {
            $('#account').val(ui.item.label);
            $('#account_id').val(ui.item.id);
        }
    }
});
</script>
<?php
$code = ob_get_clean();
Layout::addScript($code);
