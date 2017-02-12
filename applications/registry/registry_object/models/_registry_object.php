<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Registry Object PHP object
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/datasource
 * @subpackage helpers
 */
class _registry_object extends ExtensionBoilerplate {
	
	public static $extensions = array(); // temporary reference to this class's extension(s)
	
	// Minimum set of fields here, let our extensions handle most of the fields?
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	public $id;
	
	function __construct($id = NULL, $core_attributes_only = FALSE)
	{
		
		// Get our CI instance
		$this->_CI =& get_instance(); 

		// Prepare the extension list based on files in our extensions directory not starting with an underscore
		if (count(self::$extensions) == 0)
		{
			$this->_CI->load->helper('directory');
			$map = directory_map(REGISTRY_APP_PATH.'registry_object/models/extensions/');
			include_once('extensions/_base.php');
			foreach($map AS $file)
			{
				if (!preg_match("/\.php$/",$file))
				{
					continue;
				}
				if (substr($file, 0, 1) != '_')
				{
					include_once('extensions/'.$file);
					$extension_name = str_replace(".php","",$file);
					self::$extensions[] = $extension_name;
				}
			}
		}
		
		parent::__construct();
		
		// Setup our object id
		if (!is_numeric($id) && !is_null($id)) 
		{
			throw new Exception("Registry Object Wrapper must be initialised with a numeric Identifier");
		}
		$this->id = $id;

		// Load up all our extensions (effectively providing multiple class inheritence)
		foreach(self::$extensions AS $extension)
		{
			$this->_extends($extension);
		}
		
		// Initialise the object if we haven't done so yet!
		if (!is_null($id))
		{
			$this->init($core_attributes_only);
		}
		
	}

}

// BEN'S WITCHCRAFT
abstract class ExtensionBoilerplate
{
	public $extended_objects = array();
    
    public function __construct() {
    
    }

    public function __destruct() {
    	// Explicitly clean up our extensions...
    	foreach($this->extended_objects AS $class => $instance)
    	{
			unset($this->extended_objects[$class]);
    	}
    }
	
    protected function _extends($class) { //the $class is put to enforce passing class name (otherwise class name can be ommited and no error would be rased)
        $args = func_get_args();
        $class = array_shift($args);
		if (!class_exists($class."_Extension"))
		{
			include ''.strtolower($class).'.php';
		}
        $reflection_object = new \ReflectionClass($class."_Extension");//reflection has to be used so arguments can be provided to the constructor
        $this->extended_objects[$class] = $reflection_object->newInstanceArgs(array($this));
		//var_dump($this->extended_objects);
    }
	    
    public function __get($property) {
    	
        foreach ($this->extended_objects as $object) {
            if (isset($object->$property)) {
                return $object->$property;
            }
        }
		foreach ($this->extended_objects as $object) {
            if (method_exists($object, "getAttribute"))
            {
                return $object->getAttribute($property);
            }
        }
        //it is good to be strict...
        throw new \Exception(sprintf('Trying to get %s property on object of class %s.',$property,get_class($this)));
    }
    
    public function __set($property,$value) {
        foreach ($this->extended_objects as $object) {
            if (isset($object->$property)) { //variable variable
                $object->$property = $value;
                return;
            }
        }
		foreach ($this->extended_objects as $object) {
            if (method_exists($object, "setAttribute"))
            {
            	call_user_func_array(array($object, "setAttribute"), array($property, $value));
                return;
            }
        }
        //it is good to be strict...
        throw new \Exception(sprintf('Trying to set %s property on object of class %s.',$property,get_class($this)));
    }
    
    public function __isset($property) {
        foreach ($this->extended_objects as $object) {
            if (isset($object->$property)) {
                return true;
            }
        }
        return false;
    }
    
    public function __unset($property) {
        //it is good to be strict...
        throw new \Exception(sprintf('Trying to unset %s property on object of class %s. In strict classes unsetting is nto allowed.',$property,get_class($this)));
    }
    
    public function __call($method,$args) {
        foreach ($this->extended_objects as $object) {
            if (method_exists($object,$method)) {
              	return call_user_func_array(array($object,$method),$args);
            }
        }
		//echo "NSM: $method() "; var_dump($args);
        throw new \Exception(sprintf('Dynamic call of unexistant method %s on instance of class %s.',$method,get_class($this)));
    }
    
    public static function __callStatic($method,$args) {
        $class = get_called_class();//late static binding
        if (isset($class::$_extends)) { //then there is static extension
            foreach ($class::$_extends as $extended_class) {
                if (method_exists($extended_class,$method)) {
                    return call_user_func_array(array($extended_class,$method),$args);
                }
            }
        }
        throw new \Exception(sprintf('Static call of unexistant method %s on instance of class %s.',$method,$class));
    }
    
    public function __invoke() {
        $args = func_get_args();
        foreach ($this->extended_objects as $object) {
            if (method_exists($object,'__invoke')) {
                return call_user_func_array(array($object,'__invoke'),$args);
            }
        }
        throw new \Exception(sprintf('Invoking an instance of %s as function.',get_class($this)));
    }
	
	public function __toString()
	{
		$return = '';
		
		$return = sprintf("%s (%s) [%d]", $this->getAttribute("key", TRUE), $this->getAttribute("status", TRUE),$this->id) . BR;
		
		if (isset($this->attributes) && !is_null($this->attributes))
		{
			foreach ($this->attributes AS $attribute)
			{
				$return .= sprintf("%s", $attribute) . BR;
			}
		}
		
		return $return;	
	}
	
} 
