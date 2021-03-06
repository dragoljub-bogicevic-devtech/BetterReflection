<?php
declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Reflection;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\PrettyPrinter\Standard;
use Roave\BetterReflection\SourceLocator\Ast\PhpParserFactory;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\ClassReflection;

/**
 * Function that generates a stub source from a given reflection instance.
 *
 * @internal
 */
final class SourceStubber
{
    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct()
    {
        $this->parser        = PhpParserFactory::create();
        $this->prettyPrinter = new Standard();
    }

    /**
     * @param ClassReflection $reflection
     *
     * @return string
     */
    public function __invoke(ClassReflection $reflection) : string
    {
        $stubCode    = ClassGenerator::fromReflection($reflection)->generate();
        $isInterface = $reflection->isInterface();

        if ( ! ($isInterface || $reflection->isTrait())) {
            return $stubCode;
        }

        return $this->prettyPrinter->prettyPrint(
            $this->replaceNodesRecursively($this->parser->parse('<?php ' . $stubCode), $isInterface)
        );
    }

    /**
     * @param \PhpParser\Node[] $statements
     * @param bool              $isInterfaceOrTrait (true => interface, false => trait)
     *
     * @return \PhpParser\Node[]
     */
    private function replaceNodesRecursively(array $statements, bool $isInterfaceOrTrait) : array
    {
        foreach ($statements as $key => $statement) {
            if ($statement instanceof Class_) {
                $statements[$key] = $isInterfaceOrTrait
                    ? new Interface_(
                        $statement->name,
                        [
                            'extends' => $statement->implements,
                            'stmts'   => $statement->stmts,
                        ]
                    )
                    : new Trait_($statement->name, $statement->stmts);

                continue;
            }

            if (\property_exists($statement, 'stmts')) {
                $statement->stmts = $this->replaceNodesRecursively($statement->stmts, $isInterfaceOrTrait);
            }
        }

        return $statements;
    }
}
