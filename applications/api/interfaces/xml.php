<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APP_PATH . 'interfaces/_interface.php';

class XMLInterface extends FormatHandler
{
    public $params, $options, $formatter;

    public function __construct() {
        $ci =& get_instance();
        $ci->output->set_header('Content-type: application/xml');
    }

    public function display($payload)
    {
        echo "<?xml version=\"1.0\"?>" . NL;
        echo "<response>" . NL;
        echo $this->json_to_xml($payload);
        echo "</response>";
    }

    public function error($message)
    {
        echo '<?xml version="1.0" ?>' . NL;
        echo '<response type="error">' . NL;
        echo htmlentities($message);
        echo '</response>';
    }

    public function output_mimetype()
    {
        return 'application/xml';
    }

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
