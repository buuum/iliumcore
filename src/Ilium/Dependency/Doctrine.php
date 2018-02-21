<?php

namespace Ilium\Dependency;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;

class Doctrine
{

    private $doctrine_types = [];
    private $doctrine_mappings = [];
    private $config_db;
    private $proxies_path;
    private $development;

    public function __construct($config_db, $proxies_path, $development = true)
    {
        $this->config_db = $config_db;
        $this->proxies_path = $proxies_path;
        $this->development = $development;
    }

    public function addTypes(array $types)
    {
        $this->doctrine_types = array_merge($this->doctrine_types, $types);;
    }

    public function addMapping($path)
    {
        $this->doctrine_mappings[] = $path;
    }

    public function get()
    {
        $dbParams = array(
            'driver'        => 'pdo_mysql',
            'host'          => $this->config_db->host,
            'user'          => $this->config_db->user,
            'password'      => $this->config_db->pass,
            'dbname'        => $this->config_db->name,
            'charset'       => 'utf8',
            'driverOptions' => [
                1002 => 'SET NAMES utf8'
            ]
        );

        foreach ($this->doctrine_types as $name => $class) {
            Type::addType($name, $class);
        }

        if ($this->development) {
            $cache = new ArrayCache();
        } else {
            $cache = new ArrayCache();
        }

        $config_doctrine = new Configuration();
        $config_doctrine->setMetadataCacheImpl($cache);

        $driverImpl = new XmlDriver($this->doctrine_mappings);
        $config_doctrine->setMetadataDriverImpl($driverImpl);
        $config_doctrine->setQueryCacheImpl($cache);
        $config_doctrine->setProxyDir($this->proxies_path);
        $config_doctrine->setProxyNamespace('Proxies\Symfony');
        $config_doctrine->setAutoGenerateProxyClasses($this->development);

        return EntityManager::create($dbParams, $config_doctrine);
    }

}