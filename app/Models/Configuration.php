<?php

namespace App\Models;

use App\Interfaces\Model\ModelGettingInterface;
use App\Interfaces\Model\ModelUpdatingInterface;
use Geeky\Database\CacheQueryBuilder;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model implements ModelGettingInterface, ModelUpdatingInterface
{
    use CacheQueryBuilder;

    protected $fillable = ['website_name', 'version', 'email', 'theme'];
    protected $table = 'configuration';

    public function create(array $attributes = [])
    {
        return parent::create($attributes);
    }

    public function find($id, $columns = ['*'])
    {
        return parent::find($id, $columns);
    }

    public function get($columns = ['*'])
    {
        return parent::get($columns);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return parent::where($column, $operator, $value, $boolean);
    }

    public function count($columns = '*')
    {
        return parent::count($columns);
    }

    public function getFirstRow($column = "id")
    {
        if(!$this->count())
            return null;

        return $this->orderBy($column, "asc")->first();
    }

    public function getLastRow($column = "id")
    {
        if(!$this->count())
            return null;

        return $this->latest($column)->first();
    }

    public function updateFirstRow($data, $column = "id")
    {
        if(!$this->count())
            return null;

        $row = $this->getFirstRow($column);

        return $row->where("id", $row->id)->update($data);
    }

    public function updateLastRow($data, $column = "id")
    {
        if(!$this->count())
            return null;

        $row = $this->getLastRow($column);

        return $row->where("id", $row->id)->update($data);
    }
}