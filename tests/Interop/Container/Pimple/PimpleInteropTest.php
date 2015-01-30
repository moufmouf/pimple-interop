<?php
namespace Interop\Container\Pimple;

require_once __DIR__.'/../CompositeContainer.php';
require_once __DIR__.'/../CompositeNotFoundException.php';

use Interop\Container\ContainerInterface;
use Interop\Container\ParentAwareContainerInterface;
use Interop\Container\CompositeContainer;

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
		
		$pimple = new PimpleInterop($pimpleParent);
		
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
		
		$pimple = new PimpleInterop($pimpleParent);
		
		$pimple->get('hello');
	}
	
	public function testChainedContainers() {
		$pimpleGrandParent = new PimpleInterop();
		$pimpleGrandParent['hello'] = 'world';
		
		$pimpleParent = new PimpleInterop($pimpleGrandParent);
				
		$pimple = new PimpleInterop($pimpleParent);
		
		$this->assertEquals('world', $pimple->get('hello'));
		$this->assertTrue($pimple->has('hello'));
		$this->assertFalse($pimple->has('world'));
	}

	public function testPriority() {
		// Let's test a "silex" controller scenario.
		// Silex extends Pimple A.
		// My controller is declared in Pimple B.
		// Silex will query container A but result of container B should be returned.
		// My container references an instance in container A.
		// This should work too.
		
		$compositeContainer = new CompositeContainer();
		
		$pimpleA = new PimpleInterop($compositeContainer);
		$pimpleB = new PimpleInterop($compositeContainer);
		
		$pimpleB['controller'] = $pimpleB->share(function ($pimpleB) {
			return ['result' => $pimpleB['dependency']];
		});
		
		$pimpleA['dependency'] = $pimpleA->share(function ($pimpleB) {
			return 'myDependency';
		});
		
		$compositeContainer->addContainer($pimpleA);
		$compositeContainer->addContainer($pimpleB);
		
		// Let's get the controller from the composite container
		$controller = $compositeContainer->get('controller');
		$this->assertEquals('myDependency', $controller['result']);	
		
		// Let's get the controller from PimpleA (that does not declare it)
		$controller = $pimpleA->get('controller');
		$this->assertEquals('myDependency', $controller['result']);
		
	}
	
	/**
	 * @expectedException Interop\Container\Pimple\PimpleNotFoundException
	 */
	public function testStandardCompliantMode() {
		// Let's test a "silex" controller scenario.
		// Silex extends Pimple A.
		// My controller is declared in Pimple B.
		// Silex will query container A but result of container B should be returned.
		// My container references an instance in container A.
		// This should work too.

		$compositeContainer = new CompositeContainer();
		
		$pimpleA = new PimpleInterop($compositeContainer);
		$pimpleB = new PimpleInterop($compositeContainer);
	
		$pimpleB['controller'] = $pimpleB->share(function ($pimpleB) {
			return ['result' => $pimpleB['dependency']];
		});

		$pimpleA['dependency'] = $pimpleA->share(function ($pimpleB) {
			return 'myDependency';
		});


		$compositeContainer->addContainer($pimpleA);
		$compositeContainer->addContainer($pimpleB);

		$pimpleA->setMode(PimpleInterop::MODE_STANDARD_COMPLIANT);

		// Let's get the controller from PimpleA (that does not declare it)
		// This should fail
		$controller = $pimpleA->get('controller');
	}

	public function testStandardCompliantMode2() {
		// Let's test a "silex" controller scenario.
		// Silex extends Pimple A.
		// My controller is declared in Pimple B.
		// Silex will query container A but result of container B should be returned.
		// My container references an instance in container A.
		// This should work too.

		$compositeContainer = new CompositeContainer();

		$pimpleA = new PimpleInterop($compositeContainer);
		$pimpleB = new PimpleInterop($compositeContainer);

		$pimpleA->setMode(PimpleInterop::MODE_STANDARD_COMPLIANT);
		$pimpleB->setMode(PimpleInterop::MODE_STANDARD_COMPLIANT);

		$pimpleB['controller'] = $pimpleB->share(function (ContainerInterface $rootContainer) use ($compositeContainer) {
			return ['result' => $rootContainer->get('dependency')];
		});

		$pimpleA['dependency'] = $pimpleA->share(function (ContainerInterface $rootContainer) {
			return 'myDependency';
		});


		$compositeContainer->addContainer($pimpleA);
		$compositeContainer->addContainer($pimpleB);


		// Let's get the controller from PimpleA (that does not declare it)
		// This should fail
		$controller = $pimpleB->get('controller');
		$this->assertEquals("myDependency", $controller['result']);
	}
	
	
}