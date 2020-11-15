<?php

namespace Big\Hydrator;

class HydratorRepository
{
    private array $hydratorInstances = [];

    /**
     * Factory method to get an hydrator instance or create an hydrator if no instance available.
     *
     * @param $objectClass Object class to hydrate
     *
     * @return AbstractHydrator
     */
    public function getHydratorFor(string $objectClass) : Hydrator
    {
        if (! isset($this->hydratorInstances[$objectClass])) {

            $this->hydratorInstances[$objectClass] = $this->createHydratorFor($objectClass);
        }

        return $this->hydratorInstances[$objectClass];
    }

    /**
     * Factory method to create an hydrator.
     *
     * @param $objectClass Object class to hydrate
     *
     * @return AbstractHydrator
     */
    private function createHydratorFor(string $objectClass) : Hydrator
    {
        $hydrator = new Hydrator();

        return $hydrator;
    }
}

/*function someFunc()
{
    [
        'meta' => [
            'person.address' => [
                'class' => 'Address'
            ]
        ]
    ]

    [
        'Namespace\Person' => [
            'name' => 'string',
            'address' => 'Namespace\Address',
        ],
        'Namespace\Address' => [
            'nr' => 'int',
            'street' => 'string',
            'city' => 'string',
        ],
        'Namespace\ExtendedAddress' => [
            '_parent_class' => 'Namespace\Address',
            'phone' => 'string',
        ]
    ]
}
*/
/**
 * @Cdm\Config({
 *      "source-collection"={
 *          "source"={
 *               id="personSource",
 *               class="Kassko\Example\PersonSource",
 *               method="getData",
 *               args="#id",
 *               lazyLoading=true,
 *               supplySeveralFields=true
 *          }
 *      }
 *
 * })
 * @Cdm\SourceCollection({
 *      @Cdm\Source(
 *          id="personSource",
 *          class="Kassko\Example\PersonSource",
 *          method="getData",
 *          args="#id",
 *          lazyLoading=true,
 *          supplySeveralFields=true
 *      )
 * })
 */
/*class Person
{

}
*/
