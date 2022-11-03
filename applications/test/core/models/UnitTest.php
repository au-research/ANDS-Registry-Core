<?php

namespace ANDS\Test;

use \ReflectionClass as ReflectionClass;
use \ReflectionMethod as ReflectionMethod;

/**
 * Class UnitTest
 *
 * @package ANDS\Test
 * @author: Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class UnitTest
{

    public $ci;
    private $name;
    private $note;
    public $benchmark;

    /**
     * UnitTest constructor.
     */
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->benchmark = array();
        $this->nameMapping = array();
        $this->reset();
    }

    public function setUpBeforeClass()
    {

    }

    public function tearDownAfterClass()
    {

    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    /**
     * Resets the value of name and note used for reporting
     * Uses after every asserts
     *
     * @void
     */
    public function reset()
    {
        $this->setName(get_class($this));
        $this->setNote("");
    }

    /**
     * Entry function
     *
     * Run all the function that starts with test (case sensitive)
     * and run all their assertions
     *
     * @return mixed
     */
    public function runTests($specificTestFunction = false)
    {
        try {
            $this->ci->load->library('unit_test');
            $this->ci->unit->init();
            $this->setUpBeforeClass();
            $testableFunctions = get_class_methods($this);
            if ($specificTestFunction && method_exists($this, $specificTestFunction)) {
                $testableFunctions = [$specificTestFunction];
            }
            foreach ($testableFunctions as $function) {
                if (startsWith($function, 'test')) {
                    try {
                        $this->setUp();
                        $this->ci->benchmark->mark('start');
                        $this->$function();
                        $this->ci->benchmark->mark('end');
                        $this->benchmark[$function] = $this->ci->benchmark->elapsed_time('start', 'end', 5);
                        $this->tearDown();
                    } catch (\Exception $e) {
                        $message = $e->getMessage();
                        if (!$message) {
                            $message = $e->getTraceAsString();
                        }
                        $this->ci->unit->run(false, true, $function, "Exception: ". $message);
                    }
                }
            }
            $this->tearDownAfterClass();
        } catch (\Exception $e) {
            $this->ci->unit->run(false, true, $this->getName(), "Exception: ". $e->getMessage());
        }

        // returns the correct time value for the function executed
        $CIUnitTestResult = $this->ci->unit->result();
        try {
            foreach ($CIUnitTestResult as &$result) {
                if (array_key_exists($result['Test Name'], $this->nameMapping)) {
                    $methodName = $this->nameMapping[$result['Test Name']];
                    $result["Time"] = $this->benchmark[$methodName];
                }
            }
        } catch (\Exception $e) {
            $this->ci->unit->run(false, true, $this->getName(), $e->getMessage());
        }

        return $CIUnitTestResult;
    }

    /**
     * Assert if the input is true/exists
     *
     * @param $input
     * @return $this
     */
    public function assertTrue($input)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run(
            $input,
            true,
            $this->getName() . " Assert $input is true",
            $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert if the input is false
     *
     * @param $input
     * @return $this
     */
    public function assertFalse($input)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($input, false, $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert if left and right is equals
     *
     * @param $left
     * @param $right
     * @return $this
     */
    public function assertEquals($left, $right)
    {
        $this->getReflectorInfo();

        $name = $this->getName();
        if (!is_array($left) && !is_array($right)) {
            $name .= " : $left equals $right";
        }

        if (is_array($left) && is_array($right)) {
            $name .= "Array(".count($left).") equals Array(".count($right).")";
        }

        $this->ci->unit->run(
            $left, $right,
            $name,
            $this->getNote()
        );
        $this->reset();
        return $this;
    }


    /**
     * Assert if left and right is different
     *
     * @param $left
     * @param $right
     * @return $this
     */
    public function assertNotEquals($left, $right)
    {
        $this->getReflectorInfo();

        $name = $this->getName();
        if (!is_array($left) && !is_array($right)) {
            $name .= " : $left equals $right";
        }

        if (is_array($left) && is_array($right)) {
            $name .= "Array(".count($left).") equals Array(".count($right).")";
        }

        $this->ci->unit->run(
            $left != $right,
            'is_true',
            $name,
            $this->getNote()
        );
        $this->reset();
        return $this;
    }

    /**
     * Assert object is of a right instance
     *
     * @param $obj
     * @param $instance
     * @return $this
     */
    public function assertInstanceOf($obj, $instance)
    {
        $this->getReflectorInfo();
        $result = $obj instanceof $instance;
        $this->ci->unit->run($result, 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert left is greater than right
     *
     * @param $left
     * @param $right
     * @return $this
     */
    public function assertGreaterThan($left, $right)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run(
            $left > $right,
            'is_true',
            $this->getName(). " $left is greater than $right",
            $this->getNote()
        );
        $this->reset();
        return $this;
    }

    /**
     * Assert left is less than right
     *
     * @param $left
     * @param $right
     * @return $this
     */
    public function assertLessThan($left, $right)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($left < $right, 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert left is less than or equals to right
     * @param $left
     * @param $right
     * @return $this
     */
    public function assertLessThanOrEqual($left, $right)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($left <= $right, 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert input is null
     *
     * @param $input
     * @return $this
     */
    public function assertNull($input)
    {
        $type = gettype($input);
        $this->getReflectorInfo();
        $this->ci->unit->run(
            is_null($input),
            'is_true',
            $this->getName() . " asserting $type is null",
            $this->getNote()
        );
        $this->reset();
        return $this;
    }

    /**
     * Assert left is the same as right
     *
     * @param $left
     * @param $right
     * @return $this
     */
    public function assertSame($left, $right)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($left === $right, 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }


    /**
     * Assert left is greater than or equals to right
     *
     * @param $left
     * @param $right
     * @return $this
     */
    public function assertGreaterThanOrEqual($left, $right)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run($left >= $right, 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert if the input array is empty
     *
     * @param $input
     * @return $this
     */
    public function assertEmpty($input)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run(is_array_empty($input), 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert if the input array count is equals to
     *
     * @param $count
     * @param $input
     * @return $this
     */
    public function assertCount($count, $input)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run(sizeof($input), $count, $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }


    /**
     * Assert that needle is contain in the haystack
     *
     * @param $needle
     * @param $haystack
     * @return $this
     */
    public function assertContains($needle, $haystack)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run(
            in_array($needle, $haystack),
            'is_true',
            $this->getName() . " assert $needle contains in [". implode(',', $haystack)."]",
            $this->getNote()
        );
        $this->reset();
        return $this;
    }

    public function assertRegExp($pattern, $subject)
    {
        $this->getReflectorInfo();

        if (is_array($subject)) {
            $subject = implode(" ", $subject);
        }

        $match = preg_match($pattern, $subject);
        $this->ci->unit->run($match > 0, 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }

    /**
     * Assert that an array has a key
     *
     * @param $key
     * @param $array
     * @return $this
     */
    public function assertArrayHasKey($key, $array)
    {
        $this->getReflectorInfo();
        $this->ci->unit->run(array_key_exists($key, $array), 'is_true', $this->getName(), $this->getNote());
        $this->reset();
        return $this;
    }


    /**
     * Setting the Name and Note of the caller function
     * with their corresponding PHPDoc
     * Using debug_backtrace step 2 because this is called by asserts
     *
     * @void
     */
    private function getReflectorInfo()
    {
        $className = get_class($this);
        $reflector = new ReflectionClass($className);
        $methodCalled = debug_backtrace()[2]['function'];
        $methodReflector = $reflector->getMethod($methodCalled);
        $docBlock = $this->processPHPDoc($methodReflector);
        if ($docBlock) {
            if ($docBlock['name']) {
                $this->setName($docBlock['name']);
                $this->nameMapping[$docBlock['name']] = $methodCalled;
            } else {
                $this->setName($methodCalled);
            }
            if ($docBlock['note']) {
                $this->setNote($docBlock['note']);
            }
        } else {
            $this->reset();
        }
    }

    /**
     * Helper function for getReflectorInfo
     *
     * Extract the PHPDoc on top of a function and
     * returns a list of structured data reflecting the PHPDoc
     *
     * @param ReflectionMethod $reflect
     * @return array|null
     */
    private function processPHPDoc(ReflectionMethod $reflect)
    {
        $phpDoc = array('name' => null, 'note' => null, 'params' => array(), 'return' => null);
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
            if ((strpos($line, '@') === 0) && (preg_match('#^(@\w+.*?)(\n)(?:@|\r?\n|$)#s', $parsedDocComment,
                    $matches))
            ) {
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
                } elseif ($matches3[1] == 'note') {
                    $phpDoc['note'] = $value;
                } elseif ($matches3[1] == 'name') {
                    $phpDoc['name'] = $value . ' (' . $reflect->getName() . ')';
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


    /**
     * @return array
     */
    public function getBenchmark()
    {
        return $this->benchmark;
    }

}