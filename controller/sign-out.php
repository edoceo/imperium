<?php
/**

*/

Zend_Registry::set('session',null);
Zend_Session::destroy(true);
radix::redirect('/');
