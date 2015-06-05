<?php
/**
	@file
	@brief Imperium Dashboard View
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

$list = array_keys($_ENV['data']);
foreach ($list as $name) {

    if (!isset($_ENV['data'][$name])) {
        continue;
    }

    $info = $_ENV['data'][$name];

    if (count($info['list']) > 0)
    {
        // echo "<div style='display: table-cell;'>";
        echo '<div>'; // style='display: table-cell;'>";
        echo sprintf('<h2>%d %s</h2>',count($info['list']),$name);
        echo Radix::block($info['view'],array('list'=>$info['list'],'opts'=>array('head'=>true)));
        echo '</div>';
        // Radix::dump($info);
    }
}
