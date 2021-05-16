<?php

error_reporting(E_ALL);

require_once "vendor/autoload.php";
require_once "util/responses.php";

// region CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    header("Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Max-Age: 1728000");
    header("Content-Length: 0");
    header("Content-Type: application/json");
    die();
}
// endregion

require_once "util/Request.php";

// region Get all routes
$collector = new Phroute\RouteCollector();

$it = new RecursiveDirectoryIterator("endpoints/");
foreach (new RecursiveIteratorIterator($it) as $endpoint) {
    if ($endpoint->getExtension() == "php") {
        include_once $endpoint;
    }
}
// endregion

// region Working with request
function send_response($response) {
    $code = $response[0];
    $result = $response[1];

    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo $result;
}

$dispatcher = new Phroute\Dispatcher($collector);
try {
    $response = $dispatcher->dispatch(
        $_SERVER["REQUEST_METHOD"],
        parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)
    );
    send_response($response);
} catch (Phroute\Exception\HttpMethodNotAllowedException $exception) {
    send_response(
        response(405, [ "message" => "Method not allowed" ])
    );
} catch (Phroute\Exception\HttpRouteNotFoundException $exception) {
    send_response(
        response(404, [ "message" => "Invalid endpoint" ])
    );
} catch (Phroute\Exception\BadRouteException $exception) {
    send_response(
        response(500, [ "message" => "Bad route" ])
    );
}
// endregion
