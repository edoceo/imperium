<?php
/**
    @file
    @brief List of Files
*/

echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');

echo $this->partial('../elements/file-list.phtml',array('list'=>$this->Page));

echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');