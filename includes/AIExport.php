<?php namespace ProcessWire;

/**
 * AIExport
 *
 * Helpers for producing AI/LLM-friendly plaintext exports from TracyDebugger:
 *   - dumpToText()  : render any PHP value as compact, stable plaintext
 *   - scrub()       : redact common secrets (API keys, tokens, PW config, emails, etc.)
 *   - button()      : HTML for a "Copy for AI" button + hidden payload element
 *
 * The output is intentionally plain text (no HTML, no ANSI colors) so the result
 * is safe to paste directly into an AI chat or save to a .txt / .md file.
 */
class AIExport {

    /**
     * Default caps for dumpToText. Separate from the rich HTML dumper's caps
     * so AI exports stay compact and fit in an LLM context window.
     */
    const DEFAULT_MAX_DEPTH = 4;
    const DEFAULT_MAX_ITEMS = 100;
    const DEFAULT_MAX_STRING = 400;

    /**
     * Format-based default patterns. Applied in order. These catch secrets that
     * are recognizable by structure (not by the name of a key). User-defined key
     * names live in Tracy's existing keysToHide config and are folded in at
     * scrub() time - see keysToHidePatterns().
     *
     * Each entry is [regex, replacement]. Replacements keep a short hint of
     * what was scrubbed so the AI can still reason about structure.
     */
    protected static $defaultPatterns = array(
        // Bearer / Basic auth headers
        array('/(Authorization:\s*Bearer\s+)[A-Za-z0-9\-_\.=]+/i', '$1[REDACTED_BEARER]'),
        array('/(Authorization:\s*Basic\s+)[A-Za-z0-9\+\/=]+/i',   '$1[REDACTED_BASIC]'),
        // AWS-style access keys
        array('/AKIA[0-9A-Z]{16}/',                                '[REDACTED_AWS_KEY]'),
        // GitHub / Slack / Stripe style token prefixes
        array('/\b(gh[pousr]_[A-Za-z0-9]{20,}|xox[baprs]-[A-Za-z0-9\-]{10,}|sk_live_[A-Za-z0-9]{10,}|rk_live_[A-Za-z0-9]{10,})\b/',
              '[REDACTED_TOKEN]'),
        // JWT
        array('/\beyJ[A-Za-z0-9_\-]+\.eyJ[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+\b/', '[REDACTED_JWT]'),
        // Email addresses (keep domain for context)
        array('/[A-Za-z0-9._%+\-]+@([A-Za-z0-9.\-]+\.[A-Za-z]{2,})/', '[REDACTED_EMAIL]@$1'),
        // IPv4 (leave first octet so AI knows it was an IP)
        array('/\b(\d{1,3})\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', '$1.x.x.x'),
    );

    /**
     * Get the list of sensitive key names, sourced from Tracy's existing
     * keysToHide config so users configure secrets in one place.
     *
     * @return string[]  lowercased key names
     */
    protected static function getKeysToHide() {
        $keys = array();
        // prefer the live Debugger::$keysToHide (already populated from module config)
        if(class_exists('\\Tracy\\Debugger') && property_exists('\\Tracy\\Debugger', 'keysToHide')) {
            $k = \Tracy\Debugger::$keysToHide;
            if(is_array($k)) $keys = $k;
        }
        // fallback to the module config value directly
        if(empty($keys) && class_exists('\\ProcessWire\\TracyDebugger')) {
            $raw = TracyDebugger::getDataValue('keysToHide');
            if(is_string($raw) && $raw !== '') {
                $keys = array_map('trim', explode(',', $raw));
            }
        }
        return array_filter(array_map('strtolower', $keys));
    }

    /**
     * Build regex patterns from keysToHide that match the same key names when
     * they appear in rendered plaintext as "key: value", "key=value", or
     * "key" => "value". This is only needed for text that was already rendered
     * outside of dumpToText() (e.g. logs, request bodies) - structured dumps
     * redact by key directly in dumpToText().
     *
     * @return array  entries of [regex, replacement]
     */
    protected static function keysToHidePatterns() {
        $patterns = array();
        foreach(self::getKeysToHide() as $key) {
            $q = preg_quote($key, '/');
            // key: value  (stop at whitespace, comma, semicolon, quote)
            $patterns[] = array('/(\b' . $q . '\b\s*[:=]\s*)[^\s,;"\']+/i', '$1[REDACTED]');
            // "key" => "value"   or   'key' => 'value'
            $patterns[] = array('/(["\']' . $q . '["\']\s*=>\s*["\'])[^"\']*(["\'])/i', '$1[REDACTED]$2');
        }
        return $patterns;
    }

    /**
     * Render any PHP value as compact, stable plaintext suitable for an LLM.
     *
     * Output example (object):
     *   Home [Page #1]
     *     id: 1
     *     template: home
     *     title: "My Site"
     *     children (3): Page, Page, Page
     *
     * @param mixed $var
     * @param array $options  maxDepth, maxItems, maxString, title
     * @param int   $depth    internal, current depth
     * @return string
     */
    public static function dumpToText($var, array $options = array(), $depth = 0) {
        $maxDepth  = isset($options['maxDepth'])  ? (int) $options['maxDepth']  : self::DEFAULT_MAX_DEPTH;
        $maxItems  = isset($options['maxItems'])  ? (int) $options['maxItems']  : self::DEFAULT_MAX_ITEMS;
        $maxString = isset($options['maxString']) ? (int) $options['maxString'] : self::DEFAULT_MAX_STRING;

        $indent = str_repeat('  ', $depth);

        if($var === null) return 'null';
        if(is_bool($var)) return $var ? 'true' : 'false';
        if(is_int($var) || is_float($var)) return (string) $var;

        if(is_string($var)) {
            $s = $var;
            if(function_exists('mb_strlen') && mb_strlen($s) > $maxString) {
                $s = mb_substr($s, 0, $maxString) . '…(+' . (mb_strlen($var) - $maxString) . ' chars)';
            } elseif(strlen($s) > $maxString) {
                $s = substr($s, 0, $maxString) . '…(+' . (strlen($var) - $maxString) . ' chars)';
            }
            $s = str_replace(array("\r\n", "\r"), "\n", $s);
            return '"' . str_replace('"', '\"', $s) . '"';
        }

        if(is_resource($var)) {
            return 'resource(' . get_resource_type($var) . ')';
        }

        if($depth >= $maxDepth) {
            if(is_array($var)) return 'array(' . count($var) . ') [depth limit]';
            if(is_object($var)) return self::describeObjectHeader($var) . ' [depth limit]';
        }

        if(is_array($var)) {
            if(empty($var)) return '[]';
            $isList = array_keys($var) === range(0, count($var) - 1);
            $hide = array_flip(self::getKeysToHide());
            $lines = array();
            $i = 0;
            foreach($var as $k => $v) {
                if($i >= $maxItems) {
                    $lines[] = $indent . '  …(+' . (count($var) - $maxItems) . ' more items)';
                    break;
                }
                $key = $isList ? '' : '[' . (is_string($k) ? $k : (string)$k) . '] ';
                if(is_string($k) && isset($hide[strtolower($k)])) {
                    $lines[] = $indent . '  ' . $key . '*****';
                    $i++;
                    continue;
                }
                $rendered = self::dumpToText($v, $options, $depth + 1);
                if(strpos($rendered, "\n") === false) {
                    $lines[] = $indent . '  ' . $key . $rendered;
                } else {
                    $lines[] = $indent . '  ' . $key;
                    $lines[] = $rendered;
                }
                $i++;
            }
            return 'array(' . count($var) . ')' . "\n" . implode("\n", $lines);
        }

        if(is_object($var)) {
            return self::dumpObject($var, $options, $depth, $indent);
        }

        return (string) $var;
    }

    /**
     * Describe an object's header line: "ClassName" or "Home [Page #1 /path]".
     */
    protected static function describeObjectHeader($obj) {
        $class = get_class($obj);
        if($obj instanceof \ProcessWire\Page) {
            $label = $obj->id ? ('[' . $class . ' #' . $obj->id . ' ' . $obj->path . ']') : ('[' . $class . ']');
            $title = (string) ($obj->title ?? $obj->name ?? '');
            return trim(($title !== '' ? $title . ' ' : '') . $label);
        }
        if($obj instanceof \ProcessWire\Field) {
            return $obj->name . ' [Field ' . (string) $obj->type . ']';
        }
        if($obj instanceof \ProcessWire\Template) {
            return $obj->name . ' [Template]';
        }
        if($obj instanceof \ProcessWire\WireArray) {
            return $class . '(' . count($obj) . ')';
        }
        return $class;
    }

    /**
     * Render a single object.
     */
    protected static function dumpObject($obj, array $options, $depth, $indent) {
        $maxItems = isset($options['maxItems']) ? (int) $options['maxItems'] : self::DEFAULT_MAX_ITEMS;
        $header = self::describeObjectHeader($obj);

        // ProcessWire Page: render a curated subset instead of every property
        if($obj instanceof \ProcessWire\Page) {
            $fields = array(
                'id'       => $obj->id,
                'name'     => $obj->name,
                'template' => $obj->template ? $obj->template->name : null,
                'parent'   => $obj->parent && $obj->parent->id ? $obj->parent->path : null,
                'status'   => $obj->status,
                'created'  => $obj->created ? date('Y-m-d H:i:s', $obj->created) : null,
                'modified' => $obj->modified ? date('Y-m-d H:i:s', $obj->modified) : null,
            );
            $out = $header . "\n";
            foreach($fields as $k => $v) {
                $out .= $indent . '  ' . $k . ': ' . self::dumpToText($v, $options, $depth + 1) . "\n";
            }
            // include field values if we still have depth budget
            if($depth + 1 < (isset($options['maxDepth']) ? (int)$options['maxDepth'] : self::DEFAULT_MAX_DEPTH) && $obj->template) {
                foreach($obj->template->fieldgroup as $f) {
                    if(!$f) continue;
                    $val = $obj->getUnformatted($f->name);
                    $out .= $indent . '  ' . $f->name . ': ' . self::dumpToText($val, $options, $depth + 1) . "\n";
                }
            }
            return rtrim($out, "\n");
        }

        if($obj instanceof \ProcessWire\WireArray) {
            $out = $header;
            $i = 0;
            foreach($obj as $item) {
                if($i >= $maxItems) {
                    $out .= "\n" . $indent . '  …(+' . (count($obj) - $maxItems) . ' more)';
                    break;
                }
                $out .= "\n" . $indent . '  - ' . self::dumpToText($item, $options, $depth + 1);
                $i++;
            }
            return $out;
        }

        // Generic object: public properties + __debugInfo if present
        $props = array();
        if(method_exists($obj, '__debugInfo')) {
            $props = (array) $obj->__debugInfo();
        } else {
            $props = get_object_vars($obj);
        }
        if(empty($props)) return $header;

        $hide = array_flip(self::getKeysToHide());
        $out = $header;
        $i = 0;
        foreach($props as $k => $v) {
            if($i >= $maxItems) {
                $out .= "\n" . $indent . '  …(+' . (count($props) - $maxItems) . ' more)';
                break;
            }
            if(is_string($k) && isset($hide[strtolower($k)])) {
                $out .= "\n" . $indent . '  ' . $k . ': *****';
            } else {
                $out .= "\n" . $indent . '  ' . $k . ': ' . self::dumpToText($v, $options, $depth + 1);
            }
            $i++;
        }
        return $out;
    }

    /**
     * Redact common secrets from a text blob. Applies format-based default
     * patterns plus patterns derived from Tracy's keysToHide config (so the
     * user configures sensitive key names in one place).
     *
     * @param string $text
     * @param array  $extraPatterns  optional extra [regex, replacement] entries
     * @return string
     */
    public static function scrub($text, array $extraPatterns = array()) {
        if(!is_string($text) || $text === '') return $text;

        $patterns = array_merge(self::$defaultPatterns, self::keysToHidePatterns());

        foreach($extraPatterns as $p) {
            if(is_array($p) && count($p) === 2) $patterns[] = $p;
        }

        foreach($patterns as $p) {
            $result = @preg_replace($p[0], $p[1], $text);
            if($result !== null) $text = $result;
        }
        return $text;
    }

}
