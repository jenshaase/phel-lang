<?php

declare(strict_types=1);

namespace Phel\Printer;

use Phel\Lang\Symbol;

/**
 * @implements PrinterInterface<Symbol>
 */
final class SymbolPrinter implements PrinterInterface
{
    /**
     * @param Symbol $form
     */
    public function print($form): string
    {
        return $form->getName();
    }
}