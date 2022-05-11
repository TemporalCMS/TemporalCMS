<?php

namespace App;

class Update {
    private $name = "Neutron";
    private $version = "2.1.9";
    private $message = "TemporalCMS a new CMS for Minecraft Servers";
    private $authors = ["Skogrine"];

    private $last_update;

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getAuthors()
    {
        if(empty($this->authors))
            return "Skogrine";

        $data = "";

        foreach($this->authors as $author) {
            $data .= $author . ", ";
        }

        return rtrim($data, ", ");
    }
    
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    public function afterUpdate()
    {
    }

}