<?php
declare(strict_types=1);

namespace Roave\BetterReflection\NodeCompiler;

use PhpParser\Node;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Util\FileHelper;

class CompileNodeToValue
{
    /**
     * Compile an expression from a node into a value.
     *
     * @param Node $node Node has to be processed by the PhpParser\NodeVisitor\NameResolver
     * @param CompilerContext $context
     * @return mixed
     * @throw Exception\UnableToCompileNode
     */
    public function __invoke(Node $node, CompilerContext $context)
    {
        if ($node instanceof Node\Scalar\String_
            || $node instanceof Node\Scalar\DNumber
            || $node instanceof Node\Scalar\LNumber) {
            return $node->value;
        }

        // common edge case - negative numbers
        if ($node instanceof Node\Expr\UnaryMinus) {
            return $this($node->expr, $context) * -1;
        }

        if ($node instanceof Node\Expr\Array_) {
            return $this->compileArray($node, $context);
        }

        if ($node instanceof Node\Expr\ConstFetch) {
            return $this->compileConstFetch($node);
        }

        if ($node instanceof Node\Expr\ClassConstFetch) {
            return $this->compileClassConstFetch($node, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp) {
            return $this->compileBinaryOperator($node, $context);
        }

        if ($node instanceof Node\Scalar\MagicConst\Dir) {
            return $this->compileDirConstant($context);
        }

        if ($node instanceof Node\Scalar\MagicConst\Class_) {
            return $this->compileClassConstant($context);
        }

        throw new Exception\UnableToCompileNode('Unable to compile expression: ' . \get_class($node));
    }

    /**
     * Compile arrays
     *
     * @param Node\Expr\Array_ $arrayNode
     * @param CompilerContext $context
     * @return array
     */
    private function compileArray(Node\Expr\Array_ $arrayNode, CompilerContext $context) : array
    {
        $compiledArray = [];
        foreach ($arrayNode->items as $arrayItem) {
            $compiledValue = $this($arrayItem->value, $context);

            if (null === $arrayItem->key) {
                $compiledArray[] = $compiledValue;
                continue;
            }

            $compiledArray[$this($arrayItem->key, $context)] = $compiledValue;
        }
        return $compiledArray;
    }

    /**
     * Compile constant expressions
     *
     * @param Node\Expr\ConstFetch $constNode
     * @return bool|mixed|null
     * @throws \Roave\BetterReflection\NodeCompiler\Exception\UnableToCompileNode
     */
    private function compileConstFetch(Node\Expr\ConstFetch $constNode)
    {
        $firstName = \reset($constNode->name->parts);
        switch ($firstName) {
            case 'null':
                return null;
            case 'false':
                return false;
            case 'true':
                return true;
            default:
                if ( ! \defined($firstName)) {
                    throw new Exception\UnableToCompileNode(
                        \sprintf('Constant "%s" has not been defined', $firstName)
                    );
                }

                return \constant($firstName);
        }
    }

    /**
     * Compile class constants
     *
     * @param Node\Expr\ClassConstFetch $node
     * @param CompilerContext $context
     * @return string|int|float|bool|array|null
     * @throws \Roave\BetterReflection\Reflector\Exception\IdentifierNotFound
     */
    private function compileClassConstFetch(Node\Expr\ClassConstFetch $node, CompilerContext $context)
    {
        $className = $node->class->toString();

        if ($node->name === 'class') {
            return $className;
        }

        /** @var ReflectionClass|null $classInfo */
        $classInfo = null;

        if ('self' === $className || 'static' === $className) {
            $classInfo = $this->getConstantDeclaringClass($node->name, $context->getSelf());
        }

        if (null === $classInfo) {
            /** @var ReflectionClass $classInfo */
            $classInfo = $context->getReflector()->reflect($className);
        }

        return $classInfo->getConstant($node->name);
    }

    /**
     * Compile a binary operator node
     *
     * @param Node\Expr\BinaryOp $node
     * @param CompilerContext $context
     * @return mixed
     */
    private function compileBinaryOperator(Node\Expr\BinaryOp $node, CompilerContext $context)
    {
        if ($node instanceof Node\Expr\BinaryOp\Plus) {
            return $this($node->left, $context) + $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Mul) {
            return $this($node->left, $context) * $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Minus) {
            return $this($node->left, $context) - $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Div) {
            return $this($node->left, $context) / $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Concat) {
            return $this($node->left, $context) . $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            return $this($node->left, $context) && $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\BooleanOr) {
            return $this($node->left, $context) || $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\BitwiseAnd) {
            return $this($node->left, $context) & $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\BitwiseOr) {
            return $this($node->left, $context) | $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\BitwiseXor) {
            return $this($node->left, $context) ^ $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Equal) {
            return $this($node->left, $context) == $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Greater) {
            return $this($node->left, $context) > $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\GreaterOrEqual) {
            return $this($node->left, $context) >= $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Identical) {
            return $this($node->left, $context) === $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\LogicalAnd) {
            return $this($node->left, $context) and $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\LogicalOr) {
            return $this($node->left, $context) or $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\LogicalXor) {
            return $this($node->left, $context) xor $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Mod) {
            return $this($node->left, $context) % $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\NotEqual) {
            return $this($node->left, $context) != $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\NotIdentical) {
            return $this($node->left, $context) !== $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Pow) {
            return $this($node->left, $context) ** $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\ShiftLeft) {
            return $this($node->left, $context) << $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\ShiftRight) {
            return $this($node->left, $context) >> $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\Smaller) {
            return $this($node->left, $context) < $this($node->right, $context);
        }

        if ($node instanceof Node\Expr\BinaryOp\SmallerOrEqual) {
            return $this($node->left, $context) <= $this($node->right, $context);
        }

        throw new Exception\UnableToCompileNode('Unable to compile binary operator: ' . \get_class($node));
    }

    /**
     * Compile a __DIR__ node
     */
    private function compileDirConstant(CompilerContext $context) : string
    {
        return FileHelper::normalizeWindowsPath(\dirname(\realpath($context->getFileName())));
    }

    /**
     * Compiles magic constant __CLASS__
     */
    private function compileClassConstant(CompilerContext $context) : string
    {
        return $context->hasSelf() ? $context->getSelf()->getName() : '';
    }

    private function getConstantDeclaringClass(string $constantName, ReflectionClass $class) : ?ReflectionClass
    {
        if ($class->hasConstant($constantName)) {
            return $class;
        }

        $parentClass = $class->getParentClass();

        return $parentClass ? $this->getConstantDeclaringClass($constantName, $parentClass) : null;
    }
}
