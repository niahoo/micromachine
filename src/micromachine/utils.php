<?php
//@todo classer les fonctions

function attr_getter($attr_name) {
    return function ($object) use ($attr_name) {
        return $object->$attr_name;
    };
}

function switch_domain($domain,$protocol='http') {
    return "$protocol://$domain".$_SERVER['REQUEST_URI'];
}

function Ymd($time=null) {
    if(is_null($time)) $time = time();
    return date('Y-m-d', $time);
}

function Response($body, $headers=array(), $code=200) {
    return new \micromachine\Response($body, $headers, $code);
}

function Redirect($code, $url) {
    $body = '';
    $headers = array('location' => $url);
    return new \micromachine\Response($body, $headers, $code);
}

function ensure_session_start() {
    if ('' === session_id()) {
        session_start();
    }
}

function mkpath() {
    $args = func_get_args();
    return implode(DIRECTORY_SEPARATOR, $args);
}

function arw(array $data) {
    return new \micromachine\Ar($data);
}

// creates a Controllerhandler
function ch($controller, $action=null) {
    return new \micromachine\ControllerHandler($controller, $action);
}

function v($any) { return $any; }

function r($var, $label='', $step=0) {
    try {
        throw new Exception('');
    }
    catch (Exception $e) {
        $trace = $e->getTrace();
        $file_infos = '';

        $sep_tag = "\n";

        for($i = 0; $i <= $step; $i++) {
            if(! isset($trace[$i])) break;
            $previous = $trace[$i];
            @$file_call = $previous['file'] ?: '== no file ==';
            @$line_call = $previous['line'] ?: '== no line ==';
            if(''===$label && isset($previous['file'])) {
                $text = file($previous['file']);
                $labeldisplay = $i . ' ' . trim($text[$line_call - 1]);
            }
            elseif('' === $label) {
                $labeldisplay = ' -- ';
            }
            else {
                $labeldisplay = $label;
            }
            $file_infos .= $labeldisplay . ' ' . $file_call . ' line ' . $line_call . $sep_tag;
        }



        $format_tag_start = '<pre class="rdebug" style="font-family:monospace;color:#eee;font-size:16px;background:#444;padding:5px;margin:0 0 18px 0;-moz-border-radius:3px;border-radius:3px;">';

        $format_tag_end = '</pre>';

        echo $format_tag_start . $file_infos . $sep_tag;
        if(is_null($var)) {
            var_dump($var);
        }
        else {
            // print_r($var);
            var_dump($var);
        }

        echo $format_tag_end;
    }
    return $var;
}

function rx($var = "rx() exit", $label='', $step=0) {
    r($var, $label, ++$step);
    exit('rx()');
}

function unaccent ($string) {
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
    );

    return strtr($string, $table);
}

function slugify($text) {

  $text = unaccent($text);

  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

  // trim
  $text = trim($text, '-');

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // lowercase
  $text = strtolower($text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  if (empty($text))
  {
    return 'n-a';
  }

  return $text;
}

if(!function_exists('apc_exists')) {
function apc_exists($keys) {
    $result;
    apc_fetch($keys, $result);
    return $result;
}
}
