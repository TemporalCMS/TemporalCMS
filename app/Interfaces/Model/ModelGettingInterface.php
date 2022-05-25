<?php

namespace App\Interfaces\Model;

interface ModelGettingInterface{

    public function getFirstRow($column = "id");

    public function getLastRow($column = "id");

}