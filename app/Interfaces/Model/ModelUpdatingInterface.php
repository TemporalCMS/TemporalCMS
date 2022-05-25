<?php

namespace App\Interfaces\Model;

interface ModelUpdatingInterface{

    public function updateFirstRow(Array $data, $column = "id");

    public function updateLastRow(Array $data, $column = "id");

}