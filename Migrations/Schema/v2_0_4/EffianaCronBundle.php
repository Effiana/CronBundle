<?php
/**
 * This file is part of the BrandOriented package.
 *
 * (c) Brand Oriented sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Dominik Labudzinski <dominik@labudzinski.com>
 */

namespace Effiana\CronBundle\Migrations\Schema\v2_0_4;

use BrandOriented\DatabaseBundle\Migration\Column;
use BrandOriented\DatabaseBundle\Migration\Migration;
use BrandOriented\DatabaseBundle\Migration\QueryBag;
use BrandOriented\DatabaseBundle\Migration\Extension\RenameExtension;
use BrandOriented\DatabaseBundle\Migration\Extension\RenameExtensionAwareInterface;
use BrandOriented\DatabaseBundle\Migration\OrderedMigrationInterface;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Schema;

class EffianaCronBundle implements Migration, RenameExtensionAwareInterface, OrderedMigrationInterface
{
    /**
     * @var RenameExtension
     */
    protected $renameExtension;

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if($schema->hasTable('cron_job')) {
            $this->renameExtension->renameTable(
                $schema,
                $queries,
                'cron_job',
                'effiana_cron_job'
            );
        }
        if($schema->hasTable('cron_report')) {
            $this->renameExtension->renameTable(
                $schema,
                $queries,
                'cron_report',
                'effiana_cron_report'
            );
        }
    }

}