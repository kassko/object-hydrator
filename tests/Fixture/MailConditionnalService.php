<?php

namespace Big\HydratorTest\Fixture;

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
