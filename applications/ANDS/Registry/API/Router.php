<?php

namespace ANDS\Registry\API;

class Router
{
    private $routes = [
        'get' => [], 'post' => [], 'put' => [], 'delete' => [], 'patch' => []
    ];

    private $prefix = "";
    private $controllerNamespace = "ANDS\Registry\API\Controller\\";

    function __construct($prefix = "") {
        $this->prefix = $prefix;
    }
    function __clone() {}

    public function route($methods, $pattern, $callback) {
        $methods = array_map('strtolower', $methods);
        $pattern = $this->prefix.$pattern;
        $pattern = rtrim($pattern, '/').'/';
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        foreach ($methods as $method) {
            $this->routes[$method][] = [$pattern => $callback];
        }
    }

    public function get($pattern, $callback) {
        $this->route(['get'], $pattern, $callback);
    }

    public function post($pattern, $callback)
    {
        $this->route(['get'], $pattern, $callback);
    }

    public function resource($resources, $controller) {
        $resources = explode('.', $resources);
        if (count($resources) ==  1) {
            $this->addResource($resources[0], $controller);
        }
        if (count($resources) == 2) {
            $this->addResource($resources[0].'/(\w+)/'.$resources[1], $controller);
        }
    }

    public function addResource($resource, $controller)
    {
        $this->route(['get'], "$resource/", "$controller@index");
        $this->route(['get'], "$resource/(\\w+)/", "$controller@show");
        $this->route(['post'], "$resource/", "$controller@add");
        $this->route(['put', 'patch'], "$resource/(\\w+)/", "$controller@update");
        $this->route(['delete'], "$resource/(\\w+)/", "$controller@destroy");
    }

    public function execute($url = null, $requestMethod = null) {
        if (!$url) {
            $url = $_SERVER['REQUEST_URI'];
        }

        if (!$requestMethod) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        }

        if ($match = $this->getMatch($url, $requestMethod)) {
            return $this->callAction($match['callback'], $match['params']);
        }

        // no match
        throw new \Exception("No route match $url");
    }

    public function getMatch($url = null, $requestMethod = null)
    {
        if (!$url) {
            $url = $_SERVER['REQUEST_URI'];
        }

        $parsedUrl = parse_url(baseUrl());
        $path = array_key_exists('path', $parsedUrl) ? $parsedUrl['path'] : null;
        if ($path && $path !== "/") {
            $url = str_replace($path, "", $url);
            $url = str_pad($url, strlen($url) + 1, "/", STR_PAD_LEFT);
        }

        if (!$requestMethod) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        }

        foreach ($this->routes as $method => $matches) {
            $requestMethod = strtolower($requestMethod);
            if ($requestMethod != $method) {
                continue;
            }
            foreach ($matches as $match) {
                $pattern = array_keys($match)[0];
                $callback = $match[$pattern];

                // remove everything after ?
                $url = explode('?', $url)[0];

                // ensure last / for matching
                $url = rtrim($url, '/') . '/';
                // var_dump($pattern, $url);
                if (preg_match($pattern, $url, $params)) {
                    array_shift($params);
                    return [
                        'pattern' => $pattern,
                        'url' => $url,
                        'params' => $params,
                        'callback' => $callback
                    ];
                }
            }
        }
        // no match
        return null;
    }

    public function callAction($callback, $params)
    {
        try {
            if (is_string($callback)) {
                // attempt to find a controller
                $split = explode('@', $callback);
                $controller = $split[0];
                $method = $split[1];
                $controller = $this->getControllerClass($controller);
                return call_user_func_array([$controller, $method], array_values($params));
            }
            return call_user_func_array($callback, array_values($params));
        } catch (\Exception $e) {
            throw new \Exception(get_exception_msg($e));
        }

    }

    public function getControllerClass($controller)
    {
        $controllerName = $this->controllerNamespace.$controller;
        try {
            return new $controllerName;
        } catch (\Exception $e) {
            throw new \Exception ("Cannot find controller $controllerName");
        }
    }

    public function show()
    {
        $show = [];
        foreach ($this->routes as $method => $matches) {
            $show[$method] = [];
            foreach ($matches as $match) {
                $show[$method][] = array_keys($match)[0];
            }
        }
        return $show;
    }
}