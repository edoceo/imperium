<?php
/**
	Contact/Address Controller
*/

use Edoceo\Radix;
use Edoceo\Radix\Session;

switch ($_POST['a']) {
case 'delete':

	$ca = new ContactAddress($_POST['id']);
	$ca->delete();

	Session::flash('info', 'Contact Address ' . $ca['kind'] . ' was deleted');
	Radix::redirect('/contact/view?c=' . $ca['contact_id']);

	break;

case 'save':

	$ca = new ContactAddress($_POST['id']);

    // Copy from Post to Address
    foreach (array('contact_id','kind','rcpt','address','city','state','post_code','country') as $x) {
		$ca[$x] = trim($_POST[$x]);
    }

	$ca->save();

	Session::flash('info', 'Contact Address ' . $ca['kind'] . ' saved');
	Radix::redirect('/contact/view?c=' . $ca['contact_id']);

	break;

case 'validate':
	// Build Address
	//$address = null;
	//foreach (array('address','city','state','post_code','country') as $x) {
	//  $address.= ' ' . $this->_request->getPost($x);
	//}
	// Lookup Address
	$map_url = 'http://maps.google.com/maps/geo?output=xml&key=' . $_ENV['google']['map_key'];
	$http = new Zend_Http_Client($map_url . '&q=' . urlencode($ca));
	$http->setCookieJar(true);
	$http->setHeaders('Accept','application/xml');
	$http->setHeaders('User-Agent','Edoceo Imperium Google GeoCoder 0.2');
	$page = $http->request();

	// Parse Response
	$xml = simplexml_load_string($page->getBody());

	if (intval($xml->Response->Status->code) == 200) {
	// Success - Parse Address
	$ad = $xml->Response->Placemark->AddressDetails->Country;
	$ca->address = (string)$ad->AdministrativeArea->Locality->Thoroughfare->ThoroughfareName;
	$ca->city = (string)$ad->AdministrativeArea->Locality->LocalityName;
	$ca->state = (string)$ad->AdministrativeArea->AdministrativeAreaName;
	$ca->post_code = (string)$ad->AdministrativeArea->Locality->PostalCode->PostalCodeNumber;
	$ca->country = (string)$ad->CountryNameCode;
	// Coordinates
	$buf = explode(',',(string)$xml->Response->Placemark->Point->coordinates);
	$ca->lat = $buf[1];
	$ca->lon = $buf[0];
	//  return ;
	//} elseif ($status == 620) {
	//  // sent geocodes too fast
	//  $delay += 100000;
	//} else {
	//  // failure to geocode
	//  echo "Address " . $address . " failed to geocoded. ";
	//  echo "Received status " . $status . "\n";
	}
	$ss->msg = 'Contact Address #' . $ca->kind . ' was validated &amp; Saved';
	$ca->save();

	break;
}
