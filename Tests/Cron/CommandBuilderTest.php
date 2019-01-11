<?php
/**
 * This file is part of the SymfonyCronBundle package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Cron;
use Effiana\CronBundle\Cron\Manager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CommandBuilderTest extends WebTestCase
{
    public function testRenderEnvironment()
    {
        $env = rand();
        $builder = new \Effiana\CronBundle\Cron\CommandBuilder($env);

        $this->assertRegExp(sprintf('/--env=%s$/', $env), $builder->build(''));
    }

    public function testEnv()
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $builder = $kernel->getContainer()->get('Effiana\CronBundle\Cron\CommandBuilder');

        $this->assertRegExp(sprintf('/ --env=%s$/', 'test'), $builder->build(''));
    }
}
