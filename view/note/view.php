<?php
/**
    @file
    @brief Views a Note using the note/edit element
*/

require_once('markdown.php');

echo '<div class="markdown" style="background:#e0e0e0;border:2px inset #666;padding:8px;">';
echo markdown($this->Note['data']);
echo '</div>';

echo '<form action="' . $this->link('/note/save') . '" id="note-edit-form" method="post">';
echo '<div class="cmd">';
echo radix_html_form::hidden('id',$this->Note['id']);
echo radix_html_form::hidden('link',$this->Note['link']);
echo '<input class="exec" name="a" type="submit" value="Edit">';
echo '</div>';
echo '</form>';
