<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Rector\v8\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use TYPO3\CMS\Core\TypoScript\TemplateService;

/**
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Deprecation-77477-TemplateService-fileContent.html
 */
final class RefactorMethodFileContentRector extends AbstractRector
{
    /**
     * @var Typo3NodeResolver
     */
    private $typo3NodeResolver;

    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if (! $this->isName($node->name, 'fileContent')) {
            return null;
        }

        return new Ternary(
            $this->nodeFactory->createMethodCall($node->var, 'getFileName', $node->args),
            $this->nodeFactory->createFuncCall('file_get_contents', $node->args),
            $this->nodeFactory->createNull()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor method fileContent of class TemplateService', [
            new CodeSample(<<<'PHP'
$content = $GLOBALS['TSFE']->tmpl->fileContent('foo.txt');
PHP
                , <<<'PHP'
$content = $GLOBALS['TSFE']->tmpl->getFileName('foo.txt') ? file_get_contents('foo.txt') : null;
PHP
            ),
        ]);
    }

    private function shouldSkip(MethodCall $node): bool
    {
        if ($this->isObjectType($node->var, TemplateService::class)) {
            return false;
        }
        return ! $this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals(
            $node,
            Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER,
            'tmpl'
        );
    }
}
