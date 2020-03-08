<?php
/**

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\Filter;

switch ($_POST['a']) {
case 'import':
	_contact_import();
	exit;
	break;
case 'review':
	// Save the Data, Allow to Review Each One ...
	$_SESSION['contact-import'] = $_POST;
	Radix::redirect('/contact/import?a=review');
	break;
}


function _contact_import()
{
	$new_list = array();

	$col_map = array();

	$col_max = 0;
	$row_max = 0;


	$key_list = array_keys($_POST);
	foreach ($key_list as $key) {
		if (preg_match('/^col(\d+)$/', $key, $m)) {
			$col_map[ intval($m[1]) ] = $_POST[$key];
			$col_max = max($col_max, $m[1]);
		} elseif (preg_matcH('/^r(\d+)c(\d+)$/', $key, $m)) {
			$row_max = max($row_max, $m[1]);
		}
	}

	// Radix::dump($col_map);
	// Radix::dump($col_max);
	// Radix::dump($row_max);
	// exit;

	for ($row_idx=0; $row_idx<=$row_max; $row_idx++) {

		$C = new Contact();
		$C['auth_user_id'] = $_SESSION['uid'];
		$C['kind'] = 'contact';

		$AH = new ContactAddress(); // Home
		$AH['auth_user_id'] = $_SESSION['uid'];
		$AH['kind'] = 'home';

		$AM = new ContactAddress(); // Mail
		$AM['auth_user_id'] = $_SESSION['uid'];
		$AM['kind'] = 'mail';

		$AW = new ContactAddress(); // Work
		$AW['auth_user_id'] = $_SESSION['uid'];
		$AW['kind'] = 'work';

		$val_set = 0;

		for ($col_idx=0; $col_idx<=$col_max; $col_idx++) {

			$val = trim($_POST[sprintf('r%dc%d', $row_idx, $col_idx)]);
			if (empty($val)) {
				continue;
			}

			switch ($col_map[$col_idx]) {
			case 'address_home':
				$AH['address'] .= "\n$val";
				$val_set++;
				break;
			case 'address_mail':
				$AM['address'] .= "\n$val";
				$val_set++;
				break;
			case 'address_work':
				$AW['address'] .= "\n$val";
				$val_set++;
				break;
			case 'company':
				$C['company'] = $val;
				$val_set++;
				break;
			case 'contact':
				$C['contact'] = $val;
				$val_set++;
				break;
			case 'email':
				$C['email'] = Filter::email($val);
				$val_set++;
				break;
			case 'phone':
				$C['phone'] = preg_replace('/[^\d+]/', null, $val);
				$val_set++;
				break;
			case 'ignore':
				break;
			case 'uri':
				$C['url'] = Filter::uri($val);
				$val_set++;
				break;
			default:
				if (preg_match('/meta:(.+)$/', $col_map[$col_idx], $m)) {
					$C->setMeta($m[1], $val);
				} else {
					$C['note'] .= "{$col_map[$col_idx]}: $val\n";
				}
				// die("Not Handled: " . $col_map[$col_idx]);
			}
		}

		if (0 == $val_set) {
			continue;
		}

		if (empty($C['contact']) && !empty($C['company'])) {
			$C['kind'] = 'company';
		}

		// Radix::dump($val_set);
		// Check Existing?
		// $chk = Contact::find($C['contact']);

		Radix::dump($C);

		SQL::query('begin');
		$C->save();

		$AH['address'] = trim($AH['address']);
		if (!empty($AH['address'])) {
			$AH['contact_id'] = $C['id'];
			Radix::dump($AH);
			$AH->save();
		}
		$AM['address'] = trim($AM['address']);
		if (!empty($AM['address'])) {
			$AM['contact_id'] = $C['id'];
			Radix::dump($AM);
			$AM->save();
		}
		$AW['address'] = trim($AW['address']);
		if (!empty($AW['address'])) {
			$AW['contact_id'] = $C['id'];
			Radix::dump($AW);
			$AW->save();
		}

		$new_list[] = $C['id'];
	}

	$_SESSION['contact-review-list'] = $new_list;

	Session::flash('info', sprintf('Imported %d Contacts', count($new_list)));
	Radix::redirect('/contact');

	// Radix::dump($C);
	// Radix::dump($AH);
	// Radix::dump($AM);
	// Radix::dump($AW);
	// Radix::dump($_POST);
}