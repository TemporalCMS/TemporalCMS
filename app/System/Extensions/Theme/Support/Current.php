<?php

namespace App\System\Extensions\Theme\Support;

use Illuminate\Foundation\Application;

class Current {

    private $config;

    /**
     * Current constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return !is_null($this->config) ? $this->config['name'] : false;
    }

    /**
     * @return mixed
     */
    public function getEid()
    {
        return !is_null($this->config) ? $this->config['id'] : false;
    }

    /**
     * @return mixed
     */
    public function getAuthors()
    {
        return !is_null($this->config) ? $this->config['author'] : false;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return !is_null($this->config) ? $this->config['version'] : false;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return !is_null($this->config) ? $this->config['description'] : false;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return !is_null($this->config) ? $this->config['slug'] : false;
    }

    /**
     * @return bool
     */
    public function sliderIsEnable()
    {
        return !is_null($this->config) ? (isset($this->config['slider']) && $this->config['slider'] ? true : false) : false;
    }
}