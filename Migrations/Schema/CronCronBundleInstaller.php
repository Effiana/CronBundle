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

namespace Effiana\CronBundle\Migrations\Schema;

use BrandOriented\DatabaseBundle\Migration\Installation;
use BrandOriented\DatabaseBundle\Migration\QueryBag;
use Doctrine\DBAL\Schema\Schema;


class CronCronBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v2_0_3';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createBrandOrientedMigrationsDataTable($schema);
    }

    /**
     * Create brandoriented_migrations_data table
     *
     * @param Schema $schema
     */
    protected function createBrandOrientedMigrationsDataTable(Schema $schema)
    {
       if(!$schema->hasTable('cron_job')) {

           $table = $schema->createTable('cron_job');
           $table->addColumn('id', 'integer', ['notnull' => true, 'autoincrement' => true]);
           $table->addColumn('name', 'string', ['default' => null, 'notnull' => true, 'length' => 191]);
           $table->addColumn('command', 'string', ['default' => null, 'notnull' => true, 'length' => 1024]);
           $table->addColumn('schedule', 'string', ['default' => null, 'notnull' => true, 'length' => 191]);
           $table->addColumn('description', 'string', ['default' => null, 'notnull' => true, 'length' => 191]);
           $table->addColumn('enabled', 'boolean', ['notnull' => true]);
           $table->addUniqueIndex(['name'], 'un_name');

           $table->setPrimaryKey(['id']);
       }
       if(!$schema->hasTable('cron_report')) {

           $table = $schema->createTable('cron_report');
           $table->addColumn('id', 'integer', ['notnull' => true, 'autoincrement' => true]);
           $table->addColumn('run_at', 'datetime', ['notnull' => true]);
           $table->addColumn('run_time', 'float', ['notnull' => true]);
           $table->addColumn('exit_code', 'integer', ['notnull' => true]);
           $table->addColumn('text', 'text', ['notnull' => true]);
           $table->addColumn('job_id', 'integer', ['notnull' => true]);
           $table->setPrimaryKey(['id']);

           $table->addForeignKeyConstraint('cron_job', ['job_id'], ['id']);
       }
    }
}
