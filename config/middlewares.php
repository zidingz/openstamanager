<?php

// Middleware per i contenuti di base
$app->add(new \Middlewares\ContentMiddleware($container));

// Middleware per l'input
$app->add($container['filter']);

$app->add(new \Middlewares\ConfigMiddleware($container));
