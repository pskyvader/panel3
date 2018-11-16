<?php
namespace core;

defined("APPPATH") or die("Acceso denegado");

/**
 * @class minify
 */
class minify
{
    private function __construct()
    {

    }
    private static $x = "\x1A"; // a placeholder character
    private static $SS = '"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'';
    private static $CC = '\/\*[\s\S]*?\*\/';
    private static $CH = '<\!--[\s\S]*?-->';
    private static $TB = '<%1$s(?:>|\s[^<>]*?>)[\s\S]*?<\/%1$s>';
    public static function __minify_x($input)
    {
        return str_replace(array("\n", "\t", ' '), array(self::$x . '\n', self::$x . '\t', self::$x . '\s'), $input);
    }
    public static function __minify_v($input)
    {
        return str_replace(array(self::$x . '\n', self::$x . '\t', self::$x . '\s'), array("\n", "\t", ' '), $input);
    }

    private static function _minify_html($input)
    {
        return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#', function ($m) {
            if (isset($m[2])) {
                // minify inline CSS declaration(s)
                if (stripos($m[2], ' style=') !== false) {
                    $m[2] = preg_replace_callback('#( style=)([\'"]?)(.*?)\2#i', function ($m) {
                        return $m[1] . $m[2] . self::minify_css($m[3]) . $m[2];
                    }, $m[2]);
                }
                return '<' . $m[1] . preg_replace(
                    array(
                        // From `defer="defer"`, `defer='defer'`, `defer="true"`, `defer='true'`, `defer=""` and `defer=''` to `defer` [^1]
                        '#\s(checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped)(?:=([\'"]?)(?:true|\1)?\2)#i',
                        // Remove extra white-space(s) between HTML attribute(s) [^2]
                        '#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
                        // From `<img />` to `<img/>` [^3]
                        '#\s+\/$#',
                    ),
                    array(
                        // [^1]
                        ' $1',
                        // [^2]
                        ' $1$2',
                        // [^3]
                        '/',
                    ),
                    str_replace("\n", ' ', $m[2])) . '>';
            }
            return '<' . $m[1] . '>';
        }, $input);
    }
    public static function minify_html($input)
    {
        if (!$input = trim($input)) {
            return $input;
        }

        global $CH, $TB;
        // Keep important white-space(s) after self-closing HTML tag(s)
        $input = preg_replace('#(<(?:img|input)(?:\s[^<>]*?)?\s*\/?>)\s+#i', '$1' . self::$x . '\s', $input);
        // Create chunk(s) of HTML tag(s), ignored HTML group(s), HTML comment(s) and text
        $input = preg_split('#(' . $CH . '|' . sprintf($TB, 'pre') . '|' . sprintf($TB, 'code') . '|' . sprintf($TB, 'script') . '|' . sprintf($TB, 'style') . '|' . sprintf($TB, 'textarea') . '|<[^<>]+?>)#i', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";

        foreach ($input as $v) {
            if ($v !== ' ' && trim($v) === "") {
                continue;
            }
            if (strlen($v) == 1) {
                $output .= $v;
            } else {
                if ($v[0] === '<' && substr($v, -1) === '>') {
                    if ($v[1] === '!' && substr($v, 0, 4) === '<!--') { // HTML comment ...
                        // Remove if not detected as IE comment(s) ...
                        if (substr($v, -12) !== '<![endif]-->') {
                            continue;
                        }

                        $output .= $v;
                    } else {
                        $output .= self::__minify_x(self::_minify_html($v));
                    }
                } else {
                    // Force line-break with `&#10;` or `&#xa;`
                    $v = str_replace(array('&#10;', '&#xA;', '&#xa;'), self::$x . '\n', $v);
                    // Force white-space with `&#32;` or `&#x20;`
                    $v = str_replace(array('&#32;', '&#x20;'), self::$x . '\s', $v);
                    // Replace multiple white-space(s) with a space
                    $output .= preg_replace('#\s+#', ' ', $v);
                }
            }

        }
        // Clean up ...
        $output = preg_replace(
            array(
                // Remove two or more white-space(s) between tag [^1]
                '#>([\n\r\t]\s*|\s{2,})<#',
                // Remove white-space(s) before tag-close [^2]
                '#\s+(<\/[^\s]+?>)#',
            ),
            array(
                // [^1]
                '><',
                // [^2]
                '$1',
            ),
            $output);
        $output = self::__minify_v($output);
        // Remove white-space(s) after ignored tag-open and before ignored tag-close (except `<textarea>`)
        return preg_replace('#<(code|pre|script|style)(>|\s[^<>]*?>)\s*([\s\S]*?)\s*<\/\1>#i', '<$1$2$3</$1>', $output);
    }

    private static function _minify_css($input)
    {
        // Keep important white-space(s) in `calc()`
        if (stripos($input, 'calc(') !== false) {
            $input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', function ($m) {
                return $m[1] . preg_replace('#\s+#', X . '\s', $m[2]) . ')';
            }, $input);
        }
        // minify ...
        return preg_replace(
            array(
                // Fix case for `#foo [bar="baz"]` and `#foo :first-child` [^1]
                '#(?<![,\{\}])\s+(\[|:\w)#',
                // Fix case for `[bar="baz"] .foo` and `url(foo.jpg) no-repeat` [^2]
                '#\]\s+#', '#\)\s+\b#',
                // minify HEX color code ... [^3]
                '#\#([\da-f])\1([\da-f])\2([\da-f])\3\b#i',
                // Remove white-space(s) around punctuation(s) [^4]
                '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
                // Replace zero unit(s) with `0` [^5]
                '#\b(?:0\.)?0([a-z]+\b|%)#i',
                // Replace `0.6` with `.6` [^6]
                '#\b0+\.(\d+)#',
                // Replace `:0 0`, `:0 0 0` and `:0 0 0 0` with `:0` [^7]
                '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
                // Replace `background(?:-position)?:(0|none)` with `background$1:0 0` [^8]
                '#\b(background(?:-position)?):(0|none)\b#i',
                // Replace `(border(?:-radius)?|outline):none` with `$1:0` [^9]
                '#\b(border(?:-radius)?|outline):none\b#i',
                // Remove empty selector(s) [^10]
                '#(^|[\{\}])(?:[^\s\{\}]+)\{\}#',
                // Remove the last semi-colon and replace multiple semi-colon(s) with a semi-colon [^11]
                '#;+([;\}])#',
                // Replace multiple white-space(s) with a space [^12]
                '#\s+#',
            ),
            array(
                // [^1]
                self::$x . '\s$1',
                // [^2]
                ']' . self::$x . '\s', ')' . self::$x . '\s',
                // [^3]
                '#$1$2$3',
                // [^4]
                '$1',
                // [^5]
                '0',
                // [^6]
                '.$1',
                // [^7]
                ':0',
                // [^8]
                '$1:0 0',
                // [^9]
                '$1:0',
                // [^10]
                '$1',
                // [^11]
                '$1',
                // [^12]
                ' ',
            ),
            $input);
    }
    public static function minify_css($input)
    {
        if (!$input = trim($input)) {
            return $input;
        }

        global $SS, $CC;
        // Keep important white-space(s) between comment(s)
        $input = preg_replace('#(' . $CC . ')\s+(' . $CC . ')#', '$1' . self::$x . '\s$2', $input);
        // Create chunk(s) of string(s), comment(s) and text
        $input = preg_split('#(' . $SS . '|' . $CC . ')#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach ($input as $v) {
            if (trim($v) === "") {
                continue;
            }

            if (
                ($v[0] === '"' && substr($v, -1) === '"') ||
                ($v[0] === "'" && substr($v, -1) === "'") ||
                (substr($v, 0, 2) === '/*' && substr($v, -2) === '*/')
            ) {
                // Remove if not detected as important comment ...
                if ($v[0] === '/' || (substr($v, 0, 2) === '/*' && substr($v, -2) === '*/')) {
                    continue;
                }

                $output .= $v; // String or comment ...
            } else {
                $output .= self::_minify_css($v);
            }
        }
        // Remove quote(s) where possible ...
        $output = preg_replace(
            array(
                '#(' . $CC . ')|(?<!\bcontent\:)([\'"])([a-z_][-\w]*?)\2#i',
                '#(' . $CC . ')|\b(url\()([\'"])([^\s]+?)\3(\))#i',
            ),
            array(
                '$1$3',
                '$1$2$4$5',
            ),
            $output);
        return self::__minify_v($output);
    }

    private static function _minify_js($input)
    {
        return preg_replace(
            array(
                // Remove inline comment(s) [^1]
                '#\s*\/\/.*$#m',
                // Remove white-space(s) around punctuation(s) [^2]
                '#\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#',
                // Remove the last semi-colon and comma [^3]
                '#[;,]([\]\}])#',
                // Replace `true` with `!0` and `false` with `!1` [^4]
                '#\btrue\b#', '#false\b#', '#return\s+#',
            ),
            array(
                // [^1]
                "",
                // [^2]
                '$1',
                // [^3]
                '$1',
                // [^4]
                '!0', '!1', 'return ',
            ),
            $input);
    }
    public static function minify_js($input)
    {
        if (!$input = trim($input)) {
            return $input;
        }

        // Create chunk(s) of string(s), comment(s), regex(es) and
        $SS = self::$SS;
        $CC = self::$CC;
        $input = preg_split('#(' . $SS . '|' . $CC . '|\/[^\n]+?\/(?=[.,;]|[gimuy]|$))#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach ($input as $v) {
            if (trim($v) === "") {
                continue;
            }
            if (
                ($v[0] === '"' && substr($v, -1) === '"') ||
                ($v[0] === "'" && substr($v, -1) === "'") ||
                ($v[0] === '/' && substr($v, -1) === '/')
            ) {
                // Remove if not detected as important comment ...
                if (substr($v, 0, 2) === '//' || (substr($v, 0, 2) === '/*')) {
                    continue;
                }

                $output .= $v; // String, comment or regex ...
            } else {
                $output .= self::_minify_js($v);
            }
        }
        $output = preg_replace(
            array(
                // minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}` [^1]
                '#(' . $CC . ')|([\{,])([\'])(\d+|[a-z_]\w*)\3(?=:)#i',
                // From `foo['bar']` to `foo.bar` [^2]
                '#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i',
            ),
            array(
                // [^1]
                '$1$2$4',
                // [^2]
                '$1.$3',
            ),
            $output);

        $output = preg_replace('/\s+/', ' ', $output);
        return $output;

    }

}
