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

use Effiana\Cron\Validator\CrontabValidator;
use Effiana\CronBundle\Cron\Manager;
use Effiana\CronBundle\Entity\CronJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronCreateCommand extends Command
{
    private $manager;
    /**
     * @var CrontabValidator
     */
    private $validator;

    public function __construct(Manager $manager, CrontabValidator $validator)
    {
        $this->manager = $manager;
        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:create')
            ->setDescription('Create a cron job');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $job = new CronJob();

        $output->writeln('');
        $output->writeln('<info>The unique name how the job will be referenced.</info>');

        $question = new Question('<question>Name:</question> ', false);

        $name = $this->getQuestionHelper()->ask($input, $output, $question);
        $this->validateJobName($name);
        $job->setName($name);

        $output->writeln('');
        $output->writeln('<info>The command to execute. You may add extra arguments.</info>');

        $question = new Question('<question>Command:</question> ', false);

        $command = $this->getQuestionHelper()->ask($input, $output, $question);
        $this->validateCommand($command);
        $job->setCommand($command);

        $output->writeln('');
        $output->writeln('<info>The schedule in the crontab syntax.</info>');

        $question = new Question('<question>Schedule:</question> ', false);

        $schedule = $this->getQuestionHelper()->ask($input, $output, $question);
        $this->validateSchedule($schedule);
        $job->setSchedule($schedule);

        $output->writeln('');
        $output->writeln('<info>Some more information about the job.</info>');

        $question = new Question('<question>Description:</question> ', false);

        $description = $this->getQuestionHelper()->ask($input, $output, $question);
        $job->setDescription($description);

        $output->writeln('');
        $output->writeln('<info>Should the cron be enabled.</info>');

        $question = new ConfirmationQuestion('<question>Enable?</question> [y/n]: ', false, '/^(y)/i');

        $enabled = $this->getQuestionHelper()->ask($input, $output, $question);
        $job->setEnabled($enabled);

        $this->manager->saveJob($job);

        $output->writeln('');
        $output->writeln(sprintf('<info>Cron "%s" was created..</info>', $job->getName()));
        return 0;
    }

    /**
     * Validate the job name.
     *
     * @param  string                    $name
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function validateJobName($name)
    {
        if (!$name || strlen($name) == 0) {
            throw new \InvalidArgumentException('Please set a name.');
        }

        if ($this->queryJob($name)) {
            throw new \InvalidArgumentException('Name already in use.');
        }

        return $name;
    }

    /**
     * Validate the command.
     *
     * @param  string                    $command
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function validateCommand($command)
    {
        $parts = explode(' ', $command);
        $this->getApplication()->get((string) $parts[0]);

        return $command;
    }

    /**
     * Validate the schedule.
     *
     * @param  string $schedule
     * @return string
     * @throws \Effiana\Cron\Exception\InvalidPatternException
     */
    protected function validateSchedule($schedule)
    {
        $this->validator->validate($schedule);

        return $schedule;
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
