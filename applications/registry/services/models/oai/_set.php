<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class _set
{
  public $spec;
  public $name;
  public $val;
  public $source;

  private $valid_sources = array("datasource", "group", "class");

  /**
   * @ignore
   */
  public function __construct($ns, $spec, $name)
  {
      $this->spec = sprintf("%s:%s", $ns, urlencode($spec));
      $this->name = $name;
      $this->val = $spec;
      if (!in_array($ns, $this->valid_sources))
      {
	  throw new Oai_BadArgument_Exceptions("unknown set spec '$this->spec'");
      }
      else
      {
	  $this->source = $ns;
      }
  }

  public function asSet()
  {
    return sprintf("<set>\n\t\t\t<setSpec>%s</setSpec>\n\t\t\t<setName>%s</setName>\n\t\t</set>",
		   $this->spec,
		   $this->name);
  }

  public function asRef()
  {
      return sprintf("<setSpec>%s</setSpec>", $this->spec);
  }

  /**
   * @return xml string representation of the oai set
   */
  public function __toString()
  {
    return sprintf("<set>\n\t\t\t<setSpec>%s</setSpec>\n\t\t\t<setName>%s</setName>\n\t\t</set>",
		   $this->spec,
		   $this->name);
  }

}

?>