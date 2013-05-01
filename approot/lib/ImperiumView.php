<?php
/**
	Imperium View Helpers

	@copyright	2008 Edoceo, Inc
  @package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/

class ImperiumView
{
	static function drawSessionMessages() {
		// todo: would love to have an Icon in front of each of these messages
		$s = Zend_Registry::get('session');
		$out = null;
		foreach (array('fail','err','info','msg','warn') as $key) {
			if (empty($s->$key)) {
        continue;
      }
      $buf = $s->$key;
      $out.= '<div>';
      // convert single item array to string
      if ( (is_array($buf)) && (count($buf)==1) ) {
        $buf = $buf[0];
      }
      if (is_array($buf)) {
        $out.= "<ul class='$key'>";
        foreach ($buf as $msg) $out.= "<li>$msg</li>";
        $out.= "</ul>\n";
      } elseif ((is_string($buf)) && (strlen($buf))) {
        $out.= "<p class='$key'>$buf</p>";
      }
      $out.= '</div>';
      unset($s->$key);
		}
		return $out;
	}
    /**
        ImperiumView niceDate
        @param $date - date to get formatted "nicely"
        @return some HTML to display.
    */
	static function niceDate($date)
	{
		// Determines how long ago the date was/is and then how to display it nicely
		// Not smart for determining time factors like time-zone, daylight-standard, leap-year, etc
        $ts_cmp = strtotime($date);
        if (($ts_cmp <= 0) && ($date > 0) ) {
            $ts_cmp = $date;
        }
		$ts_now = time();

		// past or future doesn't matter, just the difference
		$span = abs($ts_now - $ts_cmp);

		$nice = null;
		$full = strftime('%a %b(%m) %d, %Y',$ts_cmp);
        if ($span <= 86400) { // Day
            $nice = 'Today';
            // return strftime('%H:%M',$ts_cmp);
        } elseif ($span <= 172800) { // 2 Days
            $nice = 'Yesterday';
        } elseif ($span <= 604800) { // 7 Days
            $nice = strftime('%a %e',$ts_cmp); // Day ##
        } elseif ($span <= 2592000) { // 30 Days
            $nice = strftime('%b %d',$ts_cmp); // Mon ##
        } elseif ($span <= 31536000) { // 365 Days
            $nice = strftime('%m/%d',$ts_cmp);
        } else {
            $nice = strftime($_ENV['format']['nice_date'],$ts_cmp);
        }
        return '<span title="' . $full . '">' . $nice . '</span>';
	}
    /**
        Formats a Value into wk,dy,h:m:s
    */
  static function niceTime($time)
  {
    if (!is_numeric($time)) {
      $time = strtotime($time);
    }

    $ret = array();
    $set = array(
      'w'=>604800,
      'd'=>86400,
      'h'=>3600,
      'm'=>60,
      's'=>1,
    );
		foreach ($set as $k=>$v) {
			if ($time >= $v) {
        $span = floor($time / $v);
				$time-= ($span * $v); 
				$ret[] = $span . $k;
			}
		}
		return implode('',$ret);
  }
	/**
    ImperiumView niceSize
		Turns an integer to KiB, MiB, GiB, etc
	*/
	static function niceSize($x)
	{
    $ret = $x;
		foreach (array('KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB') as $k) {
			if ($x > 1024) {
				$x = $x / 1024;
				$ret = intval($x) . ' ' . $k;
			} else {
				break;
			}
		}
		return $ret;
	}
  /**
    Add to the Session Most Recently Used
  */
  static function mruAdd($link,$name)
  {
    $ss = Zend_Registry::get('session');

    // Prime $ss->mru
    if (empty($ss->mru)) {
      $ss->mru = array();
    }
    // if it exists, remove and append
    if (array_key_exists($link,$ss->mru)) {
      unset($ss->mru[$link]);
    }
    $ss->mru[$link] = $name;
    if (count($ss->mru) > 5) {
      array_shift($ss->mru);
    }
  }
  /**
  */
  static function mruDraw()
  {
    $mru = self::mruGet();
    if (count($mru) == 0) {
      return;
    }
    $mru = array_reverse($mru);
    $list = array();
    foreach ($mru as $link=>$name) {
      $list[] = '<a href="' . $link . '">' . $name . '</a>';
    }

    $html = '<div id="mru_list">' . implode('&raquo;',$list) . '</div>';

    return $html;
  }
  /**
  */
  static function mruGet()
  {
    $ss = Zend_Registry::get('session');
    if (empty($ss->mru)) {
      $ss->mru = array();
    }

    return $ss->mru;

  }
}
