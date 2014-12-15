<?php
/**
	Contact Channel View

	A Form for creating or editing a Contact Channel

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\HTML\Form;

echo '<form method="post">';

echo '<div>';
// echo radix_html_form::hidden('id', $this->ContactChannel['id']);
echo Form::hidden('contact_id', $this->ContactChannel['contact_id']);
echo '</div>';

echo '<table>';
echo "<tr><td class='b r'>Kind:</td><td>" . Form::select('kind', $this->ContactChannel['kind'], ContactChannel::$kind_list) . '</td></tr>';
echo "<tr><td class='b r'>Name:</td><td>" . Form::text('name', $this->ContactChannel['name']) . '</td></tr>';
echo "<tr><td class='b r'>Data:</td><td>" . Form::text('data', $this->ContactChannel['data']) . '</td></tr>';
// echo "<tr><td class='b r'>Primary:</td><td>". radix_html_form::formCheckbox('primary')."</td></tr>";
echo '</table>';

echo '<div class="bf">';
// echo '<button class="exec" name="a" value="apply">Apply</button>';
echo '<button class="exec" name="a" value="save">Save</button>';
if (empty($this->ContactChannel['id'])) {
    echo '<button class="warn" name="a" value="cancel">Cancel</button>';
} else {
    echo '<button class="fail" name="a" value="delete">Delete</button>';
}
echo '</div>';

echo '</form>';

// History
$args = array(
    'list' => $this->ContactChannel->getHistory()
);
echo Radix::block('diff-list', $args);