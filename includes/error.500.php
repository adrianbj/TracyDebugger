<?php namespace ProcessWire;

/**
 * Tracy's production error page (Debugger::$errorTemplate), with the CSP nonce added to its <style>:
 * the bundled template has none, and a CSP without 'unsafe-inline' blocks it.
 *
 * @var bool $logged
 */

$tracyDir = dirname((new \ReflectionClass(\Tracy\Debugger::class))->getFileName());
$template = is_file($tracyDir . '/dist/error.500.phtml') ? $tracyDir . '/dist/error.500.phtml' : $tracyDir . '/assets/error.500.phtml';
ob_start();
require $template;
echo str_replace('<style>', '<style' . TracyDebugger::getNonceAttr() . '>', ob_get_clean());
