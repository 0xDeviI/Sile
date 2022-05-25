<?php


namespace Signite\Core;

require_once "signite-framework/modules/middleware-tool.php";
require_once "signite-framework/config/config.php";
require_once "signite-framework/Modules/middleware-result.php";
require "signite-framework/utils/whoops/vendor/autoload.php";


use Signite\Middleware;
use Signite\Middleware\Auth;
use Signite\Modules;
use Signite\Modules\SigniteMiddlewareTool;
use Signite\Modules\MiddlewareResult;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Exception;
use Closure;

mb_internal_encoding("UTF-8");

class Signite {
    private $_config = array();
    private $_applicationName;
    private $_applicationConfig;

    public function __construct($applicationName) {
        $this->_applicationName = $applicationName;
        $this->checkApplicationExists();

        $this->readConfig();
        $this->initializeApplication();
    }

    public function initializeApplication() {
        require_once "signite-framework/database/connection.php";
        $db = $GLOBALS["connection"];
        $db = $db->connect();
        return true;
    }

    public function redirect($url) {
        header("Location: " . $url);
        exit();
    }

    public function setApplicationDirectoryVisibility($isVisibility, SigniteRouter &$router) {
        if ($isVisibility)
            $router->setAllowedPathAccess($this->getFreshApplicationName() . "/*");
        else
            $router->setDeniedPathAccess($this->getFreshApplicationName() . "/*");
    }

    private function getFreshApplicationName(){
        if (substr($this->_applicationName, -1) !== "/") {
            return $this->_applicationName;
        }
        else {
            return substr($this->_applicationName, 0, -1);
        }
    }

    public function setApplicationConfig($key, $value) {
        $this->_applicationConfig[$key] = $value;
    }

    public function getApplicationConfig($key) {
        return $this->_applicationConfig[$key];
    }

    public function getApplicationName() {
        return $this->_applicationName;
    }

    public function checkApplicationExists() {
        if (!file_exists($this->_applicationName)) {
            throw new Exception("Application '$this->_applicationName' does not exist.");
        }
        $GLOBALS["application"] = &$this->_applicationName;
    }

    public function setTimeZone($timeZone)
    {
        date_default_timezone_set($timeZone);
    }

    public function resetTimeZone(){
        date_default_timezone_set($this->_config["timezone"]);
    }

    private function readConfig(){
        $configData = $GLOBALS["_SIGNITECONFIG"];
        $this->_config = $configData;
    }
}

class StatusError {
    private $_signiteRender;

    public function __construct(SigniteRender $signiteRender) {
        $this->_signiteRender = $signiteRender;
    }

    public function throw($errorView){
        echo $this->_signiteRender->render($errorView["view"], $errorView["params"]);
    }
}

class SigniteRouter {

    private Signite $_signiteApp;
    private $_routes = array();
    private $_requested_url = "";
    private $_requested_path = "";
    private $_assetsDirectory = "";
    private $_allowedPathesAccess = array();
    private $_deniedPathesAccess = array();
    private $_config = array();
    private $_paramRegex = "/\{([a-zA-Z0-9_]+)\}/";
    private $_signiteRender;
    private StatusError $_statusError;
    private $_specialPages = array(
        "explorer" => "signite-framework/pages/explorer.php",
        "404" => [
            "params" => [
                "page-title" => "Damn it, page not found!",
                "code" => "404",
                "title" => "Page not found",
                "description" => "Requested page not found on this server."
            ],
            "view" => "signite-framework/pages/errors/status-code-error.php"
        ]
    );

    public function __construct(Signite $signiteApp)
    {
        $this->_signiteApp = $signiteApp;
        $this->_signiteRender = new SigniteRender();
        $this->initializeObjects();
        $this->readConfig();
        $this->configurePathesAccess();
        $this->setWhoopsErrorHandler();
        $this->makeRoutesGlobal();
        session_start();
    }

    private function initializeObjects(){
        $this->_statusError = new StatusError($this->_signiteRender);
    }

    public function getSpecialPages($pageKey){
        return $this->_specialPages[$pageKey];
    }

    public function setAllowedPathAccess($path) {
        $this->_allowedPathesAccess[] = $path;
    }

    public function setDeniedPathAccess($path) {
        $this->_deniedPathesAccess[] = $path;
    }

    public function setSpecialPages($pageKey, $pagePath){
        $this->_specialPages[$pageKey] = $pagePath;
    }

    private function makeRoutesGlobal()
    {
        $GLOBALS["routes"] = &$this->_routes;
    }

    private function setWhoopsErrorHandler(): bool {
        if ($this->_config["debug"]) {
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();
            return true;
        }
        return false;
    }

    public function setResponseType($type)
    {
        header("Content-Type: " . $type);
    }

    private function configurePathesAccess()
    {
        $this->_allowedPathesAccess = $this->_config["path"]["path_access_allowed"];
        $this->_deniedPathesAccess = $this->_config["path"]["path_access_denied"];
    }

    private function readConfig(){
        $configData = $GLOBALS["_SIGNITECONFIG"];
        $this->_config = $configData;
    }

    public function setAssetsDirectory($assetsDirectory)
    {
        $this->_assetsDirectory = $assetsDirectory;
    }

    public function group($parentPath, $routes, string|array $method) {
        if ($parentPath[strlen($parentPath) - 1] == "/") {
            $parentPath = substr($parentPath, 0, -1);
        }
        foreach ($routes as $route) {
            $methods = $route->getMethod();
            $groupMethod = is_array($method) ? $method : explode("|", $method);
            foreach ($groupMethod as $_method) {
                if (!in_array($_method, $methods)) {
                    $methods[] = $_method;
                }
            }
            $path = $parentPath . ($route->getPath() == '/' ? "" : $route->getPath());
            $paths = $this->getRequestedPaths($parentPath . $route->getPath());
            $pathId = $this->generatePathId($paths);
            $newRoute = new SigniteRoute($path, $methods, $route->getCallback(), $pathId, $paths, $route->getMiddlewares());
            $this->objectRoute($newRoute);
        }
    }

    public function objectRoute(SigniteRoute $route) {
        $this->_routes[$route->getId()] = $route;
    }

    public function croute($path, $callback, string|array $method): SigniteRoute {
        if ($path[strlen($path) - 1] != "/") {
            $path .= "/";
        }
        $paths = $this->getRequestedPaths($path);
        $pathId = $this->generatePathId($paths);
        $route = new SigniteRoute($path, is_array($method) ? $method : explode("|", $method), 
        $callback, $pathId, $paths, []);
        return $route;
    }

    public function route($path, Closure|string $callback, string|array $method): SigniteRoute {
        if ($path[strlen($path) - 1] != "/") {
            $path .= "/";
        }
        
        if (is_string($callback)) {
            $paths = $this->getRequestedPaths($path);
            $pathId = $this->generatePathId($paths);
            $this->_routes[$pathId] = new SigniteRoute($path, is_array($method) ? $method : explode("|", $method), 
            function() use ($callback) {
                // check @ in callback
                if (strpos($callback, "@") !== false) {
                    $separated = explode("@", $callback);
                    $requestedController = $separated[0];
                    $requestedMethod = $separated[1];
                    require_once "signite-framework/controllers/$requestedController.php";
                    eval('$controller = new $requestedController($this->_signiteApp);');
                    eval('$controller->$requestedMethod();');   
                }
                else {
                    $requestedController = $callback;
                    require_once "signite-framework/controllers/$requestedController.php";
                    eval('$controller = new $requestedController($this->_signiteApp);');
                    $controller->__invoke();
                }
            }, $pathId, $paths, []);
            return $this->_routes[$pathId];
        }
        else {
            $paths = $this->getRequestedPaths($path);
            $pathId = $this->generatePathId($paths);
            $this->_routes[$pathId] = new SigniteRoute($path, is_array($method) ? $method : explode("|", $method), 
            $callback, $pathId, $paths, []);
            return $this->_routes[$pathId];
        }
    }

    private function generatePathId(Array $paths): String {
        $nonParamPaths = "/";
        for ($i = 0; $i < count($paths); $i++) {
            if (!$paths[$i]["isParam"]) {
                if ($i == count($paths) - 1) {
                    $nonParamPaths .= $paths[$i]["path"];
                }
                else {
                    $nonParamPaths .= $paths[$i]["path"] . "/";
                }
            }
        }
        return md5($nonParamPaths);
    }

    private function getRequestedPaths($path): array {
        $_paths = explode("/", $path);
        $paths = array();
        foreach ($_paths as $key => $value) {
            if ($value == "") {
                unset($_paths[$key]);
            }
            else{
                $matches = [];
                $match = preg_match($this->_paramRegex, $value, $matches);
                if ($match === 1){
                    $paths[] = [
                        "path" => $value,
                        "isParam" => true,
                        "paramName" => $matches[1]
                    ];
                }
                else{
                    $paths[] = [
                        "path" => $value,
                        "isParam" => false,
                        "paramName" => null
                    ];
                }
            }
        }
        return $paths;
    }

    private function checkRouteExists($path): bool {
        $routeExist = array_key_exists(md5($path), $this->_routes);
        if ($routeExist) {
            $route = $this->_routes[md5($path)];
            $methods = $route->getMethod();
            return in_array($_SERVER["REQUEST_METHOD"], $methods);
        }
        else {
            return false;
        }
    }

    private function checkParamableRouteExist($path): array|bool {
        foreach ($this->_routes as $route) {
            $routePaths = $route->getPaths();
            $routePathsCount = count($routePaths);
            $requestedRoutePaths = $this->getRequestedPaths($path);
            $pathsCount = count($requestedRoutePaths);
            $routePathsParams = array();
            if ($routePathsCount == $pathsCount) {
                $routePathsMatch = true;
                for ($i = 0; $i < $pathsCount; $i++) {
                    if ($routePaths[$i]["path"] != $requestedRoutePaths[$i]["path"]
                    && !$routePaths[$i]["isParam"]) {
                        $routePathsMatch = false;
                        break;
                    }
                    if ($routePaths[$i]["isParam"]) {
                        $routePathsParams[$routePaths[$i]["paramName"]] = $requestedRoutePaths[$i]["path"];
                    }
                }
                if ($routePathsMatch) {
                    $methods = $route->getMethod();
                    return in_array($_SERVER["REQUEST_METHOD"], $methods) ? [
                        "routePathsParams" => $routePathsParams,
                        "pathId" => $route->getId()
                    ] : false;
                }
            }
        }
        return false;
    }

    private function parsePath(): string {
        // extract path without query string
        $this->_requested_url = $_SERVER["REQUEST_URI"];
        $this->_requested_path = parse_url($this->_requested_url, PHP_URL_PATH);
        return $this->_requested_path;
    }

    private function getFirstPath(): string {
        $path = $this->parsePath();
        $path = explode("/", $path);
        return $path[1];
    }

    public function getPathId($path): string {
        return md5($path);
    }

    private function isPathAccessAllowed($path): bool {
        // replace first and last slash
        if ($path[0] == "/") {
            $path = substr($path, 1);
        }
        if (strlen($path) > 1 && $path[strlen($path) - 1] == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        if (in_array($path, $this->_allowedPathesAccess)){
            return true;
        }
        else{
            foreach ($this->_allowedPathesAccess as $allowedPath) {
                if (strpos($allowedPath, "*") !== false) {
                    $pathSegments = explode("/", $allowedPath);
                    $index = 0;
                    for ($i = 0; $i < count($pathSegments); $i++) {
                        if ($pathSegments[$i] == "*") {
                            break;
                        }
                        else{
                            $index++;
                        }
                    }
                    $parentPath = implode("/", array_slice($pathSegments, 0, $index));
                    return strpos($path, $parentPath) !== false;
                }
            }
            return false;
        }
    }

    private function isPathAccessDenied($path): bool {
        // replace first and last slash
        if ($path[0] == "/") {
            $path = substr($path, 1);
        }
        if (strlen($path) > 1 && $path[strlen($path) - 1] == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        if (in_array($path, $this->_deniedPathesAccess)) {
            return true;
        }
        else{
            foreach ($this->_deniedPathesAccess as $allowedPath) {
                if (strpos($allowedPath, "*") !== false) {
                    $pathSegments = explode("/", $allowedPath);
                    $index = 0;
                    for ($i = 0; $i < count($pathSegments); $i++) {
                        if ($pathSegments[$i] == "*") {
                            break;
                        }
                        else{
                            $index++;
                        }
                    }
                    $parentPath = implode("/", array_slice($pathSegments, 0, $index));
                    return strpos($path, $parentPath) !== false;
                }
            }
            return false;
        }
    }

    public function isRouteHaveMiddleware($pathId): bool {
        return array_key_exists($pathId, $this->_routes) && count($this->_routes[$pathId]->getMiddlewares()) > 0;
    }

    public function run(){
        $this->parsePath();
        $paramableRouteExistResult = $this->checkParamableRouteExist($this->_requested_path);
        if ($this->checkRouteExists($this->_requested_path)) {
            if ($this->isRouteHaveMiddleware(md5($this->_requested_path))) {
                $result = $this->_routes[md5($this->_requested_path)]->runMiddlewares();
                if ($result === true){
                    $result = $this->_routes[md5($this->_requested_path)]->callback();
                    echo $result;
                }
                else if ($result instanceof MiddlewareResult){
                    echo $result->getOnMiddlewareFailed()();
                }
                else {
                    $errorView = $this->getSpecialPages("404");
                    $this->_statusError->throw($errorView);
                }
            }
            else {
                $result = $this->_routes[md5($this->_requested_path)]->callback();
                echo $result;
            }
        } else if ($paramableRouteExistResult !== false){
            if ($this->isRouteHaveMiddleware($paramableRouteExistResult["pathId"])) {
                $result = $this->_routes[$paramableRouteExistResult["pathId"]]->runMiddlewares();
                if ($result === true){
                    $result = $this->_routes[$paramableRouteExistResult["pathId"]]->callback($paramableRouteExistResult["routePathsParams"]);
                    echo $result;
                }
                else if ($result instanceof MiddlewareResult){
                    echo $result->getOnMiddlewareFailed()();
                }
                else {
                    $errorView = $this->getSpecialPages("404");
                    $this->_statusError->throw($errorView);
                }
            }
            else {
                $result = $this->_routes[$paramableRouteExistResult["pathId"]]->callback($paramableRouteExistResult["routePathsParams"]);
                echo $result;
            }
        } else {
            if ($this->isPathAccessAllowed($this->_requested_path)
            && !$this->isPathAccessDenied($this->_requested_path)) {
                if (is_dir(substr($this->_requested_path, 1))){
                    if (strpos($this->_requested_url, "?q=") !== false) {
                        $this->_requested_url = substr($this->_requested_url, 0, strpos($this->_requested_url, "?q="));
                        header("Location: " . $this->_requested_url);
                    }
                    else{
                        echo $this->_signiteRender->render("signite-framework/pages/explorer.php", [
                            "filePath" => substr($this->_requested_path, 1),
                            "directoryToSearch" => substr($this->_requested_path, 1)
                        ]);
                    }
                } else {
                    readfile(substr($this->_requested_path, 1));
                }
            }
            else{
                $errorView = $this->getSpecialPages("404");
                $this->_statusError->throw($errorView);
            }
        }
    }
}

class SigniteRoute {
    private $_path;
    private $_method;
    private $_callback;
    private $_id;
    private $_paths;
    private $_middlewares;

    public function __construct($path, $method, $callback, $id, $paths, $middlewares) {
        $this->_path = $path;
        $this->_method = $method;
        $this->_callback = $callback;
        $this->_id = $id;
        $this->_paths = $paths;
        $this->_middlewares = $middlewares;
    }

    public function getPath(): string {
        return $this->_path;
    }

    public function getMethod(): array {
        return $this->_method;
    }

    public function setMethod(array $method) {
        $this->_method = $method;
    }

    public function setPath(string $path) {
        $this->_path = $path;
    }

    public function callback($params = null){
        return $this->getCallback()($params);
    }

    public function getCallback(): callable {
        return $this->_callback;
    }

    public function getId(): string {
        return $this->_id;
    }

    public function getPaths(): array {
        return $this->_paths;
    }

    public function getMiddlewares(): array {
        return $this->_middlewares;
    }

    public function addMiddleware($middleware){
        array_push($this->_middlewares, $middleware);
    }

    public function runMiddlewares(): MiddlewareResult|bool|Exception {
        $middlewareTool = new SigniteMiddlewareTool();
        $middlewareRunResult = true;
        $middlewareResult = null;
        foreach ($this->_middlewares as $middleware) {
            if ($middlewareTool->isMiddlewareExist($middleware["middleware"])) {
                $loadedMiddleware = $middlewareTool->loadMiddleware($middleware["middleware"]);
                $result = json_decode($loadedMiddleware->handle()->getResult(), true);
                if (!$result["success"]){
                    $middlewareRunResult = false;
                    if ($middleware["onMiddlewareFailed"] !== null){
                        $middlewareResult = new MiddlewareResult(
                            $result["middleware"],
                            $result["success"],
                            $result["message"],
                            $result["data"],
                            $middleware["onMiddlewareFailed"]
                        );
                    }
                    break;
                }
            }
            else{
                throw new Exception("Middleware '" . $middleware . "' not found.");
            }
        }
        return $middlewareResult !== null ? $middlewareResult : $middlewareRunResult == true;
    }

    public function middleware($middleware, $onMiddlewareFailed = null): SigniteRoute|Exception {
        $this->addMiddleware([
            "middleware" => $middleware,
            "onMiddlewareFailed" => $onMiddlewareFailed
        ]);
        return $this;
    }
}

class SigniteRender {

    private $_data = array();

    public function render($path, $data = array(), $useApplicationPath = false) {
        $this->_data = $data;
        $file = $this->getFile($useApplicationPath ? $this->getApplicationViewsPath() . "/$path" : $path);
        if ($file !== false) {
            $this->renderFile($file);
        } else {
            throw new Exception("View '" . $path . "' not found.");
        }
    }

    private function getApplicationViewsPath() {
        return $this->getApplicationPath() . "/views";
    }

    private function getApplicationPath() {
        if (substr($GLOBALS["application"], -1) !== "/") {
            return $GLOBALS["application"];
        }
        else {
            return substr($GLOBALS["application"], 0, -1);
        }
    }

    private function getFile($path): string|bool {
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    private function renderFile($file) {
        foreach ($this->_data as $key => $value) {
            $file = str_replace("{{{$key}}}", $value, $file);
        }
        eval("?>".$file);
    }
}

function view($path, $data = array(), $useApplicationPath = false) {
    $signiteRender = new SigniteRender();
    return $signiteRender->render($path, $data, $useApplicationPath);
}