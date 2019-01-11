<?php
/**
 * This file is part of the SymfonyCronBundle package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Effiana\CronBundle\Command;

use Effiana\CronBundle\Cron\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronListCommand extends Command
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:list')
            ->setDescription('List all available crons');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $jobs = $this->queryJobs();
        $headers = ['Cron schedule', 'Name', 'Command', 'Enabled'];
        $rows = [];
        foreach ($jobs as $job) {
            $rows[] = [
                $job['schedule'],
                $job['name'],
                $job['command'],
                $job['enabled'] ? 'x' : '',
            ];
        }


        $io->table($headers, $rows);
    }

    /**
     * @return array
     */
    protected function queryJobs()
    {
        return $this->manager->listJobs();
    }
}
