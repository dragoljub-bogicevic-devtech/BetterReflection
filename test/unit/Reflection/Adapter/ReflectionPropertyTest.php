<?php
declare(strict_types=1);

namespace Roave\BetterReflectionTest\Reflection\Adapter;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass as CoreReflectionClass;
use ReflectionProperty as CoreReflectionProperty;
use Roave\BetterReflection\Reflection\Adapter\Exception\NotImplemented;
use Roave\BetterReflection\Reflection\Adapter\ReflectionClass as ReflectionClassAdapter;
use Roave\BetterReflection\Reflection\Adapter\ReflectionProperty as ReflectionPropertyAdapter;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionProperty as BetterReflectionProperty;
use stdClass;

/**
 * @covers \Roave\BetterReflection\Reflection\Adapter\ReflectionProperty
 */
class ReflectionPropertyTest extends TestCase
{
    public function coreReflectionPropertyNamesProvider() : array
    {
        $methods = \get_class_methods(CoreReflectionProperty::class);
        return \array_combine($methods, \array_map(function (string $i) : array {
            return [$i];
        }, $methods));
    }

    /**
     * @param string $methodName
     * @dataProvider coreReflectionPropertyNamesProvider
     */
    public function testCoreReflectionPropertys(string $methodName) : void
    {
        $reflectionPropertyAdapterReflection = new CoreReflectionClass(ReflectionPropertyAdapter::class);
        self::assertTrue($reflectionPropertyAdapterReflection->hasMethod($methodName));
    }

    public function methodExpectationProvider() : array
    {
        return [
            ['__toString', null, '', []],
            ['getName', null, '', []],
            ['getValue', NotImplemented::class, null, []],
            ['setValue', NotImplemented::class, null, [new stdClass()]],
            ['isPublic', null, true, []],
            ['isPrivate', null, true, []],
            ['isProtected', null, true, []],
            ['isStatic', null, true, []],
            ['isDefault', null, true, []],
            ['getModifiers', null, 123, []],
            ['getDocComment', null, '', []],
            ['setAccessible', NotImplemented::class, null, [true]],
        ];
    }

    /**
     * @param string $methodName
     * @param string|null $expectedException
     * @param mixed $returnValue
     * @param array $args
     * @dataProvider methodExpectationProvider
     */
    public function testAdapterMethods(string $methodName, ?string $expectedException, $returnValue, array $args) : void
    {
        /** @var BetterReflectionProperty|\PHPUnit_Framework_MockObject_MockObject $reflectionStub */
        $reflectionStub = $this->createMock(BetterReflectionProperty::class);

        if (null === $expectedException) {
            $reflectionStub->expects($this->once())
                ->method($methodName)
                ->with(...$args)
                ->will($this->returnValue($returnValue));
        }

        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }

        $adapter = new ReflectionPropertyAdapter($reflectionStub);
        $adapter->{$methodName}(...$args);
    }

    public function testExport() : void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to export statically');
        ReflectionPropertyAdapter::export('foo', 0);
    }

    public function testGetDocCommentReturnsFalseWhenNoDocComment() : void
    {
        $betterReflectionProperty = $this->createMock(BetterReflectionProperty::class);
        $betterReflectionProperty
            ->method('getDocComment')
            ->willReturn('');

        $reflectionPropertyAdapter = new ReflectionPropertyAdapter($betterReflectionProperty);

        self::assertFalse($reflectionPropertyAdapter->getDocComment());
    }

    public function testGetDeclaringClass() : void
    {
        $betterReflectionClass = $this->createMock(BetterReflectionClass::class);
        $betterReflectionClass
            ->method('getName')
            ->willReturn('DeclaringClass');

        $betterReflectionProperty = $this->createMock(BetterReflectionProperty::class);
        $betterReflectionProperty
            ->method('getImplementingClass')
            ->willReturn($betterReflectionClass);

        $reflectionPropertyAdapter = new ReflectionPropertyAdapter($betterReflectionProperty);

        self::assertInstanceOf(ReflectionClassAdapter::class, $reflectionPropertyAdapter->getDeclaringClass());
        self::assertSame('DeclaringClass', $reflectionPropertyAdapter->getDeclaringClass()->getName());
    }
}
