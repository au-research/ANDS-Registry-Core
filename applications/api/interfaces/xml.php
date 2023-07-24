<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APP_PATH . 'interfaces/_interface.php';

/**
 * Formatter for XML interface
 * for use with the API application
 *
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class XMLInterface extends FormatHandler
{

    public function __construct()
    {
        $ci = &get_instance();
        $ci->output->set_header('Content-type: application/xml');
    }

    /**
    * display a normal message
    * @param  mixed $payload
    * @return application/xml
    */
    public function display($payload)
    {
        echo "<?xml version=\"1.0\"?>" . NL;
        $content = "<response>". $this->json_to_xml($payload) ."</response>";

        // special condition for scholix export
        // TODO refactor
        if (array_key_exists('scholix', $payload[0])) {
            $content = $payload[0]['scholix'];
        }
        echo $content;
    }

    /**
    * display an error message
    * @param  mixed $payload
    * @return application/xml
    */
    public function error($message)
    {
        echo '<?xml version="1.0" ?>' . NL;
        echo '<response type="error">' . NL;
        echo htmlentities($message);
        echo '</response>';
    }

    /**
     * Application mimetype
     * @return string
     */
    public function output_mimetype()
    {
        return 'application/xml';
    }

    /**
     * Transform an object to XML
     * @param  mixed $obj
     * @return xml
     */
    private function json_to_xml($obj)
    {
        $str = "";
        if (is_null($obj)) {
            return "<null/>";
        } elseif (is_array($obj)) {
            //a list is a hash with 'simple' incremental keys
            $is_list = array_keys($obj) == array_keys(array_values($obj));
            if (!$is_list) {
                foreach ($obj as $k => $v) {
                    $str .= "<$k>" . $this->json_to_xml($v) . "</$k>" . NL;
                }

            } else {
                $str .= "<list>";
                foreach ($obj as $v) {
                    $str .= "<item>" . $this->json_to_xml($v) . "</item>" . NL;
                }

                $str .= "</list>";
            }
            return $str;
        } elseif (is_string($obj)) {
            return htmlspecialchars($obj) != $obj ? "<![CDATA[$obj]]>" : $obj;
        } elseif (is_scalar($obj)) {
            return $obj;
        } else {
            throw new Exception("Unsupported type $obj");
        }

    }
}
