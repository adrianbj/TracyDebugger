<?php namespace ProcessWire;

class ProcessTracyAdminerRenderer extends Process implements Module {

    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer Renderer', __FILE__),
            'summary' => __('Adminer renderer for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.5',
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
        if(!$this->wire('user')->isSuperuser()) throw new Wire404Exception();

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

        require_once __DIR__ . '/panels/Adminer/adminneo-instance.php';
        require_once __DIR__ . '/panels/Adminer/adminneo.php';
        exit;
    }
}
