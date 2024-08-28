<?php

namespace Twig\Tests\Node;

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Twig\Node\NameDeprecation;
use Twig\Node\Node;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class NodeTest extends TestCase
{
    use ExpectDeprecationTrait;

    public function testToString()
    {
        // callable is not a supported type for a Node attribute, but Drupal uses some apparently
        $node = new Node([], ['value' => function () { return '1'; }], 1);

        $this->assertEquals(<<<EOF
Twig\Node\Node
  attributes:
    value: \Closure
EOF
            , (string) $node
        );
    }

    public function testToStringWithTwigCallables()
    {
        $node = new Node([], [
            'function' => new TwigFunction('a_function'),
            'filter' => new TwigFilter('a_filter'),
            'test' => new TwigTest('a_test'),
        ], 1);

        $this->assertEquals(<<<EOF
Twig\Node\Node
  attributes:
    function: Twig\TwigFunction(a_function)
    filter: Twig\TwigFilter(a_filter)
    test: Twig\TwigTest(a_test)
EOF
            , (string) $node);
    }

    public function testToStringWithTag()
    {
        $node = new Node([], [], 1);
        $node->setNodeTag('tag');

        $this->assertEquals(<<<EOF
Twig\Node\Node
  tag: tag
EOF
            , (string) $node);
    }

    public function testAttributeDeprecationIgnore()
    {
        $node = new Node([], ['foo' => false]);
        $node->deprecateAttribute('foo', new NameDeprecation('foo/bar', '2.0', 'bar'));

        $this->assertFalse($node->getAttribute('foo', false));
    }

    /**
     * @group legacy
     */
    public function testAttributeDeprecationWithoutAlternative()
    {
        $node = new Node([], ['foo' => false]);
        $node->deprecateAttribute('foo', new NameDeprecation('foo/bar', '2.0'));

        $this->expectDeprecation('Since foo/bar 2.0: Getting attribute "foo" on a "Twig\Node\Node" class is deprecated.');
        $this->assertFalse($node->getAttribute('foo'));
    }

    /**
     * @group legacy
     */
    public function testAttributeDeprecationWithAlternative()
    {
        $node = new Node([], ['foo' => false]);
        $node->deprecateAttribute('foo', new NameDeprecation('foo/bar', '2.0', 'bar'));

        $this->expectDeprecation('Since foo/bar 2.0: Getting attribute "foo" on a "Twig\Node\Node" class is deprecated, get the "bar" attribute instead.');
        $this->assertFalse($node->getAttribute('foo'));
    }

    public function testNodeDeprecationIgnore()
    {
        $node = new Node(['foo' => $foo = new Node()], []);
        $node->deprecateNode('foo', new NameDeprecation('foo/bar', '2.0'));

        $this->assertSame($foo, $node->getNode('foo', false));
    }

    /**
     * @group legacy
     */
    public function testNodeDeprecationWithoutAlternative()
    {
        $node = new Node(['foo' => $foo = new Node()], []);
        $node->deprecateNode('foo', new NameDeprecation('foo/bar', '2.0'));

        $this->expectDeprecation('Since foo/bar 2.0: Getting node "foo" on a "Twig\Node\Node" class is deprecated.');
        $this->assertSame($foo, $node->getNode('foo'));
    }

    /**
     * @group legacy
     */
    public function testNodeAttributeDeprecationWithAlternative()
    {
        $node = new Node(['foo' => $foo = new Node()], []);
        $node->deprecateNode('foo', new NameDeprecation('foo/bar', '2.0', 'bar'));

        $this->expectDeprecation('Since foo/bar 2.0: Getting node "foo" on a "Twig\Node\Node" class is deprecated, get the "bar" node instead.');
        $this->assertSame($foo, $node->getNode('foo'));
    }
}
