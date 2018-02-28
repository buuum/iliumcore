<?php

namespace Ilium\Dependency;

use Symfony\Component\Yaml\Yaml;

class Config
{

    private $path;
    private $url;
    private $scheme;
    private $host;
    private $query;
    private $configs = [];

    public function __construct($path)
    {
        $this->url = $path;
        $url = parse_url($path);
        $this->scheme = $url['scheme'];
        $this->host = $url['host'];
        $this->path = $url['path'];
        $this->query = $url['query'] ?? null;

        $this->prepareConfig();
    }

    public function get($name)
    {
        if (isset($this->configs[$name])) {
            return $this->configs[$name];
        } elseif (strpos($name, '.') !== false) {
            $loc = &$this->configs;
            foreach (explode('.', $name) as $part) {
                $loc = &$loc[$part];
            }
            return $loc;
        }
        return false;
    }

    public function set($name, $value)
    {
        $this->configs[$name] = $value;
    }

    protected function prepareConfig()
    {
        $root_path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';

        $host_config = str_replace('www', '', $this->host);

        $config = Yaml::parse(file_get_contents($root_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.yml'));
        $host_config = empty($host_config) ? $config["default_host"] : $host_config;
        $config_file = $root_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $host_config . '.yml';
        
        if(!file_exists($config_file)){
            preg_match('/\/.*?\//', $this->path, $matches);

            if (!empty($matches[0])) {
                $host_config = $host_config . str_replace_last('/', '', str_replace_first('/', '_', $matches[0]));
            }

            $config_file = $root_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $host_config . '.yml';
        }
        
        $vars = file_exists($config_file) ? Yaml::parse(file_get_contents($config_file)) : [];
        $config = ['root_path' => $root_path] + $vars + $config;
        date_default_timezone_set($config['timezone']);

        ////////////////
        // set scopes //
        ////////////////
        $config['scope'] = false;
        if (!empty($config['scopes'])) {
            $config['scope'] = $this->getScope($config['scopes']);
        }

        $this->configs = $config;

    }

    protected function getScope($scopes)
    {
        foreach ($scopes as $key => $options) {
            if (!preg_match($options['regex'], $this->path, $matches)) {
                continue;
            }
            return $options;
        }
        return false;
    }


}
