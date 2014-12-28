<?php
/**
    @file
    @brief Interface to the Search Options
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\DB\SQL;

// Collect Search Term
$term = null;
if (!empty($_GET['q'])) {
	$term = trim($_GET['q']);
} else {
	$term = $_SESSION['search-term'];
}
if (strlen($term)==0) {
	$_ENV['title'] = array('Search','No Term Submitted');
	unset($_SESSION['search-term']);
	return(0);
}

$_SESSION['search-term'] = $term;

// Check for ID Specific Queries
switch (strtok(strtolower($term),':')) {
case 'co':
	Radix::redirect('/contact/view?c=' . strtok(':'));
case 'iv':
	Radix::redirect('/invoice/view?i=' . strtok(':'));
case 'je':
	Radix::redirect('/account/transaction?id=' . strtok(':'));
case 'wo':
	Radix::redirect('/workorder/view?w=' . strtok(':'));
}

// PostgreSQL Full Text Search
$sql = 'SELECT link_to,link_id,name, ';
$sql.= ' ts_headline(ft,plainto_tsquery(?)) as snip, ';
$sql.= ' ts_rank_cd(tv,plainto_tsquery(?)) as sort ';
$sql.= ' FROM full_text ';
$sql.= ' WHERE tv @@ plainto_tsquery(?) ';
$sql.= ' ORDER BY sort DESC, name';
$arg = array(
	$term,
	$term,
	$term,
);

$res = SQL::fetch_all($sql, $arg);
$c = count($res);

$_ENV['title'] = array('Search',$term, ($c==1 ? '1 result' : $c . ' results') );

if (empty($term)) {
    echo '<p class="info">No search performed</p>';
    return(0);
}

if (count($res) == 0) {
    echo '<p class="info">No results found for: ' . $term . '</p>';
    return(0);
}

echo '<dl>';

foreach ($res as $k=>$item) {

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
