<?php
namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class TemplateService
{
    public function getTemplate(){
        $videaYaml = Yaml::parseFile('../config/videa.yaml');
        return $videaYaml['global']['theme'];
    }
}