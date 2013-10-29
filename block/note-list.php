<?php
/**
    @file
    @brief Note List Element: Draws a table-list of Notes

    @param $this->list can be an object or an array
*/

if (empty($data['list'])) {
    return(0);
}
if (count($data['list'])==0) {
    return(0);
}

echo '<h2>Notes';
// echo ' <span class="s">[';
// echo ' <a onclick="$(\'#note-list\').toggle(); return false;">View</a> ';
// echo ' <a onclick="$(\'#note-edit\').load(\'' . $this->page . '\').focus(); return false;" href="' . $this->page . '">';
// echo img('/silk/1.3/note_add.png','Add Note');
// echo '</a> ';
// echo ']</span>';
echo '</h2>';
echo '<div id="note-edit"></div>';
echo '<div id="note-list">';

echo '<table>';
$i = 0;
foreach ($data['list'] as $x) {

    //$i++;
    //Zend_Debug::dump($i);
    $item = new Base_Note($x);

    $date = date('m/d/y',strtotime($item->cts));

    echo '<tr class="rero">';
    echo '<td>';
    echo star($item->star);
    echo '</td>';

    if (empty($item->name)) {
        $item->name = '- Untitled -';
    }

    $link = radix::link('/note/view?id=' . $item->id);
    // echo '<td class="b"><a onclick="$(\'#note-edit\').load(\'' . $link . '\'); return false;" href="' . $link . '">' .$item->name . '</a></td>';
    echo '<td class="b"><a class="fancybox fancybox.ajax" href="' . $link . '">' .$item->name . '</a></td>';
    // echo '<td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 20%;"><strong>';
    // echo '<a href="' . $link . '">' . $item->name . '</a>';
    // echo '</strong></td>';
    // echo '<td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 80%;">' . substr($item->data,0,32). '</td>';
    // echo "<td class='c'>" . AppHelper::dateNice($date)  . "</td>";
    echo '</tr>';
}

echo '</table>';
echo '</div>';
