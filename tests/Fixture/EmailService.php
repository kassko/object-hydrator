<?php

namespace Big\HydratorTest\Fixture;

class EmailService
{
    public function getData($id)
    {
        switch ($id) {
            case 1:
                return 'dany@gomes';
            case 2:
                return 'bogdan@vassilescu';
        }
    }
}
