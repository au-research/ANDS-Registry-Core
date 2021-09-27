<?php

namespace ANDS\Registry\API\Controller;


interface  RestfulController
{
    public function index();
    public function show();
    public function update();
    public function destroy();
    public function add();
}