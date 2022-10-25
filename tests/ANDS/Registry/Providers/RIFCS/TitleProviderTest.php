<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\RIFCS\TitleProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class TitleProviderTest extends \RegistryTestClass
{
    /** @test **/
    public function it_should_get_the_first_primary_non_name_part()
    {
        $rawNames = [
            ['@attributes' => ['type' => 'non-primary'], 'value' => 'non'],
            ['@attributes' => ['type' => 'primary'], 'value' => 'right']
        ];
        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'activity');
        $this->assertEquals('right', $titles['displayTitle']);
        $this->assertEquals('right', $titles['listTitle']);
    }

    /** @test **/
    public function it_should_get_the_first_primary_name_part()
    {
        $rawNames = [
            ['@attributes' => ['type' => 'non-primary'], 'value' => 'non'],
            [
                '@attributes' => ['type' => 'primary'],
                'value' => [
                    ['@attributes' => ['type' => 'a'], 'value' => '1'],
                    ['@attributes' => ['type' => 'b'], 'value' => '2'],
                    ['@attributes' => ['type' => 'c'], 'value' => '3']
                ]
            ]
        ];
        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'activity');
        $this->assertEquals('1', $titles['displayTitle']);
        $this->assertEquals('1', $titles['listTitle']);
    }

    /** @test **/
    public function it_should_order_correctly_for_party()
    {
        $rawNames = [
            ['@attributes' => ['type' => 'non-primary'], 'value' => 'non'],
            [
                '@attributes' => ['type' => 'primary'],
                'value' => [
                    ['@attributes' => ['type' => 'family'], 'value' => 'Hime'],
                    ['@attributes' => ['type' => 'given'], 'value' => 'Gary'],
                    ['@attributes' => ['type' => 'given'], 'value' => 'R'],
                    ['@attributes' => ['type' => 'title'], 'value' => 'A/Pr']
                ]
            ]
        ];

        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'party');
        $this->assertEquals("A/Pr, Gary R., Hime", $titles['listTitle']);
        $this->assertEquals("A/Pr Gary R Hime", $titles['displayTitle']);
    }

    /** @test **/
    public function it_should_title_and_suffix()
    {
        $rawNames = [
            ['@attributes' => ['type' => 'non-primary'], 'value' => 'non'],
            [
                '@attributes' => ['type' => 'primary'],
                'value' => [
                    ['@attributes' => ['type' => 'family'], 'value' => 'Bloggs'],
                    ['@attributes' => ['type' => 'given'], 'value' => 'Joel'],
                    ['@attributes' => ['type' => 'suffix'], 'value' => 'PhD'],
                    ['@attributes' => ['type' => 'title'], 'value' => 'Mr']
                ]
            ]
        ];

        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'party');
        $this->assertEquals("Mr, Joel., Bloggs, PhD", $titles['listTitle']);
        $this->assertEquals("Mr Joel Bloggs PhD", $titles['displayTitle']);
    }

    /** @test **/
    public function it_should_get_first_name_found()
    {
        $rawNames = [
            ['@attributes' => ['type' => 'non-primary'], 'value' => 'non'],
            ['@attributes' => ['type' => 'given'], 'value' => 'non1'],
            ['@attributes' => ['type' => 'blah'], 'value' => 'non2'],
        ];

        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'collection');
        $this->assertEquals("non", $titles['listTitle']);
        $this->assertEquals("non", $titles['displayTitle']);
    }

    /** @test **/
    public function it_should_get_first_primary_namePart_found_for_party()
    {
        $rawNames = [
            [
                'value' => [
                    [
                        '@attributes' => ['type' => 'superior'],
                        'value' => '(AUTestingRecords)Reverse Relationships Internal Related Info "isManagerOf"'
                    ],
                    [
                        '@attributes' => ['type' => 'alternative'],
                        'value' => 'UTS:eResearch IT Services Group'
                    ]
                ],
                '@attributes' => ['type' => 'primary']
            ],
        ];

        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'party');
        $this->assertEquals('(AUTestingRecords)Reverse Relationships Internal Related Info "isManagerOf"', $titles['listTitle']);
        $this->assertEquals('(AUTestingRecords)Reverse Relationships Internal Related Info "isManagerOf"', $titles['displayTitle']);
    }

    /** @test **/
    public function it_should_get_primary_single_namePart()
    {
        $rawNames = [
            [
                '@attributes' => ['type' => 'primary'],
                'value' => [
                    '@attributes' => ['type' => 'primary'],
                    'value' => 'Primary'
                ]
            ]
        ];
        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'party');
        $this->assertEquals('Primary', $titles['listTitle']);
        $this->assertEquals('Primary', $titles['displayTitle']);
    }

    /** @test **/
    public function it_should_find_first_name_part_without_attribute()
    {
        $rawNames = [
            [
                'value' => [
                    [
                        '@attributes' => ['type' => 'full'],
                        'value' => 'Fish'
                    ]
                ]
            ]
        ];
        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'collection');
        $this->assertEquals('Fish', $titles['listTitle']);
        $this->assertEquals('Fish', $titles['displayTitle']);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_find_first_name_part_without_ttr()
    {
        $rawNames = [
            [
                'value' => [
                    'Thing',
                    [
                        '@attributes' => ['type' => 'full'],
                        'value' => 'Fish'
                    ]
                ],
                '@attributes' => [
                    'type' => 'primary'
                ]
            ]
        ];
        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'collection');
        $this->assertEquals('Thing', $titles['listTitle']);
        $this->assertEquals('Thing', $titles['displayTitle']);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_find_first_name_part_without_attr_without_primary()
    {
        $rawNames = [
            [
                'value' => [
                    'Thing',
                    [
                        '@attributes' => ['type' => 'full'],
                        'value' => 'Fish'
                    ]
                ]
            ]
        ];

        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'collection');
        $this->assertEquals('Thing', $titles['listTitle']);
        $this->assertEquals('Thing', $titles['displayTitle']);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_find_first_name_part_without_attr_without_primary_reverse()
    {
        $rawNames = [
            [
                'value' => [
                    [
                        '@attributes' => ['type' => 'full'],
                        'value' => 'Fish'
                    ],
                    'Thing'
                ]
            ]
        ];

        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'collection');
        $this->assertEquals('Fish', $titles['listTitle']);
        $this->assertEquals('Fish', $titles['displayTitle']);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_join_superior_and_subordinate_names()
    {
        $rawNames = [
            [
                'value' => [
                    [
                        '@attributes' => ['type' => 'superior'],
                        'value' => 'SUPERIOR'
                    ],
                    [
                        '@attributes' => ['type' => 'subordinate'],
                        'value' => 'SUBORDINATE'
                    ]
                ],
                '@attributes' => [
                    'type' => 'primary'
                ]
            ]
        ];
        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'party');
        $this->assertEquals('SUPERIOR : SUBORDINATE', $titles['displayTitle']);
        $this->assertEquals('SUPERIOR : SUBORDINATE', $titles['listTitle']);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_get_primary_title()
    {
        $rawNames = [
            ['value' => 'Kuerschner', '@attributes' => ['value' => 'primary']]
        ];
        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'Collection');
        $this->assertEquals('Kuerschner', $titles['listTitle']);
    }

    /** @test */
    public function it_should_get_alternative_name()
    {
        $rawNames = [
            ['@attributes' => ['type' => 'primary'], 'value' => 'non'],
            [
                '@attributes' => ['type' => 'alternative'],
                'value' => [
                    ['@attributes' => ['type' => 'family'], 'value' => 'Bloggs'],
                    ['@attributes' => ['type' => 'given'], 'value' => 'Joel'],
                    ['@attributes' => ['type' => 'suffix'], 'value' => 'PhD'],
                    ['@attributes' => ['type' => 'title'], 'value' => 'Mr']
                ]
            ],
            [
                '@attributes' => ['type' => 'abbreviated'],
                'value' => 'Another'
            ]
        ];

        $titles = TitleProvider::getTitlesFromRaw($rawNames, 'party');
        $this->assertEquals("non", $titles['listTitle']);
        $this->assertEquals("non", $titles['listTitle']);
        $this->assertArrayHasKey('alternativeTitles', $titles);
        $this->assertContains("Mr Joel Bloggs PhD", $titles['alternativeTitles']);
    }

    /** @test */
    public function test_on_party_record_titles()
    {
        // https://test.ands.org.au/api/registry/object/86064/core
        $record = $this->ensureKeyExist("http://nla.gov.au/nla.party-512931AUT3");
        $titles = TitleProvider::get($record);
        $this->assertContains("Alexander Öpik", $titles['alt_titles']);
    }

    public function testGetIndexableArray()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $index = TitleProvider::getIndexableArray($record);
        $this->assertEquals("Collection with all RIF v1.6 elements (primaryName)", $index['display_title']);
        $this->assertEquals("Collection with all RIF v1.6 elements (primaryName)", $index['list_title']);
        $this->assertContains("alternativeName", $index['alt_display_title']);
    }

}
