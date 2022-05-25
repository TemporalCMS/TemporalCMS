<?php

namespace App\System\Extensions\EGame\Support;

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
        if(egame()->getEGameList() == null)
            return false;

        if(egame()->isDefault())
            return false;

        return egame()->getCurrent()->first()['id'] == $this->eid ? true : false;
    }
}