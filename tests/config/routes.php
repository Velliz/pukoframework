<?php $routes = [
    "page" => [
        "" => [
            "controller" => "main",
            "function" => "main",
            "accept" => [
                "GET",
                "POST"
            ]
        ],
        "another" => [
            "controller" => "mainupdate",
            "function" => "testya",
            "accept" => [
                "POST"
            ]
        ]
    ],
    "error" => [
        "controller" => "error",
        "function" => "display",
        "accept" => [
            "GET",
            "POST"
        ]
    ],
    "not_found" => [
        "controller" => "error",
        "function" => "notfound",
        "accept" => [
            "GET",
            "POST"
        ]
    ]
]; return $routes;