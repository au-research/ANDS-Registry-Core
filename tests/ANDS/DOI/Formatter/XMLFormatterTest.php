<?php
use ANDS\DOI\Formatter\XMLFormatter;

class XMLFormatterTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_be_able_to_format_a_standard_message()
    {
        $formatter = $this->getFormatter();
        $payload = [
            'type' => 'success',
            'responsecode' => 'MT001',
            'message' => 'DOI was minted successfully',
            'doi' => 'DOI',
            'url' => 'url',
            'app_id' => 'app_id',
            'verbosemessage' => 'verbosemessage'
        ];
        $message = $formatter->format($payload);
        $this->isValidStructure($message);
    }

    /** @test **/
    public function it_should_return_a_mt010_message_for_no_app_id() {
        $formatter = $this->getFormatter();
        $message = $formatter->format([
            'responsecode' => 'MT010',
            'verbosemessage' => 'You must provide an app id to mint a doi'
        ]);
        $this->isValidStructure($message);
    }

    /**
     * Helper method for testing valid DOI Message Response Structure
     *
     * @param $message
     */
    private function isValidStructure($message)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
            <response type=\"{type}\">
            <responsecode>{code}</responsecode>
            <message>{message}</message> 
            <doi>{doi}</doi>
            <url>{url}</url>
            <app_id>{app_id}</app_id>
            <verbosemessage>{verbosemessage}</verbosemessage>
            </response> 
        ";
        $expected = new DOMDocument();
        $expected->loadXML($xml);

        $actual = new DOMDocument();
        $actual->loadXML($message);
        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * Helper method for generating a new XML Formater
     *
     * @return XMLFormatter
     */
    private function getFormatter()
    {
        $formatter = new XMLFormatter();
        return $formatter;
    }
}