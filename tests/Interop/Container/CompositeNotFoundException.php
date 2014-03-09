<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Interop\Container;

use Interop\Container\Exception\NotFoundException;
/**
 * No entry was found in the container.
 */
class CompositeNotFoundException extends \Exception implements NotFoundException
{
}
