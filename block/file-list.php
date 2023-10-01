<?php
/**
    @file
    @brief File List Element: Draws a table-list of Notes

    @note used to take a Page parameter for a Create Link, not used now
*/

namespace Edoceo\Imperium;

use Radix;

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
// echo '<i class="far fa-plus-square"></i> Add Note';
// echo '</a> ';
// echo ']</span>';
echo '</h2>';
echo '<div id="file-edit"></div>';
echo '<div id="file-list">';

echo '<table class="table table-sm table-striped">';
echo '<thead class="table-dark">';
echo '<tr><th>Icon</th><th>Name</th><th>Type</th><th>Size</th><th>&nbsp;</th></tr>';
echo '</thead>';

foreach ($data as $f) {

    $mime = Base_File::mimeInfo($f['kind']);

    echo '<tr>';
    echo '<td>' . img($mime['icon'], $mime['note']) . '</td>';
    echo '<td><a href="' . Radix::link('/file/view?id=' . $f['id']) . '">' . html($f['name']) . '</a></td>';
    echo '<td class="r">' . html($mime['name']) . '</td>';
    echo '<td class="r">' . html( ImperiumView::niceSize($f['size']) ) . '</td>';
    // echo '<td>' . $this->link('/file/render/' . $f->id, '<i class="fas fa-search"></i> Render' ) . '</td>';
    echo '<td><a href="' . Radix::link('/file/download?id=' . $f['id']) . '"><i class="fas fa-save"></i> Download</td>';
    echo '</tr>';

}

echo '</table>';
