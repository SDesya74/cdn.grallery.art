<?php

class Request {
    private static ?ArrayObject $parsed_args = null;

    static function json(): ArrayObject {
        $input = file_get_contents("php://input");
        return new ArrayObject(
            $input ? json_decode($input) : [],
            ArrayObject::ARRAY_AS_PROPS
        );
    }

    static function args(): ArrayObject {
        if (self::$parsed_args !== null) return self::$parsed_args;

        $args = null;
        parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY), $args);
        self::$parsed_args = new ArrayObject($args, ArrayObject::ARRAY_AS_PROPS);

        return self::$parsed_args;
    }

    static function header($name) {
        $name = str_replace("-", "_", strtoupper($name));
        return empty($_SERVER["HTTP_$name"]) ? null : $_SERVER["HTTP_$name"];
    }
}

