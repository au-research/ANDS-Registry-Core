<?php


use ANDS\DOI\Formatter\StringFormatter;

class StringFormatterTest extends PHPUnit_Framework_TestCase
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
            "[MT001] DOI 104/2 was successfully minted.<br />verbosemessage<br/>url"
        );
    }

    /**
     * Helper method for generating a new XML Formater
     *
     * @return StringFormatter
     */
    private function getFormatter()
    {
        $formatter = new StringFormatter();
        return $formatter;
    }
}