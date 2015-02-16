<?php
// Router file dedicated to PHP's internal web server.

if (preg_match('/\.(?:js|ico|gif|jpg|png|css|ttf|woff|woff2|eot|svg)$/', $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    include __DIR__ . '/app_router.php';
}
