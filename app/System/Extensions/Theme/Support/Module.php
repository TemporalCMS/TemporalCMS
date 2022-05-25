<?php

namespace App\System\Extensions\Theme\Support;

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
        if(theme()->getThemeList() == null)
            return false;

        if(theme()->isDefault())
            return false;

        return theme()->getCurrent()->first()['id'] == $this->eid ? true : false;
    }
}