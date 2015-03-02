<?php
/**
	Contact Event
*/

namespace Edoceo\Imperium\Contact;

class Event extends \Edoceo\Imperium\ImperiumBase
{
	protected $_table = 'contact_event';

	function save()
	{
		if (empty($this->_data['contact_id'])) {
			throw new \Exception('CE#20: Invalid Contact ID');
		}

		return parent::save();
	}

}
