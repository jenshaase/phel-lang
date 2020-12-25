<?php

declare(strict_types=1);

namespace Phel\Printer;

use Phel\Lang\PhelArray;
use Phel\Printer;

/**
 * @implements PrinterInterface<PhelArray>
 */
final class PhelArrayPrinter implements PrinterInterface
{
    private Printer $printer;

    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }

    /**
     * @param PhelArray $form
     */
    public function print($form): string
    {
        $values = array_map(
            fn ($elem): string => $this->printer->print($elem),
            $form->toPhpArray()
        );

        return '@[' . implode(' ', $values) . ']';
    }
}