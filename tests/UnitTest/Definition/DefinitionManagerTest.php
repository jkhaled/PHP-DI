<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition;

use DI\Definition\AliasDefinition;
use DI\Definition\DefinitionManager;
use DI\Definition\ValueDefinition;
use Doctrine\Common\Cache\ArrayCache;

/**
 * Test class for DefinitionManager
 *
 * @covers \DI\Definition\DefinitionManager
 */
class DefinitionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldGetFromCache()
    {
        $definitionManager = new DefinitionManager();

        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue('foo'));

        $definitionManager->setCache($cache);

        $this->assertEquals($cache, $definitionManager->getCache());

        $this->assertEquals('foo', $definitionManager->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function shouldSaveToCache()
    {
        $definitionManager = new DefinitionManager();

        $cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(false));
        $cache->expects($this->once())
            ->method('save');

        $definitionManager->setCache($cache);

        $this->assertNull($definitionManager->getDefinition('foo'));
    }

    /**
     * Tests that the given definition source is chained to the ArraySource and used.
     */
    public function testDefinitionSource()
    {
        $definition = $this->getMockForAbstractClass('DI\Definition\CacheableDefinition');

        $source = $this->getMockForAbstractClass('DI\Definition\Source\DefinitionSource');
        $source->expects($this->once())
            ->method('getDefinition')
            ->with('foo')
            ->will($this->returnValue($definition));

        $definitionManager = new DefinitionManager($source);

        $this->assertSame($definition, $definitionManager->getDefinition('foo'));
    }

    public function testAddDefinition()
    {
        $definitionManager = new DefinitionManager();
        $valueDefinition = new ValueDefinition('foo', 'bar');

        $definitionManager->addDefinition($valueDefinition);

        $this->assertSame($valueDefinition, $definitionManager->getDefinition('foo'));
    }

    /**
     * @test
     * @see https://github.com/mnapoli/PHP-DI/issues/222
     */
    public function testAddDefinitionShouldClearCachedDefinition()
    {
        $definitionManager = new DefinitionManager();
        $definitionManager->setCache(new ArrayCache());

        $firstDefinition = new AliasDefinition('foo', 'bar');
        $secondDefinition = new AliasDefinition('foo', 'bam');

        $definitionManager->addDefinition($firstDefinition);
        $this->assertSame($firstDefinition, $definitionManager->getDefinition('foo'));

        $definitionManager->addDefinition($secondDefinition);
        $this->assertSame($secondDefinition, $definitionManager->getDefinition('foo'));
    }
}
