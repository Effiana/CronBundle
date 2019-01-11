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

namespace Effiana\CronBundle\Services;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class CommandChoiceList.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
class CommandParser
{
    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var array
     */
    private $excludedNamespaces;

    /**
     * @param Kernel $kernel
     * @param array  $excludedNamespaces
     */
    public function __construct(Kernel $kernel, array $excludedNamespaces = [])
    {
        $this->kernel = $kernel;
        $this->excludedNamespaces = array_merge($excludedNamespaces, [
            '_global',
            'scheduler',
            'server',
            'container',
            'config',
            'generate',
            'init',
            'router',
            'doctrine',
            'cache',
            'cron',
            'security',
            'swiftmailer',
            'make',
            'idb_queue',
            'debug',
            'assetic',
            'assets',
            'lexik',
            'lint',
            'translation',
            'fos',
            'migrate'
        ]);
    }

    /**
     * Execute the console command "list" with XML output to have all available command.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getCommands()
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(
            array(
                'command' => 'list',
                '--format' => 'xml',
                '-v' => '',
            )
        );
        $output = new StreamOutput(fopen('php://memory', 'w+'));
        $application->run($input, $output);
        rewind($output->getStream());
        return $this->extractCommandsFromXML(stream_get_contents($output->getStream()));
    }

    /**
     * Extract an array of available Symfony command from the XML output.
     *
     * @param $xml
     *
     * @return array
     */
    private function extractCommandsFromXML($xml)
    {
        if ('' == $xml) {
            return array();
        }
        $node = new \SimpleXMLElement($xml);
        $commandsList = array();
        foreach ($node->namespaces->namespace as $namespace) {
            $namespaceId = (string) $namespace->attributes()->id;
            if (!in_array($namespaceId, $this->excludedNamespaces)) {
                foreach ($namespace->command as $command) {
                    $commandsList[$namespaceId][(string) $command] = (string) $command;
                }
            }
        }
        return $commandsList;
    }
}
