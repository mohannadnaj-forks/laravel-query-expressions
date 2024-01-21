<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Dev;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddStaticFromMethodRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Enforce the addition of a static ::from method in classes',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class MyClass
{
    public function __construct($param1, $param2)
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class MyClass
{
    public function __construct($param1, $param2)
    {
    }

    public static function from($param1, $param2)
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param  Class_  $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isAnonymous()) {
            return null;
        }

        if ($node->isAbstract()) {
            return null;
        }
        $constructorMethod = $node->getMethod('__construct');
        if (! $constructorMethod instanceof ClassMethod) {
            return null;
        }

        if (
            method_exists($node->namespacedName->toString(), 'from')
        ) {
            return null;
        }

        $fromMethod = $this->createStaticFromMethod($constructorMethod);

        $node->stmts[] = $fromMethod;

        return $node;
    }

    private function createStaticFromMethod(ClassMethod $constructorMethod): ClassMethod
    {
        $fromMethod = new ClassMethod('from');
        $fromMethod->flags = Class_::MODIFIER_PUBLIC | Class_::MODIFIER_STATIC;

        $fromMethod->params = array_map(function (Param $param) {
            $param = clone $param;
            $param->flags = 0;

            return $param;
        }, $constructorMethod->getParams());

        $fromMethod->returnType = new Name('self');

        $fromMethod->stmts = [
            new Node\Stmt\Return_(
                new Node\Expr\New_(
                    new Node\Name('self'),
                    array_map(function (Param $param) {
                        return new Node\Arg($param->var);
                    }, $fromMethod->params)
                )
            ),
        ];

        if ($comment = $constructorMethod->getDocComment()) {
            $fromMethod->setDocComment($comment);
        }

        return $fromMethod;
    }
}
