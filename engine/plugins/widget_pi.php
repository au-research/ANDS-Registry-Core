<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
* Widget Plugin
*
* Install this file as application/plugins/widget_pi.php
*
* @version:     0.2
* $copyright    Copyright (c) Wiredesignz 2009-08-24
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/
class Widget
{
    function run($name) {        
        $args = func_get_args();
        
        require_once APPPATH.'widgets/'.$name.EXT;
        $name = ucfirst($name);
        
        $widget =& new $name();
        return call_user_func_array(array(&$widget, 'run'), array_slice($args, 1));    
    }
    
    function render($view, $data = array()) {
        extract($data);
        include APPPATH.'widgets/views/'.$view.EXT;
    }

    function load($object) {
        $this->$object =& load_class(ucfirst($object));
    }

    function __get($var) {
        static $ci;
        isset($ci) OR $ci = get_instance();
        return $ci->$var;
    }
} 