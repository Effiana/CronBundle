<?php
/**
 * This file is part of the SymfonyCronBundle package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronRunCommandTest extends WebTestCase
{
    public function testNoJobs()
    {
        $manager = $this->getMockBuilder('Effiana\CronBundle\Cron\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager
            ->expects($this->once())
            ->method('saveReports')
            ->with($this->isType('array'));

        $resolver = $this->getMockBuilder('Effiana\CronBundle\Cron\Resolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver
            ->expects($this->any())
            ->method('resolve')
            ->will($this->returnValue(array()));

        $executor = $this->getMockBuilder('Effiana\Cron\Executor\Executor')
            ->disableOriginalConstructor()
            ->getMock();

        $command = $this->getCommand($manager, $executor, $resolver);

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        $this->assertContains('time:', $commandTester->getDisplay());
    }

    public function testOneJob()
    {
        $manager = $this->getMockBuilder('Effiana\CronBundle\Cron\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager
            ->expects($this->once())
            ->method('saveReports')
            ->with($this->isType('array'));

        $job = new \Effiana\Cron\Job\ShellJob();

        $resolver = $this->getMockBuilder('Effiana\CronBundle\Cron\Resolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver
            ->expects($this->any())
            ->method('resolve')
            ->will($this->returnValue(array(
                        $job
                    )));
        $executor = $this->getMockBuilder('Effiana\Cron\Executor\Executor')
            ->disableOriginalConstructor()
            ->getMock();
        $command = $this->getCommand($manager, $executor, $resolver);

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        $this->assertContains('time:', $commandTester->getDisplay());
    }

    public function testNamedJob()
    {
        $this->expectException('InvalidArgumentException');
        $manager = $this->getMockBuilder('Effiana\CronBundle\Cron\Manager')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver = $this->getMockBuilder('Effiana\CronBundle\Cron\Resolver')
            ->disableOriginalConstructor()
            ->getMock();
        $executor = $this->getMockBuilder('Effiana\Cron\Executor\Executor')
            ->disableOriginalConstructor()
            ->getMock();

        $command = $this->getCommand($manager, $executor, $resolver);

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'job' => 'jobName',
            ));

        $this->assertContains('time:', $commandTester->getDisplay());
    }

    protected function getCommand($manager, $executor, $resolver)
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $kernel->getContainer()->set('Effiana\CronBundle\Cron\Manager', $manager);
        $kernel->getContainer()->set('Effiana\CronBundle\Cron\Resolver', $resolver);

        $application = new Application($kernel);
        $application->add(new \Effiana\CronBundle\Command\CronRunCommand($manager, $executor, $resolver));

        return $application->find('cron:run');
    }
}
