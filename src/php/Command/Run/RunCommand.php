<?php

declare(strict_types=1);

namespace Phel\Command\Run;

use Phel\Command\Run\Exceptions\CannotLoadNamespaceException;
use Phel\Command\Shared\CommandExceptionWriterInterface;
use Phel\Compiler\Evaluator\Exceptions\CompiledCodeIsMalformedException;
use Phel\Compiler\Evaluator\Exceptions\FileException;
use Phel\Compiler\Exceptions\CompilerException;
use Phel\NamespaceExtractor\NamespaceExtractorFacadeInterface;
use Phel\Runtime\RuntimeFacadeInterface;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class RunCommand extends Command
{
    public const COMMAND_NAME = 'run';

    private CommandExceptionWriterInterface $exceptionWriter;
    private RuntimeFacadeInterface $runtimeFacade;
    private NamespaceExtractorFacadeInterface $namespaceExtractorFacade;

    public function __construct(
        CommandExceptionWriterInterface $exceptionWriter,
        RuntimeFacadeInterface $runtimeFacade,
        NamespaceExtractorFacadeInterface $namespaceExtractorFacade
    ) {
        parent::__construct(self::COMMAND_NAME);
        $this->exceptionWriter = $exceptionWriter;
        $this->runtimeFacade = $runtimeFacade;
        $this->namespaceExtractorFacade = $namespaceExtractorFacade;
    }

    protected function configure(): void
    {
        $this->setDescription('Runs a script.')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The file path that you want to run.'
            )->addOption(
                'with-time',
                't',
                InputOption::VALUE_NONE,
                'With time awareness'
            );
    }

    public function addRuntimePath(string $namespacePrefix, array $path): self
    {
        $this->runtimeFacade->addPath($namespacePrefix, $path);

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            /** @var string $fileOrPath */
            $fileOrPath = $input->getArgument('path');
            $this->loadNamespace($fileOrPath);

            if ($input->getOption('with-time')) {
                $output->writeln((new ResourceUsageFormatter())->resourceUsageSinceStartOfRequest());
            }

            return self::SUCCESS;
        } catch (CompilerException $e) {
            $this->exceptionWriter->writeLocatedException($output, $e->getNestedException(), $e->getCodeSnippet());
        } catch (Throwable $e) {
            $this->exceptionWriter->writeStackTrace($output, $e);
        }

        return self::FAILURE;
    }

    /**
     * @throws CompilerException
     * @throws CompiledCodeIsMalformedException
     * @throws FileException
     * @throws CannotLoadNamespaceException
     */
    private function loadNamespace(string $fileOrPath): void
    {
        $ns = file_exists($fileOrPath)
            ? $this->namespaceExtractorFacade->getNamespaceFromFile($fileOrPath)->getNamespace()
            : $fileOrPath;

        $result = $this->runtimeFacade->getRuntime()->loadNs($ns);

        if (!$result) {
            throw CannotLoadNamespaceException::withName($ns);
        }
    }
}
