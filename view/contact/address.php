<?php
/**
	Display form for Contact Address
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Layout;
use Edoceo\Radix\HTML\Form;

Layout::addScript('<script src="//maps.google.com/maps/api/js?sensor=true" type="text/javascript"></script>');

$kind_input = Form::select('kind', $this->ContactAddress['kind'], ContactAddress::$kind_list, array('class' => 'form-control'));
$opts = array('class' => 'form-control');

?>

<form method="post">
<div class="container">
<div class="row">
<div class="col-md-6">
<?php
echo "<table>";
echo "<tr><td class='b r'>Kind:</td><td>$kind_input</td></tr>";
echo "<tr><td class='b r'>Recipient:</td><td>".Form::text('rcpt',$this->ContactAddress['rcpt'], $opts)."</td></tr>";
echo "<tr><td class='b r'>Address</td><td>".Form::textarea('address',$this->ContactAddress['address'], $opts)."</td></tr>";
echo "<tr><td class='b r'>City:</td><td>" . Form::text('city',$this->ContactAddress['city'], $opts)."</td></tr>";
echo "<tr><td class='b r'>State:</td><td>" . Form::text('state',$this->ContactAddress['state'], $opts)."</td></tr>";
echo "<tr><td class='b r'>Postal:</td><td>" . Form::text('post_code',$this->ContactAddress['post_code'], $opts)."</td></tr>";
echo "<tr><td class='b r'>Country:</td><td>".Form::text('country',$this->ContactAddress['country'], $opts)."</td></tr>";
echo '</table>';
?>
</div>
<div class="col-md-6">
	<div id="gmap" style="height:320px; width:100%;"></div>
</div>
</div>

<?php

echo '<div class="form-actions">';
echo Form::hidden('id',$this->ContactAddress['id']);
echo Form::hidden('contact_id',$this->ContactAddress['contact_id']);
echo ' ' . Form::button('a','Save', array('class' => 'btn btn-primary'));
echo ' ' . Form::button('a','Validate', array('class' => 'btn btn-secondary'));
if ($this->ContactAddress['id']) {
	echo ' ' . Form::button('a', 'Delete', array('class' => 'btn btn-danger')); // $this->formSubmit('a','Delete');
}
echo '</div>';

echo '</div>';
echo '</form>';

$addr = $this->ContactAddress->address . ' ' . $this->ContactAddress->city . ', ' . $this->ContactAddress->state . ' ' . $this->ContactAddress->post_code . ' ' . $this->ContactAddress->country;
$addr = preg_replace('/\s+/ms',' ',$addr);

ob_start();
?>
<script>

function map_lat_lng(x)
{
	if (typeof(x) != 'string') {
		alert('bad:' + typeof(x));
		return false;
	}
	if (x.length == 0) {
		return;
	}
	x = x.toString();
	if (x.match(/([\d\.\-],[\d\.\-]+)/)) {
		return x.split(",");
	}
	return x;
}

function map_mark(addr,name,html)
{
	if (typeof(addr) == 'undefined') {
		return;
	}
	if (addr.length == 0) {
		return;
	}

	var m = new google.maps.Marker({
		draggable:false,
		flat:true,
		icon:'//gcdn.org/silk/1.3/star.png',
		map:map,
		title:name
	});
	var mi = new google.maps.MarkerImage();
	// mi.size = new google.maps.Size(33,33);
	// mi.origin = new google.maps.Point(0,0);
	// mi.anchor = new google.maps.Point(0,0);
	mi.url = '//gcdn.org/silk/1.3/star.png';
	// mi.anchor = new google.maps.Point(0,0);

	m.icon = mi;

	if (html) {
		var iw = new google.maps.InfoWindow({content:html});
		google.maps.event.addListener(m, 'click', function() { iw.open(map,m); });
		google.maps.event.addListener(m, 'dragend', function(e) { iw.setContent(e.toString()); iw.open(map,m); });
	}

	var p = map_lat_lng(addr);
	switch (typeof(p)) {
	case 'object':
		p = new google.maps.LatLng(p[0],p[1]);
		m.setPosition(p);
		map.setCenter(p);
		break;
	case 'string':
		gc = new google.maps.Geocoder();
		if (p.match(/^[\d\.\,\-]+$/)) {
			return; // Ignore Lat,Lng Pattern
		}
		gc.geocode( { address:p }, function(res, ret) {
			if (ret === google.maps.GeocoderStatus.OK) {
				m.setPosition(res[0].geometry.location);
				map.setCenter(res[0].geometry.location);
			}
		});
		break;
	default:
		alert('what type?');
	}

	if (typeof(m) == 'undefined') {
		alert('markAddress returning undefined!');
	}

}

(function() {

	var opt = {
		disableDefaultUI:false,
		disableDoubleClickZoom:true,
		keyboardShortcuts:false,
		mapTypeId:google.maps.MapTypeId.ROADMAP,
		streetViewControl:false,
		zoom:16
	};

	map = new google.maps.Map(document.getElementById('gmap'),opt);
	map_mark("<?php echo $addr; ?>",'Name of Address','HTML of Address');

})();
</script>
<?php
$code = ob_get_clean();
Layout::addScript($code);

// History
//$args = array(
//	'ajax' => true,
//	'list' => $this->ContactAddress->getHistory()
//);
//echo $this->partial('../elements/diff-list.phtml',$args);
