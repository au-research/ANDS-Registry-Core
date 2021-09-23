<?php


use ANDS\DOI\Formatter\JSONFormatter;

class JSONFormatterTest extends PHPUnit_Framework_TestCase
{
    /** @test * */
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
        $this->assertJsonStringEqualsJsonString(
            $message,
            json_encode([
                'response' => [
                    'type' => 'success',
                    'responsecode' => 'MT001',
                    'message' => 'DOI 104/2 was successfully minted.',
                    'doi' => '104/2',
                    'url' => 'url',
                    'app_id' => 'app_id',
                    'verbosemessage' => 'verbosemessage',
                    'code' => 200
                ]
            ], true)
        );
    }

    /**
     * Helper method for generating a new XML Formater
     *
     * @return JSONFormatter
     */
    private function getFormatter()
    {
        $formatter = new JSONFormatter();
        return $formatter;
    }
}