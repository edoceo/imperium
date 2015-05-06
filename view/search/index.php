<?php
/**
    @file
    @brief Interface to the Search Options
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

switch ($_POST['a']) {
case 'rebuild':
	require_once(APP_ROOT . '/lib/Search.php');
	Search::update();
	Session::flash('info', 'Search Index Updated');
	break;
}

// Collect Search Term
$q = null;
if (!empty($_GET['q'])) {
	$q = trim($_GET['q']);
}
// Search Commands
switch (substr($q, 0, 1)) {
case '!':
	break;
case '#':
	// Something with this Tag or Keyword
	break;
case '$':
	break;
case '*':
	break;
case '@':
	break;
case '~':
	// Promote to RegExp Search
	$q = '.*' . substr($q, 1) . '.*';
	break;
default:
	// Check for ID Specific Queries
	switch (strtok(strtolower($q),':')) {
	case 'co':
		Radix::redirect('/contact/view?c=' . strtok(':'));
	case 'iv':
		Radix::redirect('/invoice/view?i=' . strtok(':'));
	case 'je':
		Radix::redirect('/account/transaction?id=' . strtok(':'));
	case 'wo':
		Radix::redirect('/workorder/view?w=' . strtok(':'));
	}
}

// Last Check
if (strlen($q)==0) {
	$_ENV['title'] = array('Search','No Term Submitted');
	_draw_rebuild_prompt();
	return(0);
}


$idx = 0;

// PostgreSQL Full Text Search
$sql = 'SELECT link_to,link_id,name, ';
$sql.= ' ts_headline(ft,plainto_tsquery(?)) as snip, ';
$sql.= ' ts_rank_cd(tv,plainto_tsquery(?)) as sort ';
$sql.= ' FROM full_text ';
$sql.= ' WHERE tv @@ plainto_tsquery(?) ';
$sql.= ' ORDER BY sort DESC, name';
$arg = array(
	$q,
	$q,
	$q,
);

$res = SQL::fetch_all($sql, $arg);

echo '<dl>';

foreach ($res as $k=>$item) {

	$idx++;

    $html = htmlspecialchars($item['name']);
    $html.= ' <span class="s">(' . sprintf('%01d%%', $item['sort']*100) . ')</span>';

    // Special Case Links
    switch ($item['link_to']) {
    case 'account_journal':
        $link = Radix::link('/account/transaction?id=' . $item['link_id']);
        break;
    case 'base_note':
        $link = Radix::link('/note/view?id=' . $item['link_id']);
        break;
    case 'contact':
        $link = Radix::link('/contact/view?c=' . $item['link_id']);
        break;
    case 'contact_address':
        $link = Radix::link('/contact/address?id=' . $item['link_id']);
        break;
    case 'invoice':
        $link = Radix::link('/invoice/view?i=' . $item['link_id']);
        break;
    case 'invoice_item':
        $link = Radix::link('/invoice/item?id=' . $item['link_id']);
        break;
    case 'workorder':
        $link = Radix::link('/workorder/view?w=' . $item['link_id']);
        break;
    case 'workorder_item':
        $link = Radix::link('/workorder/item?id=' . $item['link_id']);
        break;
    default:
        die(print_r($item));
    }

    echo '<dt><a href="' . $link . '">' . $html . '</a></dt>';
    echo '<dd>' . $item['snip'] . '</dd>';

}

// Additional Search Contacts
$arg = array();
$sql = 'SELECT DISTINCT contact.id, contact.name';
$sql.= ' FROM contact';
$sql.= ' LEFT JOIN contact_address ON contact.id = contact_address.contact_id';
$sql.= ' LEFT JOIN contact_channel ON contact.id = contact_channel.contact_id';
$sql.= ' LEFT JOIN contact_meta ON contact.id = contact_meta.contact_id';
$sql.= ' WHERE';
$sql.= ' contact.name #op# ?';
$arg[] = $q;
$sql.= ' OR contact.email #op# ?';
$arg[] = $q;
$sql.= ' OR contact.phone #op# ?';
$arg[] = $q;
$sql.= ' OR contact_address.address #op# ?';
$arg[] = $q;
$sql.= ' OR contact_channel.data #op# ?';
$arg[] = $q;
$sql.= ' OR contact_meta.val #op# ?';
$arg[] = $q;
$sql.= ' ORDER BY contact.name';

if (preg_match('/[_%]/', $q)) {
	$sql = str_replace('#op#', 'LIKE', $sql);
} elseif (preg_match('/[\.\*\+\?]/', $q)) {
	$sql = str_replace('#op#', '~*', $sql);
} else {
	$sql = str_replace('#op#', '=', $sql);
}

// Radix::dump($sql);
// Radix::dump($arg);
$res = SQL::fetch_all($sql, $arg);
// Radix::dump(SQL::lastError());
// Radix::dump($res);
foreach ($res as $rec) {
	$idx++;
	echo '<dt><a href="' . Radix::link('/contact/view?c=' . $rec['id']) . '">Contact: ' . $rec['name'] . '</a></dt>';
}

echo '</dl>';

if ($idx == 0) {
    _draw_rebuild_prompt();
    return(0);
}

$_ENV['title'] = array('Search', $q, ($idx==1 ? '1 result' : $idx . ' results') );

/**
	Draw the Rebuild Button
*/
function _draw_rebuild_prompt()
{
	echo '<div style="padding:32px;">';
	echo '<form method="post">';
	echo '<button name="a" value="rebuild">Rebuild Index</button>';
	echo '</form>';
	echo '</div>';
}