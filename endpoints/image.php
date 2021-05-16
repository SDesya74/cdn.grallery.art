<?php

/* @var $collector RouteCollector */

use Phroute\RouteCollector;

$collector->get(
    "/{rawpath:[a-z0-9]{20}}",
    function ($rawpath) {
        [ $left, $right ] = str_split($rawpath, 10);

        $dir_name = "uploads/" . implode("/", str_split($left, 2));
        $file_name = $right;

        $full_path = implode("/", [ $dir_name, $file_name ]);

        // TODO: Resize image by args

        $info = new finfo(FILEINFO_MIME); // возвращает mime-тип а-ля mimetype расширения
        $mime = $info->file($full_path);

        header("Content-Type: $mime");
        header("Content-Length: " . filesize($full_path));

        fpassthru(fopen($full_path, "rb"));
        exit;
    }
);