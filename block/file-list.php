<?php
/**
    @file
    @brief File List Element: Draws a table-list of Notes

    @note used to take a Page parameter for a Create Link, not used now
*/

if (empty($data)) {
    return(0);
}
if (count($data)==0) {
    return(0);
}

echo '<h2>Files';
// echo ' <span class="s">[';
// echo ' <a onclick="$(\'#note-list\').toggle(); return false;">View</a> ';
// echo ' <a onclick="$(\'#note-edit\').load(\'' . $this->page . '\').focus(); return false;" href="' . $this->page . '">';
// echo img('/silk/1.3/note_add.png','Add Note');
// echo '</a> ';
// echo ']</span>';
echo '</h2>';
echo '<div id="file-edit"></div>';
echo '<div id="file-list">';

echo '<table>';
echo '<tr><th>Icon</th><th>Name</th><th>Type</th><th>Size</th><th>&nbsp;</th></tr>';

foreach ($data as $f) {

    $mime = Base_File::mimeInfo($f['kind']);

    echo '<tr class="rero">';
    echo '<td>' . img($mime['icon'],$mime['note']) . '</td>';
    echo '<td><a href="' . radix::link('/file/view?id=' . $f['id']) . '">' . html($f['name']) . '</a></td>';
    echo '<td class="r">' . html($mime['name']) . '</td>';
    echo '<td class="r">' . html( ImperiumView::niceSize($f['size']) ) . '</td>';
    // echo '<td>' . $this->link('/file/render/' . $f->id,   img('/tango/24x24/actions/edit-find.png','Render') ) . '</td>';
    echo '<td><a href="' . radix::link('/file/download?id=' . $f['id']) . '">' . img('/tango/24x24/actions/document-save.png','Download') . '</td>';
    echo '</tr>';

}

echo '</table>';
