<?php
/**
	@file
	@brief Contact List Element

	Draws the standard table of Contacts

	@package Edoceo Imperium
*/

echo '<table>';
echo '<tr>';
echo '<th>&nbsp;</th>';
echo '<th>Name</th>';
echo '<th>Phone</th>';
echo '<th>Email</th>';
echo '</tr>';

foreach ($data['list'] as $item) {
    echo '<tr class="rero ' . $item['kind'] . '">';
    // Show Contact, if none show Name
    switch (strtolower($item['kind'])) {
    case 'company':
        echo '<td>' . img('/silk/1.3/building.png',$this['kind']) . '</td>';
        break;
    case 'person':
        echo '<td>' . img('/silk/1.3/user_green.png',$this['kind']) . '</td>';
        break;
    case 'vendor':
        echo '<td>' . img('/silk/1.3/lorry.png',$this['kind']) . '</td>';
        break;
    default:
        echo '<td>' . $item['kind'] . '</td>';
    }
    echo '<td><a href="' . radix::link('/contact/view?c=' . $item['id']) . '">' . html($item['name']) . '</a></td>';
    echo '<td>' . radix::block('stub-channel',array('kind' => ContactChannel::PHONE, 'data'=>$item['phone'])) . '</td>';
    echo '<td>' . radix::block('stub-channel',array('kind' => ContactChannel::EMAIL, 'data'=>$item['email'])) . '</td>';
    echo '</tr>';
}

echo '</table>';
