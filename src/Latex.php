<?php

namespace Samwilson\FlickrLatex;

class Latex
{

    public static function texEsc($str)
    {
        $in = strip_tags($str);
        $pat = array('/\\\(\s)/', '/\\\(\S)/', '/&/', '/%/', '/\$/', '/>>/', '/_/', '/\^/', '/#/', '/"(\s)/', '/"(\S)/');
        $rep = array('\textbackslash\ $1', '\textbackslash $1', '\&', '\%', '\textdollar ', '\textgreater\textgreater ', '\_', '\^', '\#', '\textquotedbl\ $1', '\textquotedbl $1');
        return preg_replace($pat, $rep, $in);
    }

    public static function flickrDate($time, $granularity)
    {
        $granularities = array(
            '0' => 'Y-m-d H:i:s',
            '4' => 'Y-m',
            '6' => 'Y',
            '8' => '\c.~Y',
        );
        return date($granularities[$granularity], strtotime($time));
    }
}
