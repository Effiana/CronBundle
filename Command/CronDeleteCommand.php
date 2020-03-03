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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronDeleteCommand extends Command
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
        $this->setName('cron:delete')
            ->setDescription('Delete a cron job')
            ->addArgument('job', InputArgument::REQUIRED, 'The job to delete');
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

        if ($job->getEnabled()) {
            throw new \InvalidArgumentException('The job should be disabled first.');
        }

        $output->writeln(sprintf('<info>You are about to delete "%s".</info>', $job->getName()));

        $question = new ConfirmationQuestion('<question>Delete this job</question> [N/y]: ', false, '/^(y)/i');

        if (!$this->getQuestionHelper()->ask($input, $output, $question)) {
            return;
        }

        $this->manager->deleteJob($job);

        $output->writeln(sprintf('<info>Cron "%s" was deleted.</info>', $job->getName()));
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

    /**
     * @return QuestionHelper
     */
    private function getQuestionHelper()
    {
        return $this->getHelperSet()->get('question');
    }
}
