<?php
/**
 *
 */

use Edoceo\Radix;
use Edoceo\Radix\Filter;
use Edoceo\Radix\HTML\Form;

if (empty($this->Contact['parent_id'])) {

	$x = array(
		'controller' => 'contact',
		'action' => 'create',
		'parent' => $this->Contact['id'],
	);
	$url = Radix::link('/contact/new?parent=' . $this->Contact['id']); // ($x,'default',true);

	echo '<section>';
	echo '<h2 id="sub-contacts"><a href="' . $url . '"><i class="fas fa-users"></i> Sub-Contacts</a></h2>';

	if (count($this->ContactList)) {
		echo '<table class="table">';
		foreach ($this->ContactList as $item) {
			echo '<tr>';
			echo '<td><a href="' . Radix::link('/contact/view?c=' . $item['id']) . '">' . html($item['name']) . '</a></td>';
			// echo '<td>' . Radix::block('stub-channel', array('data'=>$item['phone'])) . '</td>';
			// echo '<td>' . Radix::block('stub-channel', array('data'=>$item['email'])) . '</td>';
			echo '<td>' . html($item['phone']) . '</td>';
			echo '<td>' . html($item['email']) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	echo '</section>';

	echo '<hr>';
}
