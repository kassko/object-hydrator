<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model\Property;

use Kassko\ObjectHydrator\ClassMetadata\Model\Property;

/**
 * @author kko
 */
final class Candidates extends Property
{
    public array $candidates = [];


    public function hasCandidates() : bool
    {
        return count($this->candidates) > 0;
    }

    public function getCandidates() : array
    {
        return $this->candidates;
    }

    public function addCandidate(Property $candidate) : self
    {
        $this->candidates[] = $candidate;

        return $this;
    }
}
