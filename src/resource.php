<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Parser;

class ResourceLoader
{
    private $resource = [];

    public function __construct()
    {
        $yaml = (new Parser())->parse(file_get_contents(__DIR__ . '/resource.yml'));
        if (isset($yaml['default'])) $this->setResource($yaml['default']);

        $request_uri = preg_replace('~^/|/$~', '', $_SERVER['REQUEST_URI']);
        $keys = array_keys($yaml);
        foreach ($keys as $key) {
            if (preg_match('~(.*)\*$~', $key, $matches) && preg_match("~^$matches[1]~", $request_uri)) {
                $this->setResource($yaml[$key]);
            } elseif (preg_match("~$key~", $request_uri)) {
                $this->setResource($yaml[$key]);
            }
        }
    }

    private function setResource(array $data)
    {
        foreach ($data as $tag => $resources) {
            if (!isset($this->resource[$tag])) {
                $this->resource[$tag] = $resources;
            } else {
                $this->resource[$tag] = array_merge($this->resource[$tag], $resources);
            }
        }
    }

    public function output($tag)
    {
        if (!isset($this->resource[$tag]) || !is_array($this->resource[$tag])) return;

        foreach ($this->resource[$tag] as $file_path) {
            if (preg_match('/.js$/', $file_path)) {
                echo '<script type="text/javascript" src="/js/' . $file_path . '"></script>' . PHP_EOL;
            } elseif (preg_match('/.css$/', $file_path)) {
                echo '<link rel="stylesheet" type="text/css" href="/css/' . $file_path . '">' . PHP_EOL;
            }
        }
    }
}