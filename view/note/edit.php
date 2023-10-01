<?php
/**
    @file
    @brief Views a Note using the note/edit element
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\HTML\Form;

echo '<form action="' . Radix::link('/note/save') . '" id="note-edit-form" method="post">';
echo '<table class="table">';
echo '<tr>';
// Name
// echo "<td class='b r'>Name:</td><td>" . $this->formText('name',$this->Note->name,array('style'=>'width: 100%')) . '</td>';

// Kind
// @note Conversations cannot be edited
echo '<td class="l">Kind:</td><td>';
if ($this->Note['kind'] != 'Conversation') {
    echo '<select id="note-kind" name="kind">';
    foreach (array('Note','Conversation') as $x) {
        echo '<option ' . ($x==$this->Note['kind']?'selected ':null) . ' value="' , $x . '">' . $x . '</option>';
    }
    echo '</select>';
} else {
    echo $this->Note['kind'];
}
echo '</td>';

// Status
echo "<td class='b r'>Status:</td><td><select name='status'>";
foreach (array('','New','Not Started','In Progress','Completed','Waiting','Deferred') as $x) {
    echo "<option ".($x==$this->Note['status']?'selected ':null)." value='$x'>$x</option>";
}
echo '</select></td>';
echo '</tr>';

if (!empty($this->Note['link'])) {
    echo '<tr>';
    echo '<td class="l">Link To:</td>';
    echo '<td>';
    // @todo this is not elegant /djb 20111013
    if (preg_match('/(contact|invoice|workorder):(\d+)/',$this->Note['link'],$m)) {
        $page = '/' . $m[1] . '/view?' . substr($m[1],0,1) . '=' . $m[2];
        echo '<a href="' . Radix::link($page) . '">' . ucfirst($m[1]) . ' #' . $m[2] . '</a>';
    } else {
        echo $this->Note['link'];
    }
    echo '</td>';
    echo '</tr>';
}

// Tags
//$opts = array('class'=>'tb','div'=>false,'label'=>false,'size'=>64);
//echo "<tr><td class='b r'>Tags:</td><td colspan='3'>" . $this->formText('tags',$this->Note->tags,$opts) . "</td></tr>";

// Previous text of Conversation
if ($this->Note['kind'] == 'Conversation') {
    echo '<tr><td colspan="6"><pre>' . $this->Note['note'] . '</pre></td></tr>';
    $this->Note['note'] = null;
}

echo '</td></tr>';
echo '</table>';

$c = ceil(substr_count($this->Note['note'],"\n")) + 2;
$rows = max(intval($c),12);

echo '<div>';
echo '<textarea id="note-text" name="note" style="height:' . $rows . 'em;padding:0px;width:700px;">';
echo html($this->Note['note']);
echo '</textarea>';
echo '</div>';

echo '<div class="cmd">';
echo Form::hidden('id',$this->Note['id']);
echo Form::hidden('link',$this->Note['link']);
echo '<input class="good" name="a" type="submit" value="Save">';
if (!empty($this->Note['id'])) {
    echo '<input class="fail" name="a" type="submit" value="Delete">';
}
echo '</div>';

echo '</form>';

// History
// $args = array('list' => $this->Note->getHistory());
// echo $this->partial('../elements/diff-list.phtml',$args);
