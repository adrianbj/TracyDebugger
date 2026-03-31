<?php namespace ProcessWire;

/**
 * PW Version Switcher
 * Handles ProcessWire version switching, auto-revert safety mechanism,
 * and all related file operations.
 */
class PwVersionSwitcher {

    /**
     * Handle auto-revert cleanup early in init().
     * If an auto-revert occurred, clean up infrastructure and clear stale session vars.
     *
     * @param TracyDebugger $module
     */
    public static function handleAutoRevertCleanup($module) {
        self::cleanupPwVersionAutoRevert($module);
    }

    /**
     * Handle POST request for PW version switch in init().
     * If PW version changed, reload to initialize new version with retry loop.
     *
     * @param TracyDebugger $module
     */
    public static function handleVersionSwitchPost($module) {
        // if PW version changed, reload to initialize new version
        // don't add in_array(static::$showPanels) check because that won't be true if enabled ONCE due to multiple redirects waiting for $module->wire('config')->version to update
        if(($module->wire('input')->post->tracyPwVersion && $module->wire('input')->post->tracyPwVersion != $module->wire('config')->version && $module->wire('session')->CSRF->validate()) || $module->wire('session')->tracyPwVersion) {
            $module->wire('session')->tracyPwVersion = $module->wire('session')->tracyPwVersion ?: $module->wire('input')->post->tracyPwVersion;
            $tracyPwVersionRetries = $module->wire('session')->tracyPwVersionRetries ?: 0;
            if($module->wire('session')->tracyPwVersion != $module->wire('config')->version && $tracyPwVersionRetries < 5) {
                $module->wire('session')->tracyPwVersionRetries = $tracyPwVersionRetries + 1;
                sleep(1);
                $module->wire('session')->redirect($module->httpReferer);
            }
            $module->wire('session')->remove('tracyPwVersion');
            $module->wire('session')->remove('tracyPwVersionRetries');
        }
    }

    /**
     * Clean up auto-revert marker at the very end of init().
     * If init() completes fully, register shutdown function to clean up the marker.
     *
     * @param TracyDebugger $module
     */
    public static function cleanupSwitchMarker($module) {
        self::cleanupPwVersionSwitchMarker($module);
    }

    /**
     * Show auto-revert notification in ready().
     * Deferred to ready() because PW's session system is fully stable here.
     *
     * @param TracyDebugger $module
     */
    public static function showRevertNotification($module) {
        $rootPath = $module->wire('config')->paths->root;
        $revertedFile = $rootPath . '.tracy-pw-reverted';
        if(file_exists($revertedFile)) {
            $revertLog = @json_decode(@file_get_contents($revertedFile), true);
            $revertedFrom = isset($revertLog['revertedFrom']) ? $revertLog['revertedFrom'] : 'unknown';
            $revertedTo = isset($revertLog['revertedTo']) ? $revertLog['revertedTo'] : 'unknown';
            $reason = isset($revertLog['reason']) ? $revertLog['reason'] : '';
            $msg = "TracyDebugger: ProcessWire version switch to v{$revertedFrom} was automatically reverted to v{$revertedTo}." .
                ($reason ? " Reason: {$reason}" : '');
            // Use both notice methods: session warning persists across redirects,
            // module warning ensures display on the current request
            $module->wire('session')->warning($msg);
            $module->warning($msg);
            // Only delete on a non-AJAX request where the admin theme renders notices.
            // AJAX/redirect/error requests keep the file so it retries next page load.
            if(!$module->wire('config')->ajax) {
                @unlink($revertedFile);
            }
        }
    }

    /**
     * Perform the actual file swap in __destruct().
     * Renames wire/, index.php, and .htaccess when using PW Version Switcher.
     *
     * @param TracyDebugger $module
     */
    public static function performFileSwap($module) {
        if($module->wire('input') && $module->wire('input')->post->tracyPwVersion && $module->wire('input')->post->tracyPwVersion != $module->wire('config')->version
            && (TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser)) {

            $targetVersion = $module->wire('input')->post->tracyPwVersion;

            // validate version format (digits and dots only)
            if(!preg_match('/^\d+(\.\d+)*$/', $targetVersion)) return;

            $rootPath = $module->wire('config')->paths->root;
            $currentVersion = $module->wire('config')->version;

            // pre-validate: target wire directory must exist before we move anything
            if(!file_exists($rootPath.'.wire-'.$targetVersion)) return;

            // Write the auto-revert handler file BEFORE renames so it's in place early.
            // Uses @ suppression and try/catch because PW's error handler may convert
            // file_put_contents warnings to exceptions during __destruct() shutdown.
            self::writePwRevertHandler($rootPath);

            // track which files were swapped for auto-revert
            $htaccessSwapped = false;
            $indexSwapped = false;

            // rename wire — move current away, then move target into place
            if(!self::renamePwVersions($rootPath.'wire', $rootPath.'.wire-'.$currentVersion, true)) return;
            if(!self::renamePwVersions($rootPath.'.wire-'.$targetVersion, $rootPath.'wire')) {
                // rollback: restore original wire directory
                self::renamePwVersions($rootPath.'.wire-'.$currentVersion, $rootPath.'wire');
                return;
            }

            // rename .htaccess if a versioned replacement exists
            if(file_exists($rootPath.'.htaccess-'.$targetVersion)) {
                if(!self::renamePwVersions($rootPath.'.htaccess', $rootPath.'.htaccess-'.$currentVersion, true)) return;
                if(!self::renamePwVersions($rootPath.'.htaccess-'.$targetVersion, $rootPath.'.htaccess')) {
                    // rollback htaccess
                    self::renamePwVersions($rootPath.'.htaccess-'.$currentVersion, $rootPath.'.htaccess');
                    return;
                }
                $htaccessSwapped = true;
            }
            // rename index.php if a versioned replacement exists
            if(file_exists($rootPath.'.index-'.$targetVersion.'.php')) {
                if(!self::renamePwVersions($rootPath.'index.php', $rootPath.'.index-'.$currentVersion.'.php', true)) return;
                if(!self::renamePwVersions($rootPath.'.index-'.$targetVersion.'.php', $rootPath.'index.php')) {
                    // rollback index.php
                    self::renamePwVersions($rootPath.'.index-'.$currentVersion.'.php', $rootPath.'index.php');
                    return;
                }
                $indexSwapped = true;
            }

            // Clear OPcache so the next request loads fresh bytecode from the new wire/ files
            if(function_exists('opcache_reset')) @opcache_reset();

            // Inject include line into now-active index.php and write the marker to arm the mechanism
            self::armPwVersionAutoRevert($rootPath, $currentVersion, $targetVersion, $htaccessSwapped, $indexSwapped);
        }
    }


    /**
     * Rename paths for PW version switching with collision-safe suffix.
     *
     * @param string $oldPath Old Path
     * @param string $newPath New Path
     * @param bool $addN Whether to add numeric suffix if target exists
     * @return bool
     */
    public static function renamePwVersions($oldPath, $newPath, $addN = false) {
        if(!file_exists($oldPath)) return false;
        if(file_exists($newPath) && $addN) {
            $n = 0;
            do { $newPath2 = $newPath . "-" . (++$n); } while(file_exists($newPath2) && $n < 100);
            if($n >= 100) return false;
            if(!rename($newPath, $newPath2)) return false;
        }
        return rename($oldPath, $newPath);
    }


    /**
     * Write the standalone auto-revert handler file to the site root.
     * Called BEFORE renames so it's in place early. The handler is inert until the
     * marker file is written (by armPwVersionAutoRevert) after all renames succeed.
     *
     * All file operations use @ suppression and try/catch because PW's error handler
     * may convert warnings to exceptions during __destruct() shutdown.
     *
     * @param string $rootPath Site root path
     */
    private static function writePwRevertHandler($rootPath) {
        try {
            $handlerCode = <<<'HANDLER'
<?php
/**
 * TracyDebugger PW Version Switcher — Auto-Revert Handler
 * This file is automatically created and removed by TracyDebugger.
 * It runs BEFORE ProcessWire loads, at the top of index.php.
 *
 * Revert triggers (two independent mechanisms):
 *
 * 1. IMMEDIATE — via shutdown function: if the new version causes a PHP fatal
 *    error (E_ERROR, E_PARSE, etc.), the shutdown function fires and reverts
 *    before the next request.
 *
 * 2. ATTEMPTED FLAG — if TracyDebugger's init() doesn't clean up the marker
 *    within 1 request, the version is broken (e.g. deprecation warnings flood
 *    headers, sessions break, CSRF fails — site unusable but no fatal error).
 *    On the 2nd request the handler finds the attempted flag and reverts
 *    BEFORE ProcessWire even loads.
 *
 * The attempted flag uses touch() (empty file) instead of rewriting JSON,
 * because file_put_contents() can fail in degraded PHP environments where
 * PW's error handler converts warnings to exceptions.
 */
$tracyRevertMarker = __DIR__ . '/.tracy-pw-revert';
$tracyRevertedLog = __DIR__ . '/.tracy-pw-reverted';
$tracyAttemptedFlag = __DIR__ . '/.tracy-pw-revert-attempted';

// Phase 1: Post-revert request — just clear opcache and let PW boot
// Tracy init() will read the log, show a warning, and clean everything up
if(file_exists($tracyRevertedLog)) {
    if(function_exists('opcache_reset')) @opcache_reset();
}
// Phase 2: Active marker — version switch in progress
elseif(file_exists($tracyRevertMarker)) {
    // Clear OPcache BEFORE ProcessWire loads any wire/ files
    if(function_exists('opcache_reset')) @opcache_reset();

    // Collision-safe rename helper (standalone, no PW dependencies)
    $tracySafeRename = function($oldPath, $newPath) {
        if(!file_exists($oldPath)) return false;
        if(file_exists($newPath)) {
            $n = 0;
            if(!is_dir($newPath) && preg_match('/^(.+)(\.[^.]+)$/', $newPath, $m)) {
                do { $alt = $m[1] . '-' . (++$n) . $m[2]; } while(file_exists($alt) && $n < 100);
            } else {
                do { $alt = $newPath . '-' . (++$n); } while(file_exists($alt) && $n < 100);
            }
            if($n >= 100) return false;
            if(!@rename($newPath, $alt)) return false;
        }
        return @rename($oldPath, $newPath);
    };

    // Revert function (shared by both trigger mechanisms)
    $tracyDoRevert = function($tracyRevertData, $tracyRevertMarker, $tracyAttemptedFlag, $tracySafeRename, $reason) {
        $rootPath = __DIR__ . '/';
        $prev = $tracyRevertData['previousVersion'];
        $target = $tracyRevertData['targetVersion'];

        // revert wire/
        if(is_dir($rootPath . '.wire-' . $prev)) {
            $tracySafeRename($rootPath . 'wire', $rootPath . '.wire-' . $target);
            $tracySafeRename($rootPath . '.wire-' . $prev, $rootPath . 'wire');
        }

        // revert .htaccess if it was swapped
        if(!empty($tracyRevertData['htaccessSwapped']) && file_exists($rootPath . '.htaccess-' . $prev)) {
            $tracySafeRename($rootPath . '.htaccess', $rootPath . '.htaccess-' . $target);
            $tracySafeRename($rootPath . '.htaccess-' . $prev, $rootPath . '.htaccess');
        }

        // revert index.php if it was swapped
        if(!empty($tracyRevertData['indexSwapped']) && file_exists($rootPath . '.index-' . $prev . '.php')) {
            $tracySafeRename($rootPath . 'index.php', $rootPath . '.index-' . $target . '.php');
            $tracySafeRename($rootPath . '.index-' . $prev . '.php', $rootPath . 'index.php');
        }

        if(function_exists('opcache_reset')) @opcache_reset();

        // write log for admin visibility
        @file_put_contents($rootPath . '.tracy-pw-reverted', json_encode(array(
            'revertedFrom' => $target,
            'revertedTo' => $prev,
            'reason' => $reason,
            'timestamp' => time()
        )));

        // remove marker and attempted flag
        @unlink($tracyRevertMarker);
        @unlink($tracyAttemptedFlag);
    };

    $tracyRevertData = @json_decode(@file_get_contents($tracyRevertMarker), true);
    if(is_array($tracyRevertData) && !empty($tracyRevertData['previousVersion']) && !empty($tracyRevertData['targetVersion'])) {
        // safety: ignore stale markers older than 1 hour
        if(isset($tracyRevertData['timestamp']) && (time() - $tracyRevertData['timestamp']) > 3600) {
            @unlink($tracyRevertMarker);
            @unlink($tracyAttemptedFlag);
        }
        // REQUEST 2+: attempted flag exists — TracyDebugger never cleaned up — revert now
        elseif(file_exists($tracyAttemptedFlag)) {
            $tracyDoRevert($tracyRevertData, $tracyRevertMarker, $tracyAttemptedFlag, $tracySafeRename,
                'TracyDebugger failed to boot after version switch (attempted flag present on request 2+)');
        }
        // REQUEST 1: first attempt — give PW a chance to boot
        else {
            // touch() is far more reliable than file_put_contents() in degraded
            // PHP environments — it writes zero bytes and has minimal failure modes
            @touch($tracyAttemptedFlag);

            // FATAL ERROR TRIGGER: catch immediate crashes on this first request
            register_shutdown_function(function() use ($tracyRevertData, $tracyRevertMarker, $tracyAttemptedFlag, $tracySafeRename, $tracyDoRevert) {
                // only fire if marker still exists (init() cleanup deletes it on success)
                if(!file_exists($tracyRevertMarker)) return;
                $error = error_get_last();
                if($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
                    $tracyDoRevert($tracyRevertData, $tracyRevertMarker, $tracyAttemptedFlag, $tracySafeRename,
                        'Fatal error: ' . (isset($error['message']) ? $error['message'] : 'unknown') .
                        (isset($error['file']) ? ' in ' . $error['file'] : '') .
                        (isset($error['line']) ? ' on line ' . $error['line'] : '')
                    );
                }
            });
        }
    }
}
HANDLER;
            @file_put_contents($rootPath . '.tracy-pw-revert-handler.php', $handlerCode);
        } catch(\Exception $e) {} catch(\Error $e) {}
    }


    /**
     * Inject the include line into index.php and write the marker file to arm auto-revert.
     * Called AFTER all renames succeed. The marker is written last so the handler only
     * activates when everything is in place.
     *
     * @param string $rootPath Site root path
     * @param string $previousVersion The version we switched FROM
     * @param string $targetVersion The version we switched TO
     * @param bool $htaccessSwapped Whether .htaccess was swapped
     * @param bool $indexSwapped Whether index.php was swapped
     */
    private static function armPwVersionAutoRevert($rootPath, $previousVersion, $targetVersion, $htaccessSwapped, $indexSwapped) {
        try {
            // 1. Inject include line into the current active index.php (post-swap)
            $indexPath = $rootPath . 'index.php';
            $includeLine = "if(file_exists(__DIR__ . '/.tracy-pw-revert-handler.php')) include __DIR__ . '/.tracy-pw-revert-handler.php';";
            if(file_exists($indexPath)) {
                $indexContent = @file_get_contents($indexPath);
                if($indexContent !== false) {
                    // only inject if not already present
                    if(strpos($indexContent, '.tracy-pw-revert-handler.php') === false) {
                        // insert after the first line (always <?php or <?php namespace ...)
                        $phpTagEnd = strpos($indexContent, "\n");
                        if($phpTagEnd !== false) {
                            $indexContent = substr($indexContent, 0, $phpTagEnd + 1) . $includeLine . "\n" . substr($indexContent, $phpTagEnd + 1);
                            @file_put_contents($indexPath, $indexContent);
                        }
                    }
                }
            }

            // 2. Write the marker file LAST — this arms the mechanism
            $markerData = array(
                'previousVersion' => $previousVersion,
                'targetVersion' => $targetVersion,
                'htaccessSwapped' => $htaccessSwapped,
                'indexSwapped' => $indexSwapped,
                'timestamp' => time()
            );
            @file_put_contents($rootPath . '.tracy-pw-revert', json_encode($markerData));
        } catch(\Exception $e) {} catch(\Error $e) {}
    }


    /**
     * Handle cleanup after an auto-revert occurred.
     * Called EARLY in init() (before redirect polling) so it can clear stale
     * session vars that would otherwise trigger unnecessary redirect loops.
     *
     * NOTE: The .tracy-pw-reverted log file is intentionally NOT deleted here.
     * The user notification is deferred to ready() where PW's session system is
     * fully stable. If ready() doesn't run, the file persists and the notification
     * shows on the next successful request.
     *
     * @param TracyDebugger $module
     */
    private static function cleanupPwVersionAutoRevert($module) {
        $rootPath = $module->wire('config')->paths->root;
        $revertedFile = $rootPath . '.tracy-pw-reverted';

        if(!file_exists($revertedFile)) return;

        // Auto-revert occurred — clean up all auto-revert infrastructure
        // (.tracy-pw-reverted is kept for ready() to read and display the notification)
        $markerFile = $rootPath . '.tracy-pw-revert';
        $handlerFile = $rootPath . '.tracy-pw-revert-handler.php';
        $attemptedFile = $rootPath . '.tracy-pw-revert-attempted';
        $includeLine = "if(file_exists(__DIR__ . '/.tracy-pw-revert-handler.php')) include __DIR__ . '/.tracy-pw-revert-handler.php';";

        @unlink($handlerFile);
        @unlink($markerFile); // should already be gone, but just in case
        @unlink($attemptedFile); // should already be gone, but just in case

        // remove the include line from index.php if present
        self::removePwRevertIncludeLine($rootPath, $includeLine);

        // clear stale session vars from the failed switch attempt
        $module->wire('session')->remove('tracyPwVersion');
        $module->wire('session')->remove('tracyPwVersionRetries');
    }


    /**
     * Clean up auto-revert marker after a successful version switch.
     * Called at the VERY END of init() — this is critical because if Tracy's init()
     * fails partway through (e.g. DeferredContent::sendAssets() throws because
     * deprecation warnings broke output buffering), this method is never reached,
     * the shutdown function is never registered, and the marker survives for the
     * handler to trigger a revert on the next request.
     *
     * Uses a shutdown function so cleanup happens AFTER ProcessWire::boot()
     * (init runs before boot, and boot() can fail on version-mismatched wire/).
     *
     * @param TracyDebugger $module
     */
    private static function cleanupPwVersionSwitchMarker($module) {
        $rootPath = $module->wire('config')->paths->root;
        $markerFile = $rootPath . '.tracy-pw-revert';

        if(!file_exists($markerFile)) return;

        $handlerFile = $rootPath . '.tracy-pw-revert-handler.php';
        $attemptedFile = $rootPath . '.tracy-pw-revert-attempted';
        $includeLine = "if(file_exists(__DIR__ . '/.tracy-pw-revert-handler.php')) include __DIR__ . '/.tracy-pw-revert-handler.php';";

        register_shutdown_function(function() use ($markerFile, $handlerFile, $attemptedFile, $includeLine, $rootPath) {
            // Don't clean up if an HTTP 500+ error occurred (e.g. boot() failed
            // after init() succeeded) — let the auto-revert handler fix it
            $code = http_response_code();
            if($code !== false && $code >= 500) return;
            // Don't clean up if marker was already removed by the handler's
            // fatal error shutdown function
            if(!file_exists($markerFile)) return;
            @unlink($markerFile);
            @unlink($handlerFile);
            @unlink($attemptedFile);
            // Remove the include line from index.php
            $indexPath = $rootPath . 'index.php';
            if(file_exists($indexPath)) {
                $indexContent = @file_get_contents($indexPath);
                if($indexContent !== false && strpos($indexContent, $includeLine) !== false) {
                    $indexContent = str_replace($includeLine . "\n", '', $indexContent);
                    @file_put_contents($indexPath, $indexContent);
                }
            }
        });
    }


    /**
     * Remove the auto-revert include line from index.php
     *
     * @param string $rootPath Site root path
     * @param string $includeLine The exact include line to remove
     */
    private static function removePwRevertIncludeLine($rootPath, $includeLine) {
        $indexPath = $rootPath . 'index.php';
        if(file_exists($indexPath)) {
            $indexContent = @file_get_contents($indexPath);
            if($indexContent !== false && strpos($indexContent, $includeLine) !== false) {
                $indexContent = str_replace($includeLine . "\n", '', $indexContent);
                @file_put_contents($indexPath, $indexContent);
            }
        }
    }

}
