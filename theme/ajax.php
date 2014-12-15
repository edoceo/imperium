<?php
/**
    @file
    @brief AJAX output
*/

namespace Edoceo\Imperium;

if (is_array($_ENV['title'])) {
    $_ENV['title'] = implode(' :: ',$_ENV['title']);
}

$t = html($_ENV['title']);

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head><title>$t</title></head>\n";
echo '<body>';

if (!empty($t)) echo "<h2>$t</h2>";

echo $this->body;

echo '</body></html>';
