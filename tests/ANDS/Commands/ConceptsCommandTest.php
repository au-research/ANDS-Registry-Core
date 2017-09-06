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

    public function testExecute()
    {
        // TODO: fix this test
        $this->markTestSkipped();

        $application = new Application();
        $application->add(new ConceptsCommand());

        $command = $application->find('concepts:index');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '-f' => 'tests/resources/conceptsIndex/test_tree.json',
                '-t' => 'TESTING'
            ]
        );
        // echo $commandTester->getDisplay();
        $this->assertRegExp('/test_tree.json/', $commandTester->getDisplay());
        $this->assertRegExp('/TESTING/', $commandTester->getDisplay());
        $this->assertRegExp('/You have indexed concepts/', $commandTester->getDisplay());

    }


    public function testIndexedCount()
    {
        $client = new SolrClient('devl.ands.org.au', '8983');
        $client->setCore('concepts');
        $result =  $client->request('GET', 'concepts/select', ['q'=>'type:TESTING']);
        $client->commit();
//        $this->assertEquals($result['response']['numFound'],63);
    }

     public function testDeleteByType()
      {
          $client = new SolrClient('devl.ands.org.au', '8983');
          $client->setCore('concepts');

          // removing concepts document
          $client->removeByQuery('type:TESTING');

          $client->commit();
          $result =  $client->request('GET', 'concepts/select', ['q'=>'type:TESTING']);
          $client->commit();
//          $this->assertEquals($result['response']['numFound'],0);
      }
} 