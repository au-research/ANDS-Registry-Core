<?php


use ANDS\DOI\Formatter\ArrayFormatter;

class ArrayFormatterTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_be_able_to_format_a_standard_message()
    {
        $formatter = $this->getFormatter();
        $payload = [
            'type' => 'success',
            'responsecode' => 'MT001',
            'message' => 'DOI was minted successfully',
            'doi' => '104/2',
            'url' => 'url',
            'app_id' => 'app_id',
            'verbosemessage' => 'verbosemessage'
        ];
        $message = $formatter->format($payload);
        $this->assertEquals(
            $message,
            $payload = [
                'type' => 'success',
                'responsecode' => 'MT001',
                'message' => 'DOI 104/2 was successfully minted.',
                'doi' => '104/2',
                'url' => 'url',
                'app_id' => 'app_id',
                'verbosemessage' => 'verbosemessage',
                'code' => 200
            ]
        );
    }

    /**
     * Helper method for generating a new XML Formater
     *
     * @return ArrayFormatter
     */
    private function getFormatter()
    {
        $formatter = new ArrayFormatter();
        return $formatter;
    }
}