<?php
namespace Interop\Container\Pimple;

use Interop\Container\ReadableContainerInterface;
use Interop\Container\ParentAwareContainerInterface;

/**
 * This class extends the Pimple class.
 * It adds compatibility with the container-interop APIs.
 * In particular, it adds the capability for Pimple to accept fallback DI containers.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class PimpleInteropTest extends \PHPUnit_Framework_TestCase {

	public function testGet() {
		$pimple = new PimpleInterop();
		$pimple['hello'] = 'world';
		
		$this->assertEquals('world', $pimple->get('hello'));
	}
	
	public function testHas() {
		$pimple = new PimpleInterop();
		$pimple['hello'] = 'world';
		
		$this->assertTrue($pimple->has('hello'));
		$this->assertFalse($pimple->has('world'));
	}
	
	public function testSetParentContainer() {
		$pimpleParent = new PimpleInterop();
		$pimpleParent['hello'] = 'world';
		
		$pimple = new PimpleInterop();
		$pimple->setParentContainer($pimpleParent);
		
		$this->assertEquals('world', $pimple->get('hello'));
		$this->assertTrue($pimple->has('hello'));
		$this->assertFalse($pimple->has('world'));
	}
	
	/**
	 * @expectedException Interop\Container\Pimple\PimpleNotFoundException
	 */
	public function testException()
	{
		$pimple = new PimpleInterop();
		$pimple->get('hello');
	}

	/**
	 * @expectedException Interop\Container\Pimple\PimpleNotFoundException
	 */
	public function testException2()
	{
		$pimpleParent = new PimpleInterop();
		
		$pimple = new PimpleInterop();
		$pimple->setParentContainer($pimpleParent);
		
		$pimple->get('hello');
	}
	
	public function testChainedContainers() {
		$pimpleGrandParent = new PimpleInterop();
		$pimpleGrandParent['hello'] = 'world';
		
		$pimpleParent = new PimpleInterop();
		$pimpleParent->setParentContainer($pimpleGrandParent);
				
		$pimple = new PimpleInterop();
		$pimple->setParentContainer($pimpleParent);
		
		$this->assertEquals('world', $pimple->get('hello'));
		$this->assertTrue($pimple->has('hello'));
		$this->assertFalse($pimple->has('world'));
	}
	
}