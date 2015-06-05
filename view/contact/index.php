<?php
/**
    @file
    @brief Contacts Index View
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

// if (empty($this->Page)) {
//     return(0);
// }

// @todo remove this after the Controller starts handling Sort, etc
if (empty($this->Sort)) {
  $this->Sort = 'name';
}

// Output
// $page_link = $this->paginationControl($this->Page,'All','../elements/page-control.phtml');
// echo $page_link;

// First Character Index
// @todo Would be cool to highlight
//$a = empty($_GET['a']) ? null : $_GET['a'];
$opts = array('page'=>1,'sort'=>$this->Sort);

$list = array();
if (!empty($this->Char)) {
    $opts['char'] = null;
    $list[] = '<a href="?' . http_build_query($opts) . '">ALL</a>';
}
if ($this->Char == '#') {
    $list[] = '<span class="hi">&nbsp;#&nbsp;</span>';
} else {
    $opts['char'] = '#';
    $list[] = '<a href="?' . http_build_query($opts) . '">&nbsp;#&nbsp;</a>';
}

// Alphabet Link
for ($i=65;$i<=90;$i++) {
  $opts['char'] = chr($i);
  if ($opts['char'] == $this->Char) {
      $list[] = '<span class="hi">&nbsp;' . $opts['char'] . '&nbsp;</span>';
  } else {
      $list[] = '<a href="?' . http_build_query($opts) . '">&nbsp;' . chr($i) . '&nbsp;</a>';
  }
  //$this->link('/contact?' . http_build_query($opts),'&nbsp;' . chr($i) . '&nbsp;') . '&nbsp;';
}

echo '<div class="jump_list">' . implode(' ',$list) . '</div>';

echo Radix::block('contact-list',array('list'=>$this->ContactList));

echo $page_link;

