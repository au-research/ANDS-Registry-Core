<?php
/**
 * Created by PhpStorm.
 * User: lwoods
 * Date: 20/02/2017
 * Time: 10:27 AM
 */

namespace ANDS\Commands;

use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrDocument;
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
                '-f' => 'tests/resources/conceptsIndex/test_tree.json',
                '-t' => 'GCMD'
            ]
        );
        // echo $commandTester->getDisplay();
        $this->assertRegExp('/test_tree.json/', $commandTester->getDisplay());

    }

    public function testIndex()
    {
        $client = new SolrClient('devl.ands.org.au', '8983');
        $client->setCore('concepts');

        // Adding document
        $client->add(
            new SolrDocument([
                'id' => 'test-rd'
            ])
        );
        $client->commit();
    }
} 