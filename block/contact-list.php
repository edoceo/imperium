<?php
/**
	@file
	@brief Contact List Element

	Draws the standard table of Contacts

	@package Edoceo Imperium
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

echo '<table class="table table-sm table-striped">';
echo '<thead class="table-dark">';
echo '<tr>';
echo '<th>Name</th>';
echo '<th>Phone</th>';
echo '<th>Email</th>';
echo '</tr>';
echo '</thead>';

foreach ($data['list'] as $item) {
	echo '<tr class="' . $item['kind'] . '">';
	// Show Contact, if none show Name
	echo '<td>';
	echo '<a href="' . Radix::link('/contact/view?c=' . $item['id']) . '">';
	switch (strtolower($item['kind'])) {
	case 'company':
		echo '<i class="fas fa-building"></i> ';
		break;
	case 'vendor':
		echo '<i class="fas fa-truck"></i> ';
		break;
	case 'person':
	default:
		echo '<i class="fas fa-user"></i> ';
	}
	echo html($item['name']);
	echo '</a>';
	echo '</td>';

	echo '<td>' . Radix::block('stub-channel',array('kind' => ContactChannel::PHONE, 'data'=>$item['phone'])) . '</td>';
	echo '<td>' . Radix::block('stub-channel',array('kind' => ContactChannel::EMAIL, 'data'=>$item['email'])) . '</td>';
	echo '</tr>';
}

echo '</table>';
