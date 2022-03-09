<?php

use ANDS\RegistryObject;

class MyceliumTestClass extends RegistryTestClass
{
    public function setUp()
    {
        parent::setUp();

        // create the datasource in Mycelium
        $myceliumServiceClient = new \ANDS\Mycelium\MyceliumServiceClient(\ANDS\Util\Config::get('mycelium.url'));
        $myceliumServiceClient->createDataSource($this->dataSource,null);
    }

    public function tearDown()
    {
        if (!$this->dataSource) {
            return;
        }

        $myceliumServiceClient = new \ANDS\Mycelium\MyceliumServiceClient(\ANDS\Util\Config::get('mycelium.url'));

        // find records that belong to the test data source
        $records = RegistryObject::where('data_source_id', $this->dataSource->id);

        // delete records in mycelium
        if ($records->count() > 0) {
            foreach ($records as $record) {
                $this->myceliumDelete($record);
            }
        }

        // delete the datasource in Mycelium
        $myceliumServiceClient->deleteDataSource($this->dataSource);

        // run parent::tearDown after to remove test artefacts
        parent::tearDown();
    }

    public function myceliumInsert(RegistryObject $record){
        //we need to insert the record into mycelium
        $myceliumServiceClient = new \ANDS\Mycelium\MyceliumServiceClient(\ANDS\Util\Config::get('mycelium.url'));
        $result = $myceliumServiceClient->createNewImportRecordRequest("testingup");
        $request = json_decode($result->getBody()->getContents(), true);
        $myceliumServiceClient->importRecord($record,$request['id']);
        $myceliumServiceClient->indexRecord($record);
    }

    public function myceliumDelete(RegistryObject $record){
        //we need to delete the record from mycelium
        $myceliumServiceClient = new \ANDS\Mycelium\MyceliumServiceClient(\ANDS\Util\Config::get('mycelium.url'));
        $result = $myceliumServiceClient->createNewDeleteRecordRequest();
        $request = json_decode($result->getBody()->getContents(), true);
        $myceliumServiceClient->deleteRecord($record->id,$request['id']);
    }
}