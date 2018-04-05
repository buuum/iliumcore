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
    private $root_path;

    public function __construct($path)
    {
        $this->url = $path;
        $url = parse_url($path);
        if (empty($url['scheme'])) {
            $this->url = 'http://' . $path;
            $url = parse_url($this->url);
        }
        $this->scheme = $url['scheme'];
        $this->host = $url['host'];
        $this->path = $url['path'];
        $this->query = $url['query'] ?? null;
        $this->root_path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';

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

        $host_config = str_replace('www', '', $this->host);

        $config = Yaml::parse(file_get_contents($this->root_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.yml'));
        $host_config = empty($host_config) ? $config["default_host"] : $host_config;

        $vars = $this->parseConfig($this->getConfigFilePath($host_config));
        $vars = $this->checkConfigs($vars);

        $config = ['root_path' => $this->root_path] + $vars + $config;
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

    protected function checkConfigs($vars)
    {
        if (!empty($vars['configs'])) {
            foreach ($vars['configs'] as $key => $options) {
                if (!preg_match($options['regex'], $this->path, $matches)) {
                    continue;
                }
                $file = $this->getConfigFilePath($options['config_file']);
                return array_merge($vars, $this->parseConfig($file));
            }
        }

        return $vars;
    }

    protected function parseConfig($config_file)
    {
        return file_exists($config_file) ? Yaml::parse(file_get_contents($config_file)) : [];
    }

    protected function getConfigFilePath($name)
    {
        return $this->root_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $name . '.yml';
    }


}