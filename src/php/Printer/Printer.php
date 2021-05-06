<?php

declare(strict_types=1);

namespace Phel\Printer;

use Phel\Lang\Collections\HashSet\PersistentHashSetInterface;
use Phel\Lang\Collections\HashSet\TransientHashSetInterface;
use Phel\Lang\Collections\LinkedList\PersistentListInterface;
use Phel\Lang\Collections\Map\PersistentMapInterface;
use Phel\Lang\Collections\Map\TransientMapInterface;
use Phel\Lang\Collections\Struct\AbstractPersistentStruct;
use Phel\Lang\Collections\Vector\PersistentVectorInterface;
use Phel\Lang\Collections\Vector\TransientVectorInterface;
use Phel\Lang\Keyword;
use Phel\Lang\PhelArray;
use Phel\Lang\Set;
use Phel\Lang\Symbol;
use Phel\Lang\Table;
use Phel\Printer\TypePrinter\AnonymousClassPrinter;
use Phel\Printer\TypePrinter\ArrayPrinter;
use Phel\Printer\TypePrinter\BooleanPrinter;
use Phel\Printer\TypePrinter\KeywordPrinter;
use Phel\Printer\TypePrinter\NonPrintableClassPrinter;
use Phel\Printer\TypePrinter\NullPrinter;
use Phel\Printer\TypePrinter\NumberPrinter;
use Phel\Printer\TypePrinter\ObjectPrinter;
use Phel\Printer\TypePrinter\PersistentHashSetPrinter;
use Phel\Printer\TypePrinter\PersistentListPrinter;
use Phel\Printer\TypePrinter\PersistentMapPrinter;
use Phel\Printer\TypePrinter\PersistentVectorPrinter;
use Phel\Printer\TypePrinter\PhelArrayPrinter;
use Phel\Printer\TypePrinter\ResourcePrinter;
use Phel\Printer\TypePrinter\SetPrinter;
use Phel\Printer\TypePrinter\StringPrinter;
use Phel\Printer\TypePrinter\StructPrinter;
use Phel\Printer\TypePrinter\SymbolPrinter;
use Phel\Printer\TypePrinter\TablePrinter;
use Phel\Printer\TypePrinter\ToStringPrinter;
use Phel\Printer\TypePrinter\TypePrinterInterface;
use ReflectionClass;
use RuntimeException;

final class Printer implements PrinterInterface
{
    private bool $readable;
    private bool $withColor;

    public static function readable(): self
    {
        return new self($readable = true);
    }

    public static function nonReadable(): self
    {
        return new self($readable = false);
    }

    public static function nonReadableWithColor(): self
    {
        return new self($readable = false, $withColor = true);
    }

    private function __construct(bool $readable, bool $withColor = false)
    {
        $this->readable = $readable;
        $this->withColor = $withColor;
    }

    /**
     * Converts a form to a printable string.
     *
     * @param mixed $form The form to print
     */
    public function print($form): string
    {
        $printer = $this->createTypePrinter($form);

        return $printer->print($form);
    }

    /**
     * @param mixed $form The form to print
     */
    private function createTypePrinter($form): TypePrinterInterface
    {
        if (is_object($form)) {
            return $this->createObjectTypePrinter($form);
        }

        return $this->creatScalarTypePrinter($form);
    }

    /**
     * @param mixed $form The form to print
     */
    private function createObjectTypePrinter($form): TypePrinterInterface
    {
        if ($form instanceof PersistentListInterface) {
            return new PersistentListPrinter($this);
        }
        if ($form instanceof PersistentVectorInterface || $form instanceof TransientVectorInterface) {
            return new PersistentVectorPrinter($this);
        }
        if ($form instanceof PersistentMapInterface || $form instanceof TransientMapInterface) {
            return new PersistentMapPrinter($this);
        }
        if ($form instanceof PersistentHashSetInterface || $form instanceof TransientHashSetInterface) {
            return new PersistentHashSetPrinter($this);
        }
        if ($form instanceof Keyword) {
            return new KeywordPrinter($this->withColor);
        }
        if ($form instanceof Symbol) {
            return new SymbolPrinter($this->withColor);
        }
        if ($form instanceof Set) {
            return new SetPrinter($this);
        }
        if ($form instanceof PhelArray) {
            return new PhelArrayPrinter($this);
        }
        if ($form instanceof Table) {
            return new TablePrinter($this);
        }
        if ($form instanceof AbstractPersistentStruct) {
            return new StructPrinter($this);
        }
        if (method_exists($form, '__toString')) {
            return new ToStringPrinter();
        }
        if ((new ReflectionClass($form))->isAnonymous()) {
            return new AnonymousClassPrinter();
        }

        return new NonPrintableClassPrinter($this->withColor);
    }

    /**
     * @param mixed $form The form to print
     */
    private function creatScalarTypePrinter($form): TypePrinterInterface
    {
        $printerName = gettype($form);

        if ('string' === $printerName) {
            return new StringPrinter($this->readable, $this->withColor);
        }
        if ('integer' === $printerName || 'double' === $printerName) {
            return new NumberPrinter($this->withColor);
        }
        if ('boolean' === $printerName) {
            return new BooleanPrinter($this->withColor);
        }
        if ('NULL' === $printerName) {
            return new NullPrinter($this->withColor);
        }
        if ('array' === $printerName && !$this->readable) {
            return new ArrayPrinter($this, $this->withColor);
        }
        if ('resource' === $printerName && !$this->readable) {
            return new ResourcePrinter();
        }
        if (!$this->readable) {
            return new ObjectPrinter();
        }

        throw new RuntimeException('Printer can not print this type: ' . $printerName);
    }
}
