<?php

namespace Oro\Bundle\ShippingBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\FrontendBundle\Migration\UpdateNoteAssociationKindMigration;

class MigrateNotes extends UpdateNoteAssociationKindMigration
{
    /**
     * {@inheritdoc}
     */
    protected function getRenamedClasses(Schema $schema)
    {
        return [
            'OroB2B\Bundle\ShippingBundle\Entity\FreightClass' => 'Oro\Bundle\ShippingBundle\Entity\FreightClass',
            'OroB2B\Bundle\ShippingBundle\Entity\LengthUnit' => 'Oro\Bundle\ShippingBundle\Entity\LengthUnit',
            'OroB2B\Bundle\ShippingBundle\Entity\ShippingRule' => 'Oro\Bundle\ShippingBundle\Entity\ShippingRule',
            'OroB2B\Bundle\ShippingBundle\Entity\ProductShippingOptions' => 'Oro\Bundle\ShippingBundle\Entity' .
                '\ProductShippingOptions',
        ];
    }
}
