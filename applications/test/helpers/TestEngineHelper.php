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
        $this->name = get_class($this);
    }

    public function assertTrue($input)
    {
        $this->ci->unit->run($input, true, $this->getName());
        return $this;
    }

    public function assertFalse($input)
    {
        $this->ci->unit->run($input, false, $this->getName());
        return $this;
    }

    public function assertEquals($left, $right)
    {
        $this->ci->unit->run($left, $right, $this->getName(), $this->getNote());
        return $this;
    }

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

    public function test1()
    {
        $this->setName('test1')
            ->assertTrue(1 + 1)
            ->assertTrue(true);
    }

    public function test2()
    {
        $this->setName('test2');
        $this->assertTrue(true);
    }

    public function test_get_config_item()
    {
        $this->setName('testConfig');
        $config = get_config_item('base_url');
        $this->setNote('base_url must equals to $config')
            ->assertEquals($config, "http://minhdev.ands.org.au/test/22");
    }

}