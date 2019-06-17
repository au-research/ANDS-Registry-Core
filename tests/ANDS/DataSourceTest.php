<?php

use ANDS\DataSource\Harvest;

class DataSourceTest extends RegistryTestClass
{
    /** @test **/
    public function it_should_be_true_sample_test()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_datasource_can_add_a_harvest()
    {
        $now = date('Y-m-d H:i:s');
        $this->dataSource->addHarvest('IDLE', $now, 'HARVEST');
        $this->assertInstanceOf(Harvest::class, $this->dataSource->harvest);

        $harvest = $this->dataSource->harvest;
        $this->assertEquals('IDLE', $harvest->status);
        $this->assertEquals($now, $harvest->next_run);
    }

    /** @test */
    function a_datasource_can_update_information_without_exception()
    {
        // This test is done due to the @@mysql_mode settings
        // There are certain bits in the code will need to be refactored to handle strict mode
        $this->dataSource->addHarvest('IDLE', date('Y-m-d H:i:s'), 'HARVEST');
        $harvest = $this->dataSource->harvest;

        // Test utf-8 encoding (expects no exception)
        $harvest->update([
            'importer_message' => 'Collection of folktales and elicitation data in Sümi (India)'
        ]);

        // set long message status (expects no exception)
        $harvest->update([
            'status' => 'IMPORT - PROCESSING GRANTS NETWORK RELATIONSHIPS'
        ]);
    }
}