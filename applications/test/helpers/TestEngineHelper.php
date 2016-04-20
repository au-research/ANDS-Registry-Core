<?php

class UnitTest
{

    private $ci;
    private $name;
    private $note;

    /**
     * UnitTest constructor.
     */
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->reset();
    }

    /**
     * Resets the value of name and note used for reporting
     * Uses after every asserts
     * @void
     */
    public function reset() {
        $this->setName(get_class($this));
        $this->setNote("");
    }

    /**
     * Entry function
     *
     * Run all the function that starts with test (case sensitive)
     * and run all their assertions
     * @return mixed
     */
    public function runTests()
    {
        $this->ci->load->library('unit_test');
        $testableFunctions = get_class_methods($this);
        foreach ($testableFunctions as $function) {
            if (startsWith($function, 'test')) {
                $this->$function();
            }
        }
        return $this->ci->unit->result();
    }

    public function assertTrue($input)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($input, true, $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    public function assertFalse($input)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($input, false, $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    public function assertEquals($left, $right)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($left, $right, $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Setting the Name and Note of the caller function
     * with their corresponding PHPDoc
     * Using debug_backtrace step 2 because this is called by asserts
     * @void
     */
    private function getReflectorInfo()
    {
        $className = get_class($this);
        $reflector = new ReflectionClass($className);
        $methodCalled =  debug_backtrace()[2]['function'];
        $methodReflector = $reflector->getMethod($methodCalled);
        $docBlock = $this->processPHPDoc($methodReflector);
        if ($docBlock) {
            if ($docBlock['name']) {
                $this->setName($docBlock['name']);
            }
            if ($docBlock['note']) {
                $this->setNote($docBlock['note']);
            }
        }
    }

    /**
     * Helper function for getReflectorInfo
     *
     * Extract the PHPDoc on top of a function and
     * returns a list of structured data reflecting the PHPDoc
     * @param ReflectionMethod $reflect
     * @return array|null
     */
    private function processPHPDoc(ReflectionMethod $reflect)
    {
        $phpDoc = array('name' => null, 'note'=>null,'params' => array(), 'return' => null);
        $docComment = $reflect->getDocComment();
        if (trim($docComment) == '') {
            return null;
        }
        $docComment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docComment);
        $docComment = ltrim($docComment, "\r\n");
        $parsedDocComment = $docComment;
        $lineNumber = $firstBlandLineEncountered = 0;
        while (($newlinePos = strpos($parsedDocComment, "\n")) !== false) {
            $lineNumber++;
            $line = substr($parsedDocComment, 0, $newlinePos);

            $matches = array();
            if ((strpos($line, '@') === 0) && (preg_match('#^(@\w+.*?)(\n)(?:@|\r?\n|$)#s', $parsedDocComment, $matches))) {
                $tagDocblockLine = $matches[1];
                $matches2 = array();

                if (!preg_match('#^@(\w+)(\s|$)#', $tagDocblockLine, $matches2)) {
                    break;
                }

                $matches3 = array();
                if (!preg_match('#^@(\w+)\s+([\w|\\\]+)(?:\s+(\$\S+))?(?:\s+(.*))?#s', $tagDocblockLine, $matches3)) {
                    break;
                }

                $value = implode(' ', array_slice(array_filter($matches3), 2));
                if ($matches3[1] == 'param') {
                    $phpDoc['params'][] = array('name' => $matches3[3], 'type' => $matches3[2]);
                } elseif ($matches3[1] == 'note'){
                    $phpDoc['note'] = $value;
                } elseif ($matches3[1] == 'name'){
                    $phpDoc['name'] = $value . ' ('.$reflect->getName().')';
                } else {
                    if (strtolower($matches3[1]) == 'return') {
                        $phpDoc['return'] = array('type' => $matches3[2]);
                    }
                }

                $parsedDocComment = str_replace($matches[1] . $matches[2], '', $parsedDocComment);
            }
        }
        return $phpDoc;
    }

    /**
     * Setting the name of the test, for reporting
     *
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

}

class TestEngineHelper extends UnitTest
{

    /**
     * @name Sample Test Case
     * @note Some note to go under here
     */
    public function test1()
    {
        $this->assertTrue(1 + 1);
        $this->assertTrue(true);
    }

    public function test2()
    {
        $this->assertTrue(true);
    }

    /**
     * @name Test get_config_item
     * @note base_url must equals to config
     */
    public function test_get_config_item()
    {
        $config = get_config_item('base_url');
        $this->assertEquals($config, "http://minhdev.ands.org.au/test/22");
    }

}