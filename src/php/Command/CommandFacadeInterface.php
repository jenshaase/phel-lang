<?php

declare(strict_types=1);

namespace Phel\Command;

interface CommandFacadeInterface
{
    public function executeReplCommand(): void;

    public function executeRunCommand(string $fileOrPath): void;

    /**
     * @param list<string> $paths
     */
    public function executeTestCommand(array $paths, array $options = []): void;

    /**
     * @param list<string> $paths
     */
    public function executeFormatCommand(array $paths): void;

    public function executeExportCommand(): void;
}
