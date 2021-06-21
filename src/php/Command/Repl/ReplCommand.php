<?php

declare(strict_types=1);

namespace Phel\Command\Repl;

use Phel\Command\Repl\Exceptions\ExitException;
use Phel\Compiler\CompilerFacadeInterface;
use Phel\Compiler\Exceptions\CompilerException;
use Phel\Compiler\Parser\Exceptions\UnfinishedParserException;
use Phel\Printer\PrinterInterface;
use Phel\Runtime\RuntimeFacadeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class ReplCommand extends Command
{
    public const COMMAND_NAME = 'repl';

    private const ENABLE_BRACKETED_PASTE = "\e[?2004h";
    private const DISABLE_BRACKETED_PASTE = "\e[?2004l";
    private const INITIAL_PROMPT = 'phel:%d> ';
    private const OPEN_PROMPT = '....:%d> ';
    private const EXIT_REPL = 'exit';

    private RuntimeFacadeInterface $runtimeFacade;
    private ReplCommandIoInterface $io;
    private CompilerFacadeInterface $compilerFacade;
    private ColorStyleInterface $style;
    private PrinterInterface $printer;

    /** @var string[] */
    private array $inputBuffer = [];
    private int $lineNumber = 1;
    private InputResult $previousResult;

    public function __construct(
        RuntimeFacadeInterface $runtimeFacade,
        ReplCommandIoInterface $io,
        CompilerFacadeInterface $compilerFacade,
        ColorStyleInterface $style,
        PrinterInterface $printer
    ) {
        parent::__construct(self::COMMAND_NAME);
        $this->runtimeFacade = $runtimeFacade;
        $this->io = $io;
        $this->compilerFacade = $compilerFacade;
        $this->style = $style;
        $this->printer = $printer;
        $this->previousResult = InputResult::empty();
    }

    protected function configure(): void
    {
        $this->setDescription('Start a Repl.');
    }

    public function addRuntimePath(string $namespacePrefix, array $path): self
    {
        $this->runtimeFacade->addPath($namespacePrefix, $path);

        return $this;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->readHistory();
        $this->io->writeln($this->style->yellow('Welcome to the Phel Repl'));
        $this->io->writeln('Type "exit" or press Ctrl-D to exit.');

        $this->loopReadLineAndAnalyze();

        return self::SUCCESS;
    }

    private function loopReadLineAndAnalyze(): void
    {
        while (true) {
            try {
                $this->addLineFromPromptToBuffer();
                $this->checkExitInputBuffer();
                $this->analyzeInputBuffer();
            } catch (ExitException $e) {
                break;
            } catch (Throwable $e) {
                $this->inputBuffer = [];
                $this->io->writeln($this->style->red($e->getMessage()));
                $this->io->writeln($e->getTraceAsString());
            }
        }

        $this->io->writeln($this->style->yellow('Bye!'));
    }

    private function addLineFromPromptToBuffer(): void
    {
        if ($this->io->isBracketedPasteSupported()) {
            $this->io->write(self::ENABLE_BRACKETED_PASTE);
        }

        $isInitialInput = empty($this->inputBuffer);
        $prompt = $isInitialInput ? self::INITIAL_PROMPT : self::OPEN_PROMPT;
        $input = $this->io->readline(sprintf($prompt, $this->lineNumber));

        if ($this->io->isBracketedPasteSupported()) {
            $this->io->write(self::DISABLE_BRACKETED_PASTE);
        }

        $this->lineNumber++;

        if ($input === null && $isInitialInput) {
            // Ctrl+D will exit the repl
            $this->inputBuffer[] = self::EXIT_REPL;
        } elseif ($input === null && !$isInitialInput) {
            // Ctrl+D will empty the buffer
            $this->inputBuffer = [];
            $this->io->writeln();
        } else {
            $this->inputBuffer[] = $input;
        }
    }

    /**
     * @throws ExitException
     */
    private function checkExitInputBuffer(): void
    {
        $firstInput = $this->inputBuffer[0] ?? '';

        if (self::EXIT_REPL === $firstInput) {
            throw ExitException::fromRepl();
        }
    }

    private function analyzeInputBuffer(): void
    {
        if (empty($this->inputBuffer)) {
            return;
        }

        $fullInput = $this->previousResult->readBuffer($this->inputBuffer);

        try {
            $result = $this->compilerFacade->eval($fullInput, $this->lineNumber - count($this->inputBuffer));
            $this->previousResult = InputResult::fromAny($result);

            $this->addHistory($fullInput);
            $this->io->writeln($this->printer->print($result));

            $this->inputBuffer = [];
        } catch (UnfinishedParserException $e) {
            // The input is valid but more input is missing to finish the parsing.
        } catch (CompilerException $e) {
            $this->io->writeLocatedException($e->getNestedException(), $e->getCodeSnippet());
            $this->addHistory($fullInput);
            $this->inputBuffer = [];
        } catch (Throwable $e) {
            $this->io->writeStackTrace($e);
            $this->addHistory($fullInput);
            $this->inputBuffer = [];
        }
    }

    private function addHistory(string $input): void
    {
        if ('' !== $input) {
            $this->io->addHistory($input);
        }
    }
}
