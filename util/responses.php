<?php

// 200 - ok
// 201 - created
// 204 - no content

// 400 - bad request

function ok($payload = null, $meta = []): array {
    return response($payload === null && $meta == [] ? 204 : 200, $payload, $meta);
}

function created($message = "Created", $meta = []): array {
    return response(201, [ "message" => $message ], $meta);
}

function error($message): array {
    return response(400, [ "message" => $message ]);
}

function response($code, $data, $meta = []): array {
    $ok = $code >= 200 && $code < 300;

    $meta = [ $meta ];
    while (!empty($meta) && array_filter($meta,
            function ($e) {
                return is_array($e) && empty($e["type"]);
            }) === $meta) {
        $meta = array_merge(...$meta);
    }

    $meta_response = [];
    foreach ($meta as $item) {
        if ($item["type"] == "link") {
            if (!isset($meta_response["links"])) $meta_response["links"] = [];
            $meta_response["links"][$item["name"]] = $item["link"];
        }
    }

    $response = [ "ok" => $ok, "payload" => $data ];
    if (!empty($meta_response)) $response["meta"] = $meta_response;
    return [ $code, json_encode($response) ];
}
