<?php
$db = Typeframe::Database();
$pm = Typeframe::Pagemill();

//header("HTTP/1.0 403 Forbidden");
http_response_code(403);
$pm->setVariable('login_redirect', $_SERVER['REQUEST_URI']);
Typeframe::SetPageTemplate('/403.html');
