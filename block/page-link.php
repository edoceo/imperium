<?php
/**
	@file
	@brief Show Pagination Options

    @see http://developer.yahoo.com/ypatterns/pattern.php?pattern=searchpagination

*/

$page_size = $data['size'];

$page_cur = max(0, $data['cur']);
$max = max(0, $data['max']);

$page_min = 1;
$page_max = ceil($max / $page_size);
if ($page_max <= 0) {
	return null;
}

$list = array();
if ($page_cur > $page_min) {
	$list[] = '<a href="?' . http_build_query(array_merge($_GET,array('page'=>($page_cur-1)))) . '">&laquo;&laquo;</a>';
} else {
	$list[] = '<span class="no">&laquo;&laquo;</span>';
}

for ($page_idx = 1; $page_idx <= $page_max; $page_idx++) {

	if ($page_idx < $page_cur) {
		$list[] = '<a href="?' . http_build_query(array_merge($_GET,array('page'=>$page_idx))) . '">&laquo;' . $page_idx . '&laquo;</a>';
	} elseif ($page_idx == $page_cur) {
		$list[] = '<a class="hi" href="?' . http_build_query(array_merge($_GET,array('page'=>$page_idx))) .'">&nbsp;' . $page_idx .'&nbsp;</a>';
	} else {
		$list[] = '<a href="?' . http_build_query(array_merge($_GET,array('page'=>$page_idx))) . '">&raquo;' . $page_idx . '&raquo;</a>';
	}
}

// Draw Next Buttons
if ($page_cur < $page_max) {
	$list[] = '<a href="?' . http_build_query(array_merge($_GET,array('page'=>$page_cur+1))) . '">&raquo;&raquo;</a>';
} else {
	$list[] = '<span class="no">&raquo;&raquo;</span>';
}

    // Previous page link 
    // if (isset($this->previous)) { 
    //     $list[] = '<a href="?' . http_build_query(array_merge($_GET,array('page'=>(intval($_GET['page'])-1)))) . '">&laquo;&laquo;</a>';
    // } else {
    //     $list[] = '<span class="no">&laquo;&laquo;</span>';
    // }

    // Numbered page links
    // foreach ($this->pagesInRange as $page) { 
    //     if ($page == $this->current) {
    //         $list[] = '<span class="hi">&nbsp;' . $page . '&nbsp;</span>';
    //     } else {
    //         $list[] = '<a href="?' . http_build_query(array_merge($_GET,array('page'=>$page))) .'">&nbsp;' . $page .'&nbsp;</a>';
    //     }
    // }

echo '<div class="page_list">';
echo '<span style="float:right;">Page ' . $page_cur . '/' . $page_max . '</span>';
echo implode('|',$list);
echo '</div>';