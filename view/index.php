<?php
/**
	@file
	@brief Imperium Dashboard View
*/

$list = array('Agenda','Active Timers','Active To-Dos','Pending Work Orders','Active Work Orders','Active Invoices');

foreach ($list as $k) {

    $name = $k;
    if (!isset($this->data[$name])) {
        continue;
    }

    $info = $this->data[$name];

    if (count($info['list']) > 0)
    {
        // echo "<div style='display: table-cell;'>";
        echo '<div>'; // style='display: table-cell;'>";
        echo sprintf('<h2>%d %s</h2>',count($info['list']),$name);
        echo $this->partial($info['view'],array('list'=>$info['list'],'opts'=>array('head'=>true)));
        echo '</div>';
    }
}

?>

<script type="text/javascript">
$('tr').live('click', function(e) {
    // Skip These
    if ($(e.target).is("a,input")) return;
    //$(this).find("a").eq(0).click(); // woops, .click doesn't follow normal links...
    /// location.href = $(e.target).attr('href');
    window.location = $(this).find('a').attr('href');
    e.stopPropagation();
});
</script>