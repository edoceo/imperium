<?php
/**
	Contact Address List Element

	Displays Table of Contact Addresses

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1013
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

echo '<table>';

foreach ($data['list'] as $item) {

	$link = Radix::link('/contact/address?id=' . $item['id']);

	echo '<tr class="rero">';
	echo '<td><a href="' . $link . '">' . html($item['kind']) . '</a></td>';
	echo '<td><a href="' . $link . '">' . $item->__toString() . '</a></td>';
	echo '<td><a class="fancybox-media" href="http://maps.google.com/maps?q=' . urlencode($item->__toString()) . '">';
	echo img('/tango/24x24/actions/system-search.png','Map');
	echo '</a></td>';
	echo '</tr>';

}

echo '</table>';
