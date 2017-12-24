<?php

namespace Appzcoder\LumenRoutesList;

use Illuminate\Console\Command;

class RoutesCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all registered routes.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        global $app;

        $routeCollection = property_exists($app, 'router') ? $app->router->getRoutes() : $app->getRoutes();
        $rows = array();
        foreach ($routeCollection as $route) {
            $rows[] = [
                'verb' => $route['method'],
                'path' => $route['uri'],
                'namedRoute' => $this->getNamedRoute($route['action']),
                'controller' => $this->getController($route['action']),
                'action' => $this->getAction($route['action']),
                'middleware' => $this->getMiddleware($route['action']),
                'params' => $this->getParamList($route['action']),
            ];
        }

        $headers = array('Verb', 'Path', 'NamedRoute', 'Controller', 'Action', 'Middleware');
        $this->table($headers, $rows);
    }

    /**
     * @param array $action
     * @return string
     */
    protected function getNamedRoute(array $action)
    {
        return (!isset($action['as'])) ? "" : $action['as'];
    }

    /**
     * @param array $action
     * @return mixed|string
     */
    protected function getController(array $action)
    {
        if (empty($action['uses'])) {
            return 'None';
        }

        return current(explode("@", $action['uses']));
    }

    /**
     * @param array $action
     * @return string
     */
    protected function getAction(array $action)
    {
        if (!empty($action['uses'])) {
            $data = $action['uses'];
            if (($pos = strpos($data, "@")) !== false) {
                return substr($data, $pos + 1);
            } else {
                return "METHOD NOT FOUND";
            }
        } else {
            return 'Closure';
        }
    }

    /**
     * @param array $action
     * @return string
     */
    protected function getMiddleware(array $action)
    {
        return (isset($action['middleware']))
            ? (is_array($action['middleware']))
            ? join(", ", $action['middleware'])
            : $action['middleware'] : '';
    }
    
    /**
     * Read method comment with reflection
     * get only the line with @paramList and return this.
     * @param array $action
     * @return string    
    ** /
    protected function getParamList($action) {
        if(!isset($action['uses'])) {
            return "";
        }
        list($class, $method) = explode("@", $action['uses']);

        if($class[0] != "\\") {
            $class = "\\".$class;
        }

        $reflector = new \ReflectionMethod($class,$method);
        $comments = $reflector->getDocComment();
        $paramList = "";
        if(!empty($comments)) {
            $comments = preg_split('/$\R?^/m', $comments);
            if(!empty($comments)) {
                foreach ($comments as $comment) {
                    if (strstr($comment, "@paramList")) {
                        list($pre,$paramList) = explode("@paramList", $comment);
                        $paramList = trim($paramList);
                    }
                }
            }
        }
        return $paramList;
    }

}
