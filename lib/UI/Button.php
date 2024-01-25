<?php
/**
 * Just renders various HTML Buttons
 */

namespace Edoceo\Imperium\UI;

class Button
{
	static function create($url)
	{
		ob_start();
		?>


		<?php
		return ob_get_clean();
	}

	static function save()
	{
		ob_start();
		?>

		<button
			class="btn btn-primary me-2"
			name="a"
			type="submit" value="save">
				Save <i class="fa-regular fa-floppy-disk"></i>
		</button>

		<?php
		return ob_get_clean();
	}

	static function print($url)
	{
		ob_start();
		?>

		<a
			class="btn btn-primary me-2"
			href="<?= $url ?>"
			target="_blank">
				Print <i class="fa-solid fa-print"></i>
		</a>

		<?php
		return ob_get_clean();
	}

}
