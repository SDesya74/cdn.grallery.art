<?php

/* @var $collector RouteCollector */

use Phroute\RouteCollector;

$collector->get(
    "/available",
    function () {
        $args = Request::args();
        if (!isset($args->hash)) return error("Where hash?");
        $flag = md5($args->hash . time());
        $hash = md5($flag . $args->hash);

        if (strlen($flag) !== 32) return error("Wrong hash (not MD5)");

        $flag_path = "flags/" . $flag;
        if (file_exists($flag_path)) return ok([ "available" => false ]);
        touch($flag_path);

        $upload_url = $_SERVER["SERVER_NAME"] . "/upload";
        $upload_url = (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $upload_url;
        $upload_url .= "?" . http_build_query([ "flag" => $flag, "hash" => $hash ]);

        return ok([ "available" => true, "upload_url" => $upload_url ]);
    }
);