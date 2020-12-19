<?php

namespace Big\HydratorTest\Integration\Fixture\Service;

class PersonService
{
    public function getData($id)
    {
        switch ($id) {
            case 1:
                return [
                    'first_name' => 'Dany',
                    'last_name' => 'Gomes',
                    'name' => 'Christian',
                    'phone' => '01 02 03 04 05',
                ];
            case 2:
                return [
                    'first_name' => 'Bogdan',
                    'name' => 'Vassilescu',
                    'phone' => '01 06 07 08 09',
                ];
        }
    }
}
