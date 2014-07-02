<?php
/**
    @file
    @brief Interface to the Search Options
*/

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
	radix::redirect('/contact/view?c=' . strtok(':'));
case 'iv':
	radix::redirect('/invoice/view?i=' . strtok(':'));
case 'je':
	radix::redirect('/account/transaction?id=' . strtok(':'));
case 'wo':
	radix::redirect('/workorder/view?w=' . strtok(':'));
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

$res = radix_db_sql::fetch_all($sql, $arg);
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
        $link = radix::link('/account/transaction?id=' . $item['link_id']);
        break;
    case 'base_note':
        $link = radix::link('/note/view?id=' . $item['link_id']);
        break;
    case 'contact':
        $link = radix::link('/contact/view?c=' . $item['link_id']);
        break;
    case 'contact_address':
        $link = radix::link('/contact/address?id=' . $item['link_id']);
        break;
    case 'invoice':
        $link = radix::link('/invoice/view?i=' . $item['link_id']);
        break;
    case 'invoice_item':
        $link = radix::link('/invoice/item?id=' . $item['link_id']);
        break;
    case 'workorder':
        $link = radix::link('/workorder/view?w=' . $item['link_id']);
        break;
    case 'workorder_item':
        $link = radix::link('/workorder/item?id=' . $item['link_id']);
        break;
    default:
        die(print_r($item));
    }

    echo '<dt><a href="' . $link . '">' . $html . '</a></dt>';
    echo '<dd>' . $item['snip'] . '</dd>';

}
