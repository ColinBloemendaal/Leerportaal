<?php

declare(strict_types=1);

namespace App\Services\Ordering;

use App\Exceptions\InvalidOrderingException;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Reassigns each sibling's `order` column to match a caller-supplied
 * sequence. Shared by modules, lessons, and blocks -- all three are
 * "an ordered HasMany of a parent", the same reordering rule applies
 * identically to each, and none needs its own bespoke algorithm.
 */
final readonly class SiblingOrderingService
{
    /**
     * @param HasMany<*, *> $siblings scoped to the parent already, e.g. $course->modules()
     * @param list<int> $orderedIds every sibling's ID, in the desired order
     */
    public function reorder(HasMany $siblings, array $orderedIds): void
    {
        $existingIds = $siblings->pluck('id')->map(static fn (mixed $id): int => (int) $id)->sort()->values()->all();
        $givenIds = collect($orderedIds)->sort()->values()->all();

        if ($existingIds !== $givenIds) {
            throw InvalidOrderingException::mismatchedIds();
        }

        foreach ($orderedIds as $index => $id) {
            $siblings->getRelated()->newQuery()->whereKey($id)->update(['order' => $index]);
        }
    }
}
