<?php
/**
 * This file is part of the SymfonyCronBundle package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Effiana\CronBundle\Cron;

use Doctrine\ORM\EntityManagerInterface;
use Effiana\Cron\Report\JobReport;
use Effiana\CronBundle\Entity\CronJob;
use Effiana\CronBundle\Entity\Repository\CronJobRepository;
use Effiana\CronBundle\Entity\CronReport;
use Doctrine\Bundle\DoctrineBundle\Registry as RegistryInterface;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class Manager
{
    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct($registry)
    {
        $this->manager = $registry->getManagerForClass(CronJob::class);
    }

    /**
     * @return CronJobRepository
     */
    protected function getJobRepo(): CronJobRepository
    {
        return $this->manager->getRepository(CronJob::class);
    }

    /**
     * @param CronReport[] $reports
     */
    public function saveReports(array $reports): void
    {
        /** @var JobReport $report */
        foreach ($reports as $report) {
            $dbReport = new CronReport();
            $dbReport->setJob($report->getJob()->raw);
            $dbReport->setOutput(implode("\n", (array) $report->getOutput()));
            $dbReport->setExitCode($report->getJob()->getProcess()->getExitCode());
            $startTime = number_format($report->getStartTime(), 2, '.', '');

            $dbReport->setRunAt(\DateTime::createFromFormat('U.u', (string)$startTime));
            $dbReport->setRunTime($report->getEndTime() - $startTime);
            $this->manager->persist($dbReport);
        }
        $this->manager->flush();
    }

    /**
     * @return CronJob[]
     */
    public function listJobs(): array
    {
        return $this->getJobRepo()
            ->createQueryBuilder('cronJob')
            ->orderBy('cronJob.name', 'ASC')
            ->getQuery()->getArrayResult();
    }

    /**
     * @return CronJob[]
     */
    public function listEnabledJobs(): array
    {
        return $this->getJobRepo()
            ->findBy(array(
                'enabled' => 1,
            ), array(
                'name' => 'asc',
            ));
    }

    /**
     * @param CronJob $job
     */
    public function saveJob(CronJob $job): void
    {
        $this->manager->persist($job);
        $this->manager->flush();
    }

    /**
     * @param  string  $name
     * @return CronJob|object
     */
    public function getJobByName($name)
    {
        return $this->getJobRepo()
            ->findOneBy(array(
                'name' => $name,
            ));
    }

    /**
     * @param CronJob $job
     */
    public function deleteJob(CronJob $job): void
    {
        $this->manager->remove($job);
        $this->manager->flush();
    }
}
