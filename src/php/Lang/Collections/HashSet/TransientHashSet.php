<?php

declare(strict_types=1);

namespace Phel\Lang\Collections\HashSet;

use Phel\Lang\Collections\Map\TransientMapInterface;
use Phel\Lang\HasherInterface;

/**
 * @template V
 *
 * @implements TransientHashSetInterface<V>
 */
class TransientHashSet implements TransientHashSetInterface
{
    private TransientMapInterface $transientMap;
    private HasherInterface $hasher;

    public function __construct(HasherInterface $hasher, TransientMapInterface $transientMap)
    {
        $this->hasher = $hasher;
        $this->transientMap = $transientMap;
    }

    public function count()
    {
        return $this->transientMap->count();
    }

    /**
     * @param V $value
     */
    public function contains($value): bool
    {
        return $this->transientMap->containsKey($value);
    }

    /**
     * @param V $value
     */
    public function add($value): TransientHashSetInterface
    {
        $this->transientMap->put($value, $value);

        return $this;
    }

    /**
     * @param V $value
     */
    public function remove($value): TransientHashSetInterface
    {
        $this->transientMap->remove($value);

        return $this;
    }

    public function persistent(): PersistentHashSetInterface
    {
        return new PersistentHashSet($this->hasher, null, $this->transientMap->persistent());
    }

    public function toPhpArray(): array
    {
        return $this->persistent()->toPhpArray();
    }
}
