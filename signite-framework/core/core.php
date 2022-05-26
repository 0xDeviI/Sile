<?php

namespace Signite\Core;

require_once "signite-framework/modules/MiddlewareTool.php";
require_once "signite-framework/config/Config.php";
require_once "signite-framework/Modules/MiddlewareResult.php";
require_once "signite-framework/core/HelperFunctions.php";
require "signite-framework/utils/whoops/vendor/autoload.php";


use Signite\Modules\SigniteMiddlewareTool;
use Signite\Modules\MiddlewareResult;
use function Signite\Core\view;
use function Signite\Core\response;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Exception;
use Closure;

mb_internal_encoding("UTF-8");

class Signite {
    private $_config = array();
    private $_applicationName;
    private $_applicationConfig;
    private SigniteRouter $_router;

    public function __construct($applicationName) {
        $this->_applicationName = $applicationName;
        $this->checkApplicationExists();

        $this->readConfig();
        $this->initializeApplication();
    }

    public function initializeApplication() {
        // initialize router
        $this->_router = new SigniteRouter($this);

        // initialize database
        require_once "signite-framework/database/connection.php";
        $db = $GLOBALS["connection"]->connect();
        return true;
    }

    public function route($uri, Closure|string $method, string|array $callback) {
        return $this->_router->route($uri, $method, $callback);
    }

    public function run() {
        $this->_router->run();
    }

    public function redirect($url) {
        header("Location: " . $url);
        exit();
    }

    public function setApplicationDirectoryVisibility($isVisibility) {
        if ($isVisibility)
            $this->_router->setAllowedPathAccess($this->getFreshApplicationName() . "/*");
        else
            $this->_router->setDeniedPathAccess($this->getFreshApplicationName() . "/*");
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

    public static function throw($errorView){
        echo view($errorView["view"], $errorView["params"]);
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
    private SigniteRequest $_request;
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
    private $_controllerResourceMethods = [
        "index",
        "create",
        "store",
        "show",
        "edit",
        "update",
        "destroy"
    ];

    public function __construct(Signite $signiteApp)
    {
        $this->_signiteApp = $signiteApp;
        $this->readConfig();
        $this->configurePathesAccess();
        $this->setWhoopsErrorHandler();
        $this->makeRoutesGlobal();
        session_start();
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
            $newRoute = new SigniteRoute($path, $methods, $route->getCallback(), $pathId, $paths, $route->getMiddlewares(), false, false); // need review
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
        $callback, $pathId, $paths, [], false, false);
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
            $callback, $pathId, $paths, [], true, !strpos($callback, "@"));
            return $this->_routes[$pathId];
        }
        else {
            $paths = $this->getRequestedPaths($path);
            $pathId = $this->generatePathId($paths);
            $this->_routes[$pathId] = new SigniteRoute($path, is_array($method) ? $method : explode("|", $method), 
            $callback, $pathId, $paths, [], false, false);
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

    private function parseRequest() {
        $this->_request = new SigniteRequest();
        $this->parsePath();
    }

    private function parsePath(): string {
        // extract path without query string
        $this->_requested_url = $_SERVER["REQUEST_URI"];
        $this->_requested_path = parse_url($this->_requested_url, PHP_URL_PATH);
        return $this->_requested_path;
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
        $this->parseRequest();
        $paramableRouteExistResult = $this->checkParamableRouteExist($this->_requested_path);
        if ($this->checkRouteExists($this->_requested_path)) {
            if ($this->isRouteHaveMiddleware(md5($this->_requested_path))) {
                $result = $this->_routes[md5($this->_requested_path)]->runMiddlewares();
                if ($result === true){
                    if ($this->_routes[md5($this->_requested_path)]->isControllerBased()) {
                        if ($this->_routes[md5($this->_requested_path)]->isInvokableControllerBased()) {
                            $requestedController = $this->_routes[md5($this->_requested_path)]->getCallback();
                            require_once "signite-framework/controllers/$requestedController.php";
                            eval('$controller = new $requestedController($this->_signiteApp);');
                            echo $controller->__invoke();
                        }
                        else {
                            $separated = explode("@", $this->_routes[md5($this->_requested_path)]->getCallback());
                            $requestedController = $separated[0];
                            $requestedMethod = $separated[1];
                            require_once "signite-framework/controllers/$requestedController.php";
                            eval('$controller = new $requestedController($this->_signiteApp);');
                            if (in_array($requestedMethod, $this->_controllerResourceMethods)) {
                                if ($requestedMethod == "store") {
                                    echo $controller->store($this->_request);
                                }
                            }
                            else {
                                if (method_exists($controller, $requestedMethod)) {
                                    echo $controller->$requestedMethod($this->_request);
                                }
                                else {
                                    throw new \Exception("Method $requestedMethod not found in $requestedController");
                                }
                            }
                            
                        }
                    }
                    else {
                        $result = $this->_routes[md5($this->_requested_path)]->callback();
                        echo $result;
                    }
                }
                else if ($result instanceof MiddlewareResult){
                    echo $result->getOnMiddlewareFailed()();
                }
                else {
                    $errorView = $this->getSpecialPages("404");
                    StatusError::throw($errorView);
                }
            }
            else {
                if ($this->_routes[md5($this->_requested_path)]->isControllerBased()) {
                    if ($this->_routes[md5($this->_requested_path)]->isInvokableControllerBased()) {
                        $requestedController = $this->_routes[md5($this->_requested_path)]->getCallback();
                        require_once "signite-framework/controllers/$requestedController.php";
                        eval('$controller = new $requestedController($this->_signiteApp);');
                        echo $controller->__invoke();
                    }
                    else {
                        $separated = explode("@", $this->_routes[md5($this->_requested_path)]->getCallback());
                        $requestedController = $separated[0];
                        $requestedMethod = $separated[1];
                        require_once "signite-framework/controllers/$requestedController.php";
                        eval('$controller = new $requestedController($this->_signiteApp);');
                        if (in_array($requestedMethod, $this->_controllerResourceMethods)) {
                            if ($requestedMethod == "store") {
                                echo $controller->store($this->_request);
                            }
                        }
                        else {
                            if (method_exists($controller, $requestedMethod)) {
                                echo $controller->$requestedMethod($this->_request);
                            }
                            else {
                                throw new \Exception("Method $requestedMethod not found in $requestedController");
                            }
                        }
                    }
                }
                else {
                    $result = $this->_routes[md5($this->_requested_path)]->callback();
                    echo $result;
                }
            }
        } else if ($paramableRouteExistResult !== false){
            if ($this->isRouteHaveMiddleware($paramableRouteExistResult["pathId"])) {
                $result = $this->_routes[$paramableRouteExistResult["pathId"]]->runMiddlewares();
                if ($result === true){
                    if ($this->_routes[$paramableRouteExistResult["pathId"]]->isControllerBased()) {
                        if ($this->_routes[$paramableRouteExistResult["pathId"]]->isInvokableControllerBased()) {
                            $requestedController = $this->_routes[$paramableRouteExistResult["pathId"]]->getCallback();
                            require_once "signite-framework/controllers/$requestedController.php";
                            eval('$controller = new $requestedController($this->_signiteApp);');
                            echo $controller->__invoke();
                        }
                        else {
                            $separated = explode("@", $this->_routes[$paramableRouteExistResult["pathId"]]->getCallback());
                            $requestedController = $separated[0];
                            $requestedMethod = $separated[1];
                            require_once "signite-framework/controllers/$requestedController.php";
                            eval('$controller = new $requestedController($this->_signiteApp);');
                            if (in_array($requestedMethod, $this->_controllerResourceMethods)) {
                                if ($requestedMethod == "store") {
                                    echo $controller->store($this->_request);
                                }
                            }
                            else {
                                if (method_exists($controller, $requestedMethod)) {
                                    echo $controller->$requestedMethod($this->_request);
                                }
                                else {
                                    throw new \Exception("Method $requestedMethod not found in $requestedController");
                                }
                            }
                        }
                    }
                    else {
                        $result = $this->_routes[$paramableRouteExistResult["pathId"]]->callback($paramableRouteExistResult["routePathsParams"]);
                        echo $result;
                    }
                }
                else if ($result instanceof MiddlewareResult){
                    echo $result->getOnMiddlewareFailed()();
                }
                else {
                    $errorView = $this->getSpecialPages("404");
                    StatusError::throw($errorView);
                }
            }
            else {
                if ($this->_routes[$paramableRouteExistResult["pathId"]]->isControllerBased()) {
                    if ($this->_routes[$paramableRouteExistResult["pathId"]]->isInvokableControllerBased()) {
                        $requestedController = $this->_routes[$paramableRouteExistResult["pathId"]]->getCallback();
                        require_once "signite-framework/controllers/$requestedController.php";
                        eval('$controller = new $requestedController($this->_signiteApp);');
                        echo $controller->__invoke();
                    }
                    else {
                        $separated = explode("@", $this->_routes[$paramableRouteExistResult["pathId"]]->getCallback());
                        $requestedController = $separated[0];
                        $requestedMethod = $separated[1];
                        require_once "signite-framework/controllers/$requestedController.php";
                        eval('$controller = new $requestedController($this->_signiteApp);');
                        if (in_array($requestedMethod, $this->_controllerResourceMethods)) {
                            if ($requestedMethod == "store") {
                                echo $controller->store($this->_request);
                            }
                        }
                        else {
                            if (method_exists($controller, $requestedMethod)) {
                                echo $controller->$requestedMethod($this->_request);
                            }
                            else {
                                throw new \Exception("Method $requestedMethod not found in $requestedController");
                            }
                        }
                    }
                }
                else {
                    $result = $this->_routes[$paramableRouteExistResult["pathId"]]->callback($paramableRouteExistResult["routePathsParams"]);
                    echo $result;
                }
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
                        echo view("signite-framework/pages/explorer.php", [
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
                StatusError::throw($errorView);
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
    private bool $_isControllerBased;
    private bool $_isInvokableControllerBased;

    public function __construct($path, $method, $callback, $id, $paths, $middlewares, $isControllerBased, $isInvokableControllerBased) {
        $this->_path = $path;
        $this->_method = $method;
        $this->_callback = $callback;
        $this->_id = $id;
        $this->_paths = $paths;
        $this->_middlewares = $middlewares;
        $this->_isControllerBased = $isControllerBased;
        $this->_isInvokableControllerBased = $isInvokableControllerBased;
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

    public function getCallback(): callable|string {
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

    public function isControllerBased(): bool {
        return $this->_isControllerBased;
    }

    public function isInvokableControllerBased(): bool {
        return $this->_isInvokableControllerBased;
    }

    public function runMiddlewares(): MiddlewareResult|bool|Exception {
        $middlewareTool = new SigniteMiddlewareTool();
        $middlewareRunResult = true;
        $middlewareResult = null;
        foreach ($this->_middlewares as $middleware) {
            if ($middlewareTool->isMiddlewareExist($middleware["middleware"])) {
                $loadedMiddleware = $middlewareTool->loadMiddleware($middleware["middleware"]);
                $handledResult = $loadedMiddleware->handle();
                $result = json_decode($handledResult->getResult(), true);
                if (!$result["success"]){
                    $middlewareRunResult = false;
                    if ($middleware["onMiddlewareFailed"] !== null){
                        $middlewareResult = new MiddlewareResult(
                            $result["middleware"],
                            $result["success"],
                            $result["message"],
                            $result["data"],
                            $handledResult->getOnMiddlewareFailed()
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

class SigniteRequest {
    private $_requested_url;
    private $_requested_path;
    private $_requested_method;
    private $_requested_params;

    public function __construct() {
        $this->_requested_url = $_SERVER["REQUEST_URI"];
        $this->_requested_path = substr($this->_requested_url, 1);
        $this->_requested_method = $_SERVER["REQUEST_METHOD"];
        $this->_requested_params = $_REQUEST;
    }

    public function __toString()
    {
        return json_encode([
            "requested_url" => $this->_requested_url,
            "requested_path" => $this->_requested_path,
            "requested_method" => $this->_requested_method,
            "requested_params" => $this->_requested_params
        ], JSON_UNESCAPED_UNICODE);
    }

    public function getRequestedUrl(): string {
        return $this->_requested_url;
    }

    public function getRequestedPath(): string {
        return $this->_requested_path;
    }

    public function getRequestedMethod(): string {
        return $this->_requested_method;
    }

    public function getRequestedParams(): array {
        return $this->_requested_params;
    }

    public function get($key) {
        if (isset($this->_requested_params[$key])) {
            return $this->_requested_params[$key];
        }
        return null;
    }
}

class SigniteResponse {
    private $_status;
    private $_data;
    private $_headers;

    public function __construct($status = null, $data = null, $headers = array()) {
        $this->_status = $status;
        $this->setStatus($status);
        $this->_data = $data;
        $this->_headers = $headers;
    }

    public function __toString()
    {
        // encode json in json
        return json_encode($this->_data
        , JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function json() {
        header("Content-Type: application/json");
        return $this;
    }

    public function getStatus(): int {
        return $this->_status;
    }

    public function getData(): array {
        return $this->_data;
    }

    public function setStatus($status) {
        $this->_status = $status;
        http_response_code($status);
        return $this;
    }

    public function setData($data) {
        $this->_data = $data;
        return $this;
    }

    public function getHeaders(): array {
        return $this->_headers;
    }

    public function setHeader($header, $value) {
        $this->_headers[$header] = $value;
        header($header . ": " . $value);
        return $this;
    }

    public function setHeaders($headers) {
        $this->_headers = $headers;
        for ($i = 0; $i < count($headers); $i++) {
            $this->setHeader($headers[$i]["header"], $headers[$i]["value"]);
        }
        return $this;
    }

    public function redirect($url) {
        header("Location: $url");
    }

    public function redirectTo($url) {
        $this->redirect($url);
    }

    public function redirectToRoute($route, $params = array()) {
        $this->redirect($this->getRouteUrl($route, $params));
    }

    public function getRouteUrl($route, $params = array()) {
        $route = $GLOBALS["routes"][$route];
        $url = $route["path"];
        foreach ($params as $key => $value) {
            $url = str_replace("{{{$key}}}", $value, $url);
        }
        return $url;
    }

    public function getRoutePath($route, $params = array()) {
        return $this->getRouteUrl($route, $params);
    }

    public function getRouteMethod($route, $params = array()) {
        return $GLOBALS["routes"][$route]["method"];
    }

}