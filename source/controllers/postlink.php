<?php
$parts = parse_url($_GET['href']);
parse_str($parts['query'], $vars);
$pm->setVariable('path', $parts['path']);
$pm->setVariable('query', $vars);
