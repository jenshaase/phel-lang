<?php

declare(strict_types=1);

namespace Phel\Compiler\Emitter\OutputEmitter\NodeEmitter;

use Phel\Compiler\Analyzer\Ast\AbstractNode;
use Phel\Compiler\Analyzer\Ast\NsNode;
use Phel\Compiler\Emitter\OutputEmitter\NodeEmitterInterface;
use Phel\Lang\Symbol;
use function addslashes;

final class NsEmitter implements NodeEmitterInterface
{
    use WithOutputEmitterTrait;

    public function emit(AbstractNode $node): void
    {
        assert($node instanceof NsNode);

        $this->emitRequiredNamespaces($node);
        $this->emitCurrentNamespace($node);
    }

    private function emitRequiredNamespaces(NsNode $node): void
    {
        foreach ($node->getRequireNs() as $i => $ns) {
            $this->outputEmitter->emitLine(
                '\Phel\Runtime\RuntimeSingleton::getInstance()->loadNs("' . addslashes($ns->getName()) . '");',
                $ns->getStartLocation()
            );
        }
    }

    private function emitCurrentNamespace(NsNode $node): void
    {
        $this->outputEmitter->emitLine(
            '\Phel\Runtime\RuntimeSingleton::getInstance()->getEnv()->setNs("' . addslashes($node->getNamespace()) . '");',
            $node->getStartSourceLocation()
        );

        $nsSym = Symbol::create('*ns*');
        $nsSym->setStartLocation($node->getStartSourceLocation());
        $this->outputEmitter->emitGlobalBase('phel\\core', $nsSym);
        $this->outputEmitter->emitStr(' = ', $node->getStartSourceLocation());
        $this->outputEmitter->emitLiteral($this->outputEmitter->mungeEncodeNs($node->getNamespace()));
        $this->outputEmitter->emitLine(';', $node->getStartSourceLocation());
    }
}
