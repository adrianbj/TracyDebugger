<?php namespace ProcessWire;

/**
 * Runs Console panel code in its own PHP CLI process, so it isn't subject to the web server's request
 * time limit. Started in the background by TracyDebugger::startConsoleCliRun() when "Run in CLI" is
 * ticked in the Console panel:
 *
 *   php ConsoleCliRunner.php <runId> <ProcessWire root path>
 *
 * The panel polls for the result, streams output, and cancels the run exactly as it does for a
 * long-running browser run.
 */

if(PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$runId = isset($argv[1]) ? preg_replace('/[^a-zA-Z0-9_.]/', '', $argv[1]) : '';
$root = isset($argv[2]) ? rtrim($argv[2], '/') . '/' : '';
if($runId === '' || strpos($runId, '..') !== false || !is_file($root . 'index.php')) {
    fwrite(STDERR, "Usage: php ConsoleCliRunner.php <runId> <ProcessWire root path>\n");
    exit(1);
}

chdir($root);
include $root . 'index.php';

/** @var ProcessWire $wire */
$tracy = $wire->modules->get('TracyDebugger');
if(!$tracy) {
    fwrite(STDERR, "TracyDebugger is not installed\n");
    exit(1);
}
$tracy->runConsoleCliJob($runId);
