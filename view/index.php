<?php
/**
	@file
	@brief Imperium Dashboard View
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;

$list = array_keys($_ENV['data']);
foreach ($list as $name) {

    if (!isset($_ENV['data'][$name])) {
        continue;
    }

    $info = $_ENV['data'][$name];

    if (count($info['list']) > 0)
    {
        // echo "<div style='display: table-cell;'>";
        echo '<section class="mb-4">'; // style='display: table-cell;'>";
        echo sprintf('<h2>%s :: %d</h2>', $name, count($info['list']));
        echo Radix::block($info['view'],array('list'=>$info['list'],'opts'=>array('head'=>true)));
        echo '</section>';
    }
}

// Show the Events
$sql = 'SELECT contact_event.*, contact.name AS contact_name FROM contact_event';
$sql.= ' JOIN contact ON contact_event.contact_id = contact.id';
// $sql.= ' WHERE flag = 0';
$sql.= ' ORDER BY contact_event.xts DESC';
$sql.= ' LIMIT 20';
//$res = SQL::fetch_all($sql);
$res = [];
foreach ($res as $rec) {
	echo '<p>';
	echo '<a href="' . Radix::link('/contact/view?c=' . $rec['contact_id']) . '">' . html($rec['contact_name']) . '</a>';
	echo ' - ';
	echo html($rec['name']);
	echo ' - ';
	echo html($rec['note']);
	echo '</p>';
	echo '<p>Due: ' . strftime('%Y-%m-%d %H:%M', $rec['xts']) . '</p>';
}
