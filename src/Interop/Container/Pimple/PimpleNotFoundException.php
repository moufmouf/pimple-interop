<?php
namespace Interop\Container\Pimple;

use Interop\Container\Exception\NotFoundException;

/**
 * This exception is thrown when an identifier is passed to Pimple and is not found. 
 *  
 * @author David Négrier <david@mouf-php.com>
 */
class PimpleNotFoundException extends \InvalidArgumentException implements NotFoundException {

}