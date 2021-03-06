<?php
declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Type;

use Iterator;
use Roave\BetterReflection\Identifier\Identifier;
use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflection\Reflection;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Exception\InvalidFileInfo;
use Roave\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use SplFileInfo;

/**
 * This source locator loads all php files from \FileSystemIterator
 */
class FileIteratorSourceLocator implements SourceLocator
{
    /**
     * @var AggregateSourceLocator|null
     */
    private $aggregateSourceLocator;

    /**
     * @var \Iterator|\SplFileInfo[]
     */
    private $fileSystemIterator;

    /**
     * @param \Iterator|\SplFileInfo[] $fileInfoIterator note: only \SplFileInfo allowed in this iterator
     *
     * @throws InvalidFileInfo In case of iterator not contains only SplFileInfo
     */
    public function __construct(Iterator $fileInfoIterator)
    {
        foreach ($fileInfoIterator as $fileInfo) {
            if ( ! $fileInfo instanceof SplFileInfo) {
                throw InvalidFileInfo::fromNonSplFileInfo($fileInfo);
            }
        }

        $this->fileSystemIterator = $fileInfoIterator;
    }

    /**
     * @return AggregateSourceLocator
     * @throws InvalidFileLocation
     */
    private function getAggregatedSourceLocator() : AggregateSourceLocator
    {
        return $this->aggregateSourceLocator ?: new AggregateSourceLocator(\array_values(\array_filter(\array_map(
            function (SplFileInfo $item) : ?SingleFileSourceLocator {
                if ( ! ($item->isFile() && \pathinfo($item->getRealPath(), \PATHINFO_EXTENSION) === 'php')) {
                    return null;
                }

                return new SingleFileSourceLocator($item->getRealPath());
            },
            \iterator_to_array($this->fileSystemIterator)
        ))));
    }

    /**
     * {@inheritDoc}
     * @throws InvalidFileLocation
     */
    public function locateIdentifier(Reflector $reflector, Identifier $identifier) : ?Reflection
    {
        return $this->getAggregatedSourceLocator()->locateIdentifier($reflector, $identifier);
    }

    /**
     * {@inheritDoc}
     * @throws InvalidFileLocation
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType) : array
    {
        return $this->getAggregatedSourceLocator()->locateIdentifiersByType($reflector, $identifierType);
    }
}
