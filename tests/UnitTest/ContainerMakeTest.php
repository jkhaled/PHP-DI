<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest;

use DI\ContainerBuilder;
use stdClass;

/**
 * Test class for Container
 *
 * @covers \DI\Container
 */
class ContainerMakeTest extends \PHPUnit_Framework_TestCase
{
    public function testSetMake()
    {
        $container = ContainerBuilder::buildDevContainer();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->make('key'));
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testMakeNotFound()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make('key');
    }

    /**
     * @coversNothing
     */
    public function testClosureIsNotResolved()
    {
        $closure = function () {
            return 'hello';
        };
        $container = ContainerBuilder::buildDevContainer();
        $container->set('key', $closure);
        $this->assertSame($closure, $container->make('key'));
    }

    public function testMakeWithClassName()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertInstanceOf('stdClass', $container->make('stdClass'));
    }

    /**
     * Checks that the singleton scope is ignored.
     */
    public function testGetWithSingletonScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Without @Injectable annotation => default is Singleton
        $instance1 = $container->make('stdClass');
        $instance2 = $container->make('stdClass');
        $this->assertNotSame($instance1, $instance2);
        // With @Injectable(scope="singleton") annotation
        $instance3 = $container->make('DI\Test\UnitTest\Fixtures\Singleton');
        $instance4 = $container->make('DI\Test\UnitTest\Fixtures\Singleton');
        $this->assertNotSame($instance3, $instance4);
    }

    public function testMakeWithPrototypeScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        // With @Injectable(scope="prototype") annotation
        $instance1 = $container->make('DI\Test\UnitTest\Fixtures\Prototype');
        $instance2 = $container->make('DI\Test\UnitTest\Fixtures\Prototype');
        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * @expectedException \DI\Definition\Exception\DefinitionException
     * @expectedExceptionMessage Error while reading @Injectable on DI\Test\UnitTest\Fixtures\InvalidScope: Value 'foobar' is not part of the enum DI\Scope
     * @coversNothing
     */
    public function testMakeWithInvalidScope()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make('DI\Test\UnitTest\Fixtures\InvalidScope');
    }

    /**
     * Tests if instantiation unlock works. We should be able to create two instances of the same class.
     */
    public function testCircularDependencies()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make('DI\Test\UnitTest\Fixtures\Prototype');
        $container->make('DI\Test\UnitTest\Fixtures\Prototype');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'DI\Test\UnitTest\Fixtures\Class1CircularDependencies'
     */
    public function testCircularDependencyException()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make('DI\Test\UnitTest\Fixtures\Class1CircularDependencies');
    }

    /**
     * @expectedException \DI\DependencyException
     * @expectedExceptionMessage Circular dependency detected while trying to resolve entry 'foo'
     */
    public function testCircularDependencyExceptionWithAlias()
    {
        $container = ContainerBuilder::buildDevContainer();
        // Alias to itself -> infinite recursive loop
        $container->set('foo', \DI\link('foo'));
        $container->make('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The name parameter must be of type string
     */
    public function testNonStringParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->make(new stdClass());
    }
}
