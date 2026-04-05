<?php
/**
 * Standalone poll endpoint for Console background execution.
 *
 * This file is called directly via HTTP — it does NOT go through ProcessWire's
 * bootstrap.  This is intentional: the code-execution request holds a PHP
 * session lock for the duration of the run, and any request that goes through
 * PW would block on session_start() until the lock is released.
 *
 * Security: each run stores a random pollToken in its cache file.  The client
 * must send the matching token with every poll request.
 */

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('status' => 'error', 'output' => 'Method not allowed'));
    exit;
}

$runId = isset($_POST['runId']) ? preg_replace('/[^a-zA-Z0-9_.]/', '', $_POST['runId']) : '';
$pollToken = isset($_POST['pollToken']) ? preg_replace('/[^a-zA-Z0-9_.]/', '', $_POST['pollToken']) : '';

if(!$runId || !$pollToken) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'output' => 'Missing run ID or poll token'));
    exit;
}

/* Derive cache path from module location:
   This file:  site/modules/TracyDebugger/includes/ConsolePollEndpoint.php
   Cache dir:  site/assets/cache/TracyDebugger/console_runs/               */
$cacheDir = dirname(__DIR__, 3) . '/assets/cache/TracyDebugger/console_runs/';
$runFile  = $cacheDir . $runId . '.json';
$lockFile = $cacheDir . $runId . '.lock';

clearstatcache(true, $runFile);

if(!file_exists($runFile)) {
    echo json_encode(array('status' => 'running'));
    exit;
}

$raw = file_get_contents($runFile);
if($raw === false) {
    echo json_encode(array('status' => 'running'));
    exit;
}

$data = json_decode($raw, true);
if(!is_array($data)) {
    echo json_encode(array('status' => 'running'));
    exit;
}

/* Validate poll token */
if(!isset($data['pollToken']) || !hash_equals($data['pollToken'], $pollToken)) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'output' => 'Invalid poll token'));
    exit;
}

if(isset($data['status']) && $data['status'] === 'running') {
    /* Check lock file to detect whether the execution process is still alive */
    $processDead = false;
    if(file_exists($lockFile)) {
        $fh = fopen($lockFile, 'r');
        if($fh) {
            if(flock($fh, LOCK_EX | LOCK_NB)) {
                flock($fh, LOCK_UN);
                $processDead = true;
            }
            fclose($fh);
        }
    }
    else {
        $started = isset($data['started']) ? (int)$data['started'] : 0;
        if($started && (time() - $started) > 10) $processDead = true;
    }

    if($processDead) {
        $elapsed = isset($data['started']) ? (time() - (int)$data['started']) : 0;
        @unlink($runFile);
        @unlink($lockFile);
        echo json_encode(array(
            'status' => 'error',
            'output' => 'Process was terminated by the server after ' . $elapsed . 's. You may need to increase Apache\'s Timeout directive or PHP\'s max_execution_time.',
            'time'   => '',
            'memory' => ''
        ));
    }
    else {
        echo json_encode(array('status' => 'running'));
    }
}
else {
    /* Complete or error — deliver result and clean up */
    @unlink($runFile);
    @unlink($lockFile);
    /* Strip pollToken before sending to client */
    unset($data['pollToken']);
    echo json_encode($data);
}
