<?php
declare(strict_types=1);

namespace Roave\BetterReflection\Reflection\Adapter;

use ReflectionMethod as CoreReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionMethod as BetterReflectionMethod;

class ReflectionMethod extends CoreReflectionMethod
{
    /**
     * @var BetterReflectionMethod
     */
    private $betterReflectionMethod;

    public function __construct(BetterReflectionMethod $betterReflectionMethod)
    {
        $this->betterReflectionMethod = $betterReflectionMethod;
    }

    /**
     * {@inheritDoc}
     */
    public static function export($class, $name, $return = null)
    {
        BetterReflectionMethod::export(...\func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->betterReflectionMethod->__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function inNamespace()
    {
        return $this->betterReflectionMethod->inNamespace();
    }

    /**
     * {@inheritDoc}
     */
    public function isClosure()
    {
        return $this->betterReflectionMethod->isClosure();
    }

    /**
     * {@inheritDoc}
     */
    public function isDeprecated()
    {
        return $this->betterReflectionMethod->isDeprecated();
    }

    /**
     * {@inheritDoc}
     */
    public function isInternal()
    {
        return $this->betterReflectionMethod->isInternal();
    }

    /**
     * {@inheritDoc}
     */
    public function isUserDefined()
    {
        return $this->betterReflectionMethod->isUserDefined();
    }

    /**
     * {@inheritDoc}
     */
    public function getClosureThis()
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getClosureScopeClass()
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getDocComment()
    {
        return $this->betterReflectionMethod->getDocComment() ?: false;
    }

    /**
     * {@inheritDoc}
     */
    public function getEndLine()
    {
        return $this->betterReflectionMethod->getEndLine();
    }

    /**
     * {@inheritDoc}
     */
    public function getExtension()
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensionName()
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getFileName()
    {
        return $this->betterReflectionMethod->getFileName() ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->betterReflectionMethod->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespaceName()
    {
        return $this->betterReflectionMethod->getNamespaceName();
    }

    /**
     * {@inheritDoc}
     */
    public function getNumberOfParameters()
    {
        return $this->betterReflectionMethod->getNumberOfParameters();
    }

    /**
     * {@inheritDoc}
     */
    public function getNumberOfRequiredParameters()
    {
        return $this->betterReflectionMethod->getNumberOfRequiredParameters();
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        $parameters = $this->betterReflectionMethod->getParameters();

        $wrappedParameters = [];
        foreach ($parameters as $key => $parameter) {
            $wrappedParameters[$key] = new ReflectionParameter($parameter);
        }
        return $wrappedParameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getReturnType()
    {
        return ReflectionType::fromReturnTypeOrNull($this->betterReflectionMethod->getReturnType());
    }

    /**
     * {@inheritDoc}
     */
    public function getShortName()
    {
        return $this->betterReflectionMethod->getShortName();
    }

    /**
     * {@inheritDoc}
     */
    public function getStartLine()
    {
        return $this->betterReflectionMethod->getStartLine();
    }

    /**
     * {@inheritDoc}
     */
    public function getStaticVariables()
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function returnsReference()
    {
        return $this->betterReflectionMethod->returnsReference();
    }

    /**
     * {@inheritDoc}
     */
    public function isGenerator()
    {
        return $this->betterReflectionMethod->isGenerator();
    }

    /**
     * {@inheritDoc}
     */
    public function isVariadic()
    {
        return $this->betterReflectionMethod->isVariadic();
    }

    /**
     * {@inheritDoc}
     */
    public function isPublic()
    {
        return $this->betterReflectionMethod->isPublic();
    }

    /**
     * {@inheritDoc}
     */
    public function isPrivate()
    {
        return $this->betterReflectionMethod->isPrivate();
    }

    /**
     * {@inheritDoc}
     */
    public function isProtected()
    {
        return $this->betterReflectionMethod->isProtected();
    }

    /**
     * {@inheritDoc}
     */
    public function isAbstract()
    {
        return $this->betterReflectionMethod->isAbstract();
    }

    /**
     * {@inheritDoc}
     */
    public function isFinal()
    {
        return $this->betterReflectionMethod->isFinal();
    }

    /**
     * {@inheritDoc}
     */
    public function isStatic()
    {
        return $this->betterReflectionMethod->isStatic();
    }

    /**
     * {@inheritDoc}
     */
    public function isConstructor()
    {
        return $this->betterReflectionMethod->isConstructor();
    }

    /**
     * {@inheritDoc}
     */
    public function isDestructor()
    {
        return $this->betterReflectionMethod->isDestructor();
    }

    /**
     * {@inheritDoc}
     */
    public function getClosure($object)
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getModifiers()
    {
        return $this->betterReflectionMethod->getModifiers();
    }

    /**
     * {@inheritDoc}
     */
    public function invoke($object, $args)
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function invokeArgs($object, array $args)
    {
        throw new Exception\NotImplemented('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getDeclaringClass()
    {
        return new ReflectionClass($this->betterReflectionMethod->getImplementingClass());
    }

    /**
     * {@inheritDoc}
     */
    public function getPrototype()
    {
        return new self($this->betterReflectionMethod->getPrototype());
    }

    /**
     * {@inheritDoc}
     */
    public function setAccessible($value)
    {
        throw new Exception\NotImplemented('Not implemented');
    }
}
