<?php

namespace App\Http\Traits;

use GuzzleHttp\Client;
use Illuminate\Foundation\Application;

trait Shortcuts
{
    /**
     * @param $class_name
     * @param string $path_class
     * @return bool|Application|mixed
     * @throws \Exception
     */
    public function access($class_name, string $path_class = "App\Http\Controllers", \Closure $closure = null)
    {
        $fusion = $path_class . "\\" . $class_name;
        if(empty($class_name) || empty($path_class))
            return false;
        if(!class_exists($fusion))
            throw new \Exception($fusion . " doesn't exist.");
        if(!isset($closure))
            return app($fusion);

        $closure = call_user_func($closure);
        if($closure == true) return app($fusion);
        return $closure;
    }

    /**
     * @param $alert
     * @param $message
     * @param null $custom
     */
    public function sendJsonRequest($alert, $message, $custom = null)
    {
        switch($alert) {
            case "error":
                echo response()->json(['alert' => 'danger', 'message' => $message, 'custom' => $custom, 'now' => getLoadPageTime()])->getContent();
                break;
            case "info":
                echo response()->json(['alert' => 'info', 'message' => $message, 'custom' => $custom, 'now' => getLoadPageTime()])->getContent();
                break;
            case "success":
                echo response()->json(['alert' => 'success', 'message' => $message, 'custom' => $custom, 'now' => getLoadPageTime()])->getContent();
                break;
            case "warning":
                echo response()->json(['alert' => 'warning', 'message' => $message, 'custom' => $custom, 'now' => getLoadPageTime()])->getContent();
                break;
        }
    }

    /**
     * @param $data
     * @param array $replace
     * @return array|string|null
     */
    public function lang($data, $replace = [])
    {
        return __($data, $replace);
    }

    /**
     * @param $uri
     * @param string $method
     * @param array $option
     * @param array $config
     * @return string
     */
    public function callApi($uri, $method = "get", $option = [], $config = [])
    {
        $client = new Client($config);

        switch ($method) {
            case "post":
                $res = $client->post($uri, $option);
                return ($res->getBody())->getContents();
                break;
            case "get":
                $res = $client->get($uri, $option);
                return ($res->getBody())->getContents();
                break;
            case "put":
                $res = $client->put($uri, $option);
                return ($res->getBody())->getContents();
                break;
            case "patch":
                $res = $client->patch($uri, $option);
                return ($res->getBody())->getContents();
                break;
            case "delete":
                $res = $client->delete($uri, $option);
                return ($res->getBody())->getContents();
                break;
            default:
                return "error, $method doesn't exist (callApi)";
                break;
        }
    }
}