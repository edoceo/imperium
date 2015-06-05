<?php
/**
	Capture an Image for this Contact
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

$_ENV['h1'] = $_ENV['title'] = 'Capture Image for ' . $this->Contact['name'];

?>

<form method="post">

<div class="pure-g">
<div class="pure-u-1-2"><div id="camera-preview" style="margin: 8px 0 8px 8px;"></div></div>
<div class="pure-u-1-2">
	<div id="camera-capture" style="margin: 8px 0 8px 8px;"></div>
	<input id="image-data" name="image-data" type="hidden">
</div>
</div>

<div>
<button class="exec" id="exec-camera-preview" name="a" type="button" value="preview">Preview</button>
<!-- <input class="good" id="exec-camera-preview" name="a" type="submit" value="Freeze"> -->
<button class="exec" id="exec-camera-capture" name="a" type="button" value="capture">Capture</button>
<button class="good" name="a" type="submit" value="save">Save</button>
</div>

</form>

<script src="<?= Radix::link('/vendor/webcamjs/webcam.js') ?>"></script>
<script>
$(function() {

	$('#exec-camera-preview').on('click', function() {
		Webcam.set({
			height: 240,
			width: 320,
			dest_height: 240,
			dest_width: 320,
		//	image_format: 'png'
		});
		Webcam.attach('#camera-preview');
	});

	$('#exec-camera-capture').on('click', function() {
		Webcam.snap( function(data_uri) {
			document.getElementById('camera-capture').innerHTML = '<img src="'+data_uri+'"/>';
			document.getElementById('image-data').value = data_uri;
			Webcam.reset();
		});
	});
});
</script>

<?php
Radix::dump($this->Contact);