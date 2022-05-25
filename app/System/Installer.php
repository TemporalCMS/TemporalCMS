<?php

namespace App\System;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Installer {

    private $link;
    private $path;

    private $fileName;

    private $files;

    /**
     * Installer constructor.
     * @param $link
     * @param $path
     * @param Filesystem $files
     */
    public function __construct($link, $path = "")
    {
        $this->link = $link;
        $this->path = $path;

        $this->fileName = Str::random() . ".zip";

        $this->files = app('files');
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function download()
    {
        if($this->path == "")
            $this->path = storage_path("app/download");

        if(!$this->files->isWritable($this->path))
            throw new \Exception($this->path . " is not writable.");

        $this->files->copy($this->link, $this->path . "/" . $this->fileName);

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function extractTo($to, $createFolder = true)
    {
        $zip = new \ZipArchive();

        $path = $this->path . "/" . $this->fileName;

        if(!$zip->open($path))
            throw new \Exception($path . " can't open");

        if(!$this->files->exists($to) && $createFolder)
            $this->files->makeDirectory($to);

        $zip->extractTo($to);
        $zip->close();

        $this->files->delete($path);

        return true;
    }
}