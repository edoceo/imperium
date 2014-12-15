<?php
/**

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\Session;

Session::kill();
Radix::redirect('/auth/sign-in');