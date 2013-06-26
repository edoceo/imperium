<?php
/**
    @file
    @brief Star Controller
*/

class StarController extends ImperiumController
{
	/**
		indexAction
	*/
	function indexAction()
	{
        switch (strtolower($_GET['a'])) {
        case 'next':
            $last = strtolower($_GET['c']);
            $next = false;
            foreach ($_ENV['star'] as $k=>$v) {
                if ($next) {
                    $next = $k;
                    break;
                }
                if ($k == $last) $next = true;
            }
            if (empty($next)) $next = 'star0';
            $ret = array(
                'last' => $last,
                'name' => $next,
                'src' => $_ENV['star'][$next],
            );
            // echo json_encode($_ENV['star'][strtolower($_GET['c'])]);
        }
        if ($x = $this->object_from_referer()) {
            $sql = 'UPDATE ' . $x['name'] . ' SET star = ? WHERE id = ?';
            $arg = array($next,$x['id']);
            $this->_d->query($sql,$arg);
        }
        echo json_encode($ret);
        return(true);
	}
	
	function object_from_referer()
	{
        // if (empty($_GET['link'])) {
        //     $url = parse_url($_SERVER['HTTP_REFERER']);
        //     parse_str($url['query'],$arg);
        //     if (!empty($arg['c'])) {
        //         return sprintf('contact:%d',$_GET['c']);
        //     }
        //     if (!empty($arg['i'])) {
        //         return  = sprintf('invoice:%d',$_GET['i']);
        //     }
        //     if (!empty($arg['w'])) {
        //         return  = sprintf('workorder:%d',$_GET['w']);
        //     }
        // }
        if (preg_match('/(contact|invoice|workorder).*(c|i|w)=(\d+)/',$_SERVER['HTTP_REFERER'],$m)) {
            return array(
                'name' => $m[1],
                'id' => $m[3],
            );
        }
	}
}
