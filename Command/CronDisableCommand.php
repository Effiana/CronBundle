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
use Effiana\CronBundle\Entity\CronJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronDisableCommand extends Command
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
        $this->setName('cron:disable')
            ->setDescription('Disable a cron job')
            ->addArgument('job', InputArgument::REQUIRED, 'The job to disable');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $job = $this->queryJob($input->getArgument('job'));

        if (!$job) {
            throw new \InvalidArgumentException('Unknown job.');
        }

        $job->setEnabled(false);

        $this->manager->saveJob($job);

        $output->writeln(sprintf('Cron "%s" disabled', $job->getName()));
        return 0;
    }

    /**
     * @param  string  $jobName
     * @return CronJob
     */
    protected function queryJob($jobName)
    {
        return $this->manager->getJobByName($jobName);
    }
}
