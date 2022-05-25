<?php

namespace App\Models;

use App\Interfaces\Model\ModelGettingInterface;
use App\Interfaces\Model\ModelUpdatingInterface;
use App\System\Module\TXLaratrust\Models\LaratrustPermission;
use Geeky\Database\CacheQueryBuilder;

class Permission extends LaratrustPermission implements ModelUpdatingInterface, ModelGettingInterface
{
    use CacheQueryBuilder;

    public static function add(string $name, string $display_name, string $extension_type, string $extension_eid, string $description = null): string
    {
        if(self::whereName($name)->count())
            return false;

        return self::insert([
            "name" => $name,
            "display_name" => $display_name,
            "extension_linked" => json_encode(["type" => $extension_type, "eid" => $extension_eid]),
            "description" => $description,
            "created_at" => now()
        ]);
    }

    public static function addOrUpdate(string $name, string $display_name, string $extension_type, string $extension_eid, string $description = null): string
    {
        if(self::whereName($name)->count())
            return self::whereName($name)->update([
                "display_name" => $display_name,
                "extension_linked" => json_encode(["type" => $extension_type, "eid" => $extension_eid]),
                "description" => $description,
                "updated_at" => now()
            ]);

        return self::insert([
            "name" => $name,
            "display_name" => $display_name,
            "extension_linked" => json_encode(["type" => $extension_type, "eid" => $extension_eid]),
            "description" => $description,
            "created_at" => now()
        ]);
    }

    public function isLinkedToExtension()
    {
        return $this->extension_linked != null ? true : false;
    }

    public function isDisable()
    {
        return $this->is_disable ? true : false;
    }

    public function getExtensionLinkedJson($key)
    {
        if(!$this->isLinkedToExtension())
            return null;

        return json_decode($this->extension_linked, true)[$key];
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

    /**
     * @inheritDoc
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
