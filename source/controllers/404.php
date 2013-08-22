<?php
$db = Typeframe::Database();
$pm = Typeframe::Pagemill();

http_response_code(404);
Typeframe::SetPageTemplate('/404.html');
