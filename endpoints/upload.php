<?php

/* @var $collector RouteCollector */

use Phroute\RouteCollector;

$collector->post(
    "/upload",
    function () {
        try {
            $args = Request::args();
            if (!isset($args->flag)) return error("Where flag?");
            if (!isset($args->hash)) return error("Where hash?");
            [ "flag" => $flag, "hash" => $hash ] = $args;

            $flag_path = "flags/" . $flag;
            if (!file_exists($flag_path)) return error("Invalid flag");

            if (empty($_FILES)) return error("Where file?");
            if (count($_FILES) > 1) return error("Only one file allowed");

            $file = array_values($_FILES)[0];

            $tmp_path = $file["tmp_name"];
            $file_hash = hash_file("sha256", $tmp_path);
            if (md5($flag . $file_hash) != $hash) return error("Invalid hash or file");

            $type = exif_imagetype($tmp_path);
            if($type === false) return error("File is not image: " . $tmp_path);

            if (filesize($tmp_path) > 20 * 1024 * 1024) {
                // File size is more than 20 MB
                return error("File size is more than 20 MB");
            }
            // TODO: Figure out how to check filesize without hardcode

            do {
                $left = substr(md5(uniqid()), 0, 10);
                $right = substr(md5(uniqid(uniqid(), true)), 0, 10);

                $dir_name = "uploads/" . implode('/', str_split($left, 2));
                $file_name = $right;

                $full_path = implode("/", [ $dir_name, $file_name ]);
            } while (file_exists($full_path));

            mkdir($dir_name, 0777, true);
            move_uploaded_file($tmp_path, $full_path);
            unlink($flag_path);

            $size = filesize($full_path);
            $server = (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER["SERVER_NAME"];
            [ $width, $height ] = getimagesize($full_path);
            $image_size = $width . "x" . $height;
            $image_type = image_type_to_mime_type($type);

            $result = [ base64_encode($server), $left, $right, $image_size, $size, $image_type ];

            return ok([ "image" => implode("|", $result) ]);
        } catch (Exception $ignore) {
            return error($ignore);
        }
    }
);