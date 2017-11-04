<?php

namespace Components;

class Application
{
    public function getThemeConfig()
    {
        $hash = md5(__CLASS__.'/themes/');
        $config = substr($hash, 0, 10);

        return $config;
    }

    public function getTheme()
    {
        if (!file_exists(realpath(dirname(__DIR__)).'/data/configs.json'))
            return 'default';

        $content = file_get_contents(realpath(dirname(__DIR__)).'/data/configs.json');
        $configs = json_decode($content, true);
        if (is_array($configs)){
            return $configs['theme'];
        }

        return 'default';
    }

    public function getParams()
    {
        if (!file_exists(realpath(dirname(__DIR__)).'/data/configs.json'))
            return array();

        $content = file_get_contents(realpath(dirname(__DIR__)).'/data/configs.json');
        $configs = json_decode($content, true);

        if (is_array($configs)){
            return $configs;
        }

        return array();
    }

    public function getDbs()
    {
        $params = self::getParams();

        return (array_key_exists('db', $params) && !empty($params['db']))? $params['db'] : false;
    }

    public function setDb($data)
    {
        if (!file_exists(realpath(dirname(__DIR__)).'/data/configs.json')) {
            $file = fopen(realpath(dirname(__DIR__)).'/data/configs.json', 'w');
            $update = file_put_contents(realpath(dirname(__DIR__)).'/data/configs.json', json_encode(array()));
        }

        $content = file_get_contents(realpath(dirname(__DIR__)).'/data/configs.json');
        $configs = json_decode($content, true);
        $configs['db'] = [
            'connectionString' => 'mysql:host='.$data['host'].';dbname='.$data['dbname'],
            'emulatePrepare' => true,
            'username' => $data['username'],
            'password' => $data['password'],
            'charset' => 'utf8',
            'tablePrefix' => $data['tablePrefix']
        ];

        $update = file_put_contents(realpath(dirname(__DIR__)).'/data/configs.json', json_encode($configs));

        return $update;
    }
    
    public function createDb($data, $settings)
    {
        $base_model = new \Model\BaseModel( ['settings'=>$settings] );
        if (!file_exists(realpath(dirname(__DIR__)).'/data/schema.mysql')) {
            return false;
        }

        $content = file_get_contents(realpath(dirname(__DIR__)).'/data/schema.mysql');

        $patterns = [
            '/{dbname}/', '/{admin_email}/', '/{created_at}/', '/{updated_at}/', '/{site_name}/', '/{tag_line}/', '/{theme}/', '/{site_url}/'
        ];

        if (!isset($data['site_name']))
            $data['site_name'] = 'SlightSite';

        if (!isset($data['tag_line']))
            $data['tag_line'] = 'Website Ringan, Mudah, dan Cepat';

        if (!isset($data['theme']))
            $data['theme'] = 'freelancer';
        
        $replacements = [
            $data['dbname'],
            'admin@'.parse_url($data['base_url'])['host'],
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s"),
            $data['site_name'],
            $data['tag_line'],
            $data['theme'],
            $data['base_url']
        ];

        $content = preg_replace($patterns, $replacements, $content);

        try {
            $conn = new \PDO("mysql:host=".$data['host'].";", $data['username'], $data['password']);
            // set the PDO error mode to exception
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // use exec() because no results are returned
            $conn->exec( $content );
        }
        catch(\Exception $e)
        {
            throw new \Exception( $e->getMessage());
        }

        return true;
    }
}