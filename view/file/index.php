<?php
/**
    @file
    @brief List of Files
*/

// echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');

$FileList = Radix\DB\SQL::fetch('SELECT * FROM base_file WHERE link IS NULL ORDER BY name');


echo radix::block('file-list', $FileList);

// echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');