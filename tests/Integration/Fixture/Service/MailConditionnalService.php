<?php

namespace Kassko\ObjectHydratorTest\Integration\Fixture\Service;

class MailConditionnalService
{
    public function isPrivateMail($id)
    {
        switch ($id) {
            case 1:
                return true;
            case 2:
                return false;
        }
    }
}
