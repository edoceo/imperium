<?php
/**
    @file
    @brief Views a Note using the note/edit element
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\HTML\Form;

require_once('markdown.php');

$this->Note = new Base_Note($_GET['id']);
$_ENV['title'] = $this->Note['name'];

echo '<div class="markdown" style="background:#e0e0e0;border:2px inset #666;padding:8px;">';
echo markdown($this->Note['data']);
echo '</div>';

echo '<form action="' . Radix::link('/note/save') . '" id="note-edit-form" method="post">';
echo '<div class="cmd">';
echo Form::hidden('id', $this->Note['id']);
echo Form::hidden('link', $this->Note['link']);
echo '<input class="exec" name="a" type="submit" value="Edit">';
echo '</div>';
echo '</form>';

// Radix::dump($this->Note);