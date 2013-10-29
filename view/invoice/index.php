<?php
/**
    @file
    @brief Invoice Index View Shows Paginated Results of the Index View
*/

//if (empty($this->Page)) {
//    return(0);
//}
//
//echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');

echo radix::block('invoice-list', array('list' => $this->list));

// echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');
