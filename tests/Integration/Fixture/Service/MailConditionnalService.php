<?php

namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Service;

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
