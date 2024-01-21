<?php
/**
    @file
    @brief Note List Element: Draws a table-list of Notes

    @param $data('page', 'list') can be an object or an array
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

echo '<h2>';
echo '<a href="' . $data['page'] . '" onclick="$(\'#note-edit\').load(\'' . $data['page'] . '\'); return false;">';
echo '<i class="far fa-file-alt"></i>';
echo ' Notes</a>';
// echo ' <a onclick="$(\'#note-list\').toggle(); return false;">View</a> ';
// echo ' <a onclick="$(\'#note-edit\').load(\'' . $this->page . '\').focus(); return false;" href="' . $this->page . '">';
echo '</h2>';
echo '<div id="note-edit"></div>';

if (empty($data['list'])) {
    return(0);
}
if (count($data['list'])==0) {
    return(0);
}

echo '<div id="note-list">';
$i = 0;
foreach ($data['list'] as $item) {

    //$i++;
    // $item = new Base_Note($x);
    if (empty($item['name'])) {
        $item['name'] = '- Untitled -';
    }

    $date = date('m/d/y',strtotime($item['cts']));
	$link = Radix::link('/note/view?id=' . $item['id']);

    echo '<p>';
    switch ($item['kind']) {
    case 'Conversation':
    	echo '<i class="far fa-comments"></i> ';
    	break;
    case 'Note':
    	echo '<i class="far fa-sticky-note"></i> ';
    	break;
    }

    echo star($item['star']);

    // echo '<td class="b"><a onclick="$(\'#note-edit\').load(\'' . $link . '\'); return false;" href="' . $link . '">' .$item->name . '</a></td>';
    echo '<a href="' . $link . '">' .$item['name'] . '</a>';
    // echo '<td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 80%;">' . substr($item->data,0,32). '</td>';
    // echo "<td class='c'>" . AppHelper::dateNice($date)  . "</td>";
    echo '</p>';
}
echo '</div>';
