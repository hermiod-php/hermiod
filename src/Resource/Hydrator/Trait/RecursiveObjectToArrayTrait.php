<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Hydrator\Trait;

trait RecursiveObjectToArrayTrait
{
    private function objectsToArrays(mixed $object): mixed
    {
        if (\is_object($object)) {
            $object = (array) $object;
        }

        if (\is_array($object)) {
            foreach ($object as $key => $value) {
                $object[$key] = $this->objectsToArrays($value);
            }
        }

        return $object;
    }
}