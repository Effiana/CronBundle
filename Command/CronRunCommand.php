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

use Effiana\Cron\Cron;
use Effiana\Cron\Executor\Executor;
use Effiana\Cron\Job\ShellJob;
use Effiana\Cron\Resolver\ArrayResolver;
use Effiana\Cron\Schedule\CrontabSchedule;
use Effiana\CronBundle\Cron\Manager;
use Effiana\CronBundle\Cron\Resolver;
use Effiana\CronBundle\Entity\CronJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronRunCommand extends Command
{
    /**
     * @var Manager
     */
    private $manager;
    /**
     * @var Executor
     */
    private $executor;
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * CronRunCommand constructor.
     * @param Manager $manager
     * @param Executor $executor
     * @param Resolver $resolver
     */
    public function __construct(Manager $manager, Executor $executor, Resolver $resolver)
    {
        $this->manager = $manager;
        $this->executor = $executor;
        $this->resolver = $resolver;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:run')
            ->setDescription('Runs any currently schedule cron jobs')
            ->addArgument('job', InputArgument::OPTIONAL, 'Run only this job (if enabled)')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the current job.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $cron = new Cron();
        $cron->setExecutor($this->executor);
        if ($input->getArgument('job')) {
            $resolver = $this->getJobResolver($input->getArgument('job'), $input->hasOption('force'));
        } else {
            $resolver = $this->resolver;
        }
        $cron->setResolver($resolver);

        $time = microtime(true);
        /** @var \Effiana\Cron\Report\CronReport $dbReport */
        $dbReport = $cron->run();

        while ($cron->isRunning()) {}

        $io->success('time: ' . (microtime(true) - $time));
        $reports = $dbReport->getReports();
        $this->manager->saveReports($reports);

        /** @var \Effiana\Cron\Report\JobReport $report */
        foreach ($reports as $report) {
            if(!empty($report->getError())) {
                $io->error(implode("\n", $report->getError()));
            }
        }
    }

    /**
     * @param  string                    $jobName
     * @param  bool                      $force
     * @return ArrayResolver
     * @throws \InvalidArgumentException
     */
    protected function getJobResolver($jobName, $force = false)
    {
        $dbJob = $this->queryJob($jobName);

        if (!$dbJob) {
            throw new \InvalidArgumentException('Unknown job.');
        }

        $finder = new PhpExecutableFinder();
        $phpExecutable = $finder->find();
        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        $resolver = new ArrayResolver();

        if ($dbJob->getEnabled() || $force) {
            $job = new ShellJob();
            $job->setCommand(escapeshellarg($phpExecutable) . ' bin/console ' . $dbJob->getCommand(), $rootDir);
            $job->setSchedule(new CrontabSchedule($dbJob->getSchedule()));
            $job->raw = $dbJob;

            $resolver->addJob($job);
        }

        return $resolver;
    }

    /**
     * @param  string  $jobName
     * @return CronJob
     */
    protected function queryJob($jobName)
    {
        /** @var CronJob $job */
        $job = $this->manager->getJobByName($jobName);

        return ($job && $job->getEnabled()) ? $job : null;
    }
}
