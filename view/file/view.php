<?php
/**
    @file
    @breif View a File Attachment, AJAX aware
*/

$mime = Base_File::mimeInfo($this->File->kind);

// $opts = array(
//     'action'=>$this->link('/file/save'),
//     'enctype'=>'multipart/form-data',
//     'method'=>'post'
// );
// echo $this->form('FileUpload',$opts);

echo '<form action="' . $this->link('/file/save') . '" enctype="multipart/form-data" id="note-edit-form" method="post">';

echo '<table>';
echo '<tr><td class="l">File:</td><td>' . $this->escape($this->File->name) . '</td></tr>';
echo '<tr><td class="l">Type:</td><td>' . img($mime['icon'],'File') . '&nbsp;' . $mime['name'] . '</td></tr>';
echo '<tr><td class="l">Size:</td><td>' . ImperiumView::niceSize($this->File->size) . '</td></tr>';

if (!empty($this->File->link)) {
    echo '<tr>';
    echo '<td class="l">Link To:</td>';
    echo '<td>';
    // @todo this is not elegant /djb 20111013
    if (preg_match('/(contact|invoice|workorder):(\d+)/',$this->File->link,$m)) {
        $page = '/' . $m[1] . '/view?' . substr($m[1],0,1) . '=' . $m[2];
        echo '<a href="' . $this->link($page) . '">' . ucfirst($m[1]) . ' #' . $m[2] . '</a>';
    } else {
        echo $this->File->link;
    }
    echo '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td class="b r">Upload:</td>';
echo '<td>';
echo $this->formFile('file',null,array('size'=>50));
echo '</td>';
echo '</tr>';

echo '</table>';

echo '<div class="cmd">';
echo $this->formHidden('id',$this->File->id);
echo $this->formHidden('link',$this->File->link);
echo '<input name="c" type="submit" value="Upload">';
if (!empty($this->File->id)) {
    echo '<input name="c" type="submit" value="Download">';
    echo '<input name="c" type="submit" value="Delete">';
}
echo '</div>';

echo '</form>';

$mime0 = strtok($this->File->kind,'/');
$mime1 = strtok($this->File->kind);
switch ($mime0) {
case 'audio':
    echo '<h2>Audio Preview</h2>';
    $src = $this->appurl . '/file/download?id=' . $this->File->id;
    echo '<div class="c">';
    echo '<audio controls preload src="' . $src . '"></audio>';
    echo '</div>';
    break;
case 'image':
    echo '<h2>Preview</h2>';
    $src = $this->appurl . '/file/download?a=preview&id=' . $this->File->id;
    echo '<div class="c">';
    echo '<img src="' . $src . '">';
    echo '</div>';
    //Zend_Debug::dump( stream_get_contents($this->File->data) );
default:
    echo '<h2>Preview</h2>';
    $src = $this->appurl . '/file/download?a=preview&id=' . $this->File->id;
    echo '<div class="c">';
    echo '<a href="' . $this->link('/file/download?id=' . $this->File->id) . '">Download</a>';
    echo '</div>';
}




// History
$args = array(
//    'ajax' => true,
    'list' => $this->File->getHistory()
);
echo $this->partial('../elements/diff-list.phtml',$args);

// Zend_Debug::dump($this->File);
