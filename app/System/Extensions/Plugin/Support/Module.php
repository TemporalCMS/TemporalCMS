<?php

namespace App\System\Extensions\Plugin\Support;

class Module {

    private $eid;

    /**
     * Module constructor.
     * @param string $eid
     */
    public function __construct(string $eid)
    {
        $this->eid = $eid;
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        if(!plugin()->exists($this->eid))
            return false;

        if(plugin()->getPluginEnable() == null)
            return false;

        return plugin()->getPluginEnable()->has($this->eid);
    }
}