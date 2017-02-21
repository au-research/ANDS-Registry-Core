<?php
/**
 * Created by PhpStorm.
 * User: lwoods
 * Date: 20/02/2017
 * Time: 10:27 AM
 */

namespace ANDS\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;


class ConceptsCommandTest extends \CommandsTestClass
{
    public function testExecute()
    {


        $application = new Application();
        $application->add(new ConceptsCommand());

        $command = $application->find('concepts:index');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '-f' => 'tests/resources/conceptsIndex/test_tree.json'
            ]
        );
        // echo $commandTester->getDisplay();
        $this->assertRegExp('/test_tree.json/', $commandTester->getDisplay());

        // ...
    }
} 