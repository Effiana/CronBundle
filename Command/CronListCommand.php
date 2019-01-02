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

use Effiana\CronBundle\Entity\CronJob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronListCommand extends ContainerAwareCommand
{
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
        return $this->getContainer()->get('cron.manager')->listJobs();
    }
}
