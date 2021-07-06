<?php

declare(strict_types=1);

namespace PhelTest\Benchmark\Command;

use Phel\Command\CommandFactory;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @BeforeMethods("setUp")
 */
final class CommandBench
{
    private CommandFactory $commandFactory;

    public function setUp(): void
    {
        $this->commandFactory = new CommandFactory();
    }

    public function bench_run_command(): void
    {
        $this->commandFactory
            ->createRunCommand()
            ->run(
                new StringInput(__DIR__ . '/fixtures/run-command.phel'),
                new NullOutput()
            );
    }

    public function bench_test_command(): void
    {
        ob_start();
        $this->commandFactory
            ->createTestCommand()
            ->run(
                new StringInput(__DIR__ . '/fixtures/test-command.phel'),
                new NullOutput()
            );
        ob_clean();
    }
}
