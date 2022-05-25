<?php

namespace App\System;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

abstract class EEvent {

    protected $app;
    protected $files;

    /**
     * EEvent constructor.
     * @param Application $application
     * @param Filesystem $files
     */
    public function __construct(Application $application, Filesystem $files)
    {
        $this->app = $application;
        $this->files = $files;
    }

    /**
     * @param $file
     * @return mixed|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function loadPhpFileEvent($file)
    {
        if ($this->files->exists($file))
            return $this->files->getRequire($file);

        return null;
    }
}