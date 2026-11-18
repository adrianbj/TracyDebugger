<?php namespace ProcessWire;

class ProcessTracyAdminerRenderer extends Process implements Module {

    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer Renderer', __FILE__),
            'summary' => __('Adminer renderer for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.11',
            'autoload' => false,
            'singular' => true,
            'icon' => 'database',
            'requires'  => 'ProcessWire>=3.0.0, PHP>=7.1.0, TracyDebugger',
            'page' => array(
                'name' => 'adminer-renderer',
                'parent' => 'setup',
                'title' => 'Adminer Renderer',
                'status' => 'hidden'
            )
        );
    }

    public function ___execute() {
        // a superuser, and with "Restrict superusers" on, one with the tracy-debugger permission (as for the bar)
        if(!$this->wire('user')->isSuperuser() || (class_exists('\\ProcessWire\\TracyDebugger', false) && !TracyDebugger::adminerAllowed())) throw new Wire404Exception();

        /* AdminNeo's forms post from its own pages in the iframe, so a POST from another site is never
           ours. And a POST carrying auth[...] is AdminNeo's login form, which isn't used here (login is
           automatic, see ExternalLoginPlugin): AdminNeo gives such a request a valid token of its own
           before checking it, so with the automatic login it would run without one. */
        if(isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
            $fetchSite = isset($_SERVER['HTTP_SEC_FETCH_SITE']) ? $_SERVER['HTTP_SEC_FETCH_SITE'] : '';
            $sameOrigin = $fetchSite !== '' ? $fetchSite === 'same-origin' : (class_exists('\\ProcessWire\\TracyDebugger', false) && TracyDebugger::sameOriginRequest());
            if(isset($_POST['auth']) || !$sameOrigin) {
                http_response_code(403);
                exit;
            }
        }

        /* AdminNeo keeps the database password in the session (ExternalLoginPlugin's automatic login saves
           it there), encrypted only if the browser has its "neo_key" cookie, which AdminNeo itself only
           sets on its login page: a page the automatic login never shows. So set the cookie here, and drop
           a password that an earlier request saved unencrypted, which makes the plugin save it again. */
        if(empty($_COOKIE['neo_key']) || !is_string($_COOKIE['neo_key'])) {
            $neoKey = bin2hex(random_bytes(16));
            $_COOKIE['neo_key'] = $neoKey;
            $cookiePath = $this->wire('config')->urls->admin;
            $secure = (bool) $this->wire('config')->https;
            if(PHP_VERSION_ID >= 70300) {
                setcookie('neo_key', $neoKey, array('expires' => 0, 'path' => $cookiePath, 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax'));
            }
            else {
                setcookie('neo_key', $neoKey, 0, $cookiePath, '', $secure, true);
            }
        }
        if(isset($_SESSION['pwds']) && is_array($_SESSION['pwds'])) {
            foreach($_SESSION['pwds'] as $driver => $servers) {
                foreach((array) $servers as $server => $users) {
                    foreach((array) $users as $username => $password) {
                        if(!is_array($password)) unset($_SESSION['pwds'][$driver][$server][$username]);
                    }
                }
            }
        }

        /* AdminNeo turns on zlib.output_compression partway through the response —
           page_header() in adminneo.php does it right before emitting the document.
           PHP then sends Content-Encoding: gzip and compresses only what follows,
           so anything already echoed is delivered as plaintext ahead of the gzip
           stream and the browser rejects the whole response with
           ERR_CONTENT_DECODING_FAILED (a 200 with an undisplayable body).

           Under ProcessWire there is always something to echo: adminneo.php calls
           ini_set('session.use_trans_sid', '0') at include time, which PHP refuses
           with a warning because PW has already started the session. With
           display_errors on (PW debug mode) that warning lands in the output.

           Keep PHP's error display out of this response body — it is AdminNeo's
           document, not a PW page. Errors still reach Tracy's logs and bar. */
        ini_set('display_errors', '0');

        /* AdminNeo serves its own CSS, JS, icons and favicons through ?file= URLs, which means
           every one of them (11 per page view) is a full ProcessWire admin request through this
           module. PHP's session cache limiter has already queued "Pragma: no-cache" by the time we
           get here, and adminneo.php's file handler only sends "Cache-Control: immutable" with no
           max-age, so browsers revalidate all of them on every navigation instead of using their
           cache. The URLs already carry a content hash, so they are safe to cache for a long time.

           This has to be a header callback rather than plain header() calls, because adminneo.php
           sends its own headers after this point and header() would replace ours. Only 200/304
           responses are made cacheable so an unknown filename's 404 isn't kept for a year, and
           "private" keeps this to the browser cache: the response is superuser-only and may carry
           a session cookie, so a shared cache must not store it. */
        if(isset($_GET['file'])) {
            header_register_callback(function() {
                if(!in_array(http_response_code(), array(200, 304))) return;
                header_remove('Pragma');
                header('Cache-Control: private, max-age=31536000, immutable');
            });
        }

        require_once __DIR__ . '/panels/Adminer/adminneo-instance.php';
        require_once __DIR__ . '/panels/Adminer/adminneo.php';
        exit;
    }
}
