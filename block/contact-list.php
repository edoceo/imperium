<?php
/**
	@file
	@brief Contact List Element

	Draws the standard table of Contacts

	@package Edoceo Imperium
*/

echo '<table>';
echo '<tr>';
echo '<th>Name</th>';
echo '<th>Phone</th>';
echo '<th>Email</th>';
echo '</tr>';

foreach ($data['list'] as $item) {
    echo '<tr class="rero ' . $item['kind'] . '">';
    // Show Contact, if none show Name
    echo '<td>';
    switch (strtolower($item['kind'])) {
    case 'company':
        echo '<i class="fa fa-building"></i> ';
        break;
    case 'vendor':
        echo '<i class="fa fa-truck"></i> ';
        break;
    case 'person':
    default:
        echo '<i class="fa fa-user"></i> ';
    }
    echo '<a href="' . radix::link('/contact/view?c=' . $item['id']) . '">' . html($item['name']) . '</a>';
    echo '</td>';

    echo '<td>' . radix::block('stub-channel',array('kind' => ContactChannel::PHONE, 'data'=>$item['phone'])) . '</td>';
    echo '<td>' . radix::block('stub-channel',array('kind' => ContactChannel::EMAIL, 'data'=>$item['email'])) . '</td>';
    echo '</tr>';
}

echo '</table>';
