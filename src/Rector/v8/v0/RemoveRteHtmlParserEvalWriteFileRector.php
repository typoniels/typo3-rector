<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use TYPO3\CMS\Core\Html\RteHtmlParser;

/**
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72384-RemovedDeprecatedCodeFromHtmlParser.html
 */
final class RemoveRteHtmlParserEvalWriteFileRector extends AbstractRector
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, RteHtmlParser::class)) {
            return null;
        }

        if ($this->isName($node->name, 'evalWriteFile')) {
            $methodName = $this->getName($node->name);
            if (null === $methodName) {
                return null;
            }

            try {
                $this->removeNode($node);
                return $node;
            } catch (ShouldNotHappenException $shouldNotHappenException) {
                $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
                $this->removeNode($parentNode);
                return $node;
            }
        }
        return null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('remove evalWriteFile method from RteHtmlparser.', [
            new CodeSample(
                <<<'PHP'
use TYPO3\CMS\Core\Html\RteHtmlParser;

final class RteHtmlParserRemovedMethods
{

    public function doSomething(): void
    {
        $rtehtmlparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(RteHtmlParser::class);
        $rtehtmlparser->evalWriteFile();
    }

}
PHP
                ,
                <<<'PHP'
use TYPO3\CMS\Core\Html\RteHtmlParser;

final class RteHtmlParserRemovedMethods
{

    public function doSomething(): void
    {
        $rtehtmlparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(RteHtmlParser::class);
    }

}
PHP
            ),
        ]);
    }
}
