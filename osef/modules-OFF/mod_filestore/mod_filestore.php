<?php

class mod_filestore {

    public static function init($context) {

        $context->set('files', new \micromachine\Ar($context->conf->get_default('gaufrette.adapters',array())));


// $path = function($p) {
//     return "/home/ludovic/osef/img/$p";   
// };
// // $imagine = new Imagine\Gd\Imagine();
// // or
// $imagine = new Imagine\Imagick\Imagine();
// // or
// // $imagine = new Imagine\Gmagick\Imagine();

// $size    = new Imagine\Image\Box(400, 800);
// $size2    = new Imagine\Image\Box(500, 150);
// $origin    = new Imagine\Image\Point(0, 0);

// $mode_inset    = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
// // or
// $mode_outbound    = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;



// $imagine->open($path('large.jpg'))
//     ->thumbnail($size, $mode_inset)
//     ->save($path('thumb-inset.jpg'))
// ;

// $imagine->open($path('large.jpg'))
//     ->thumbnail($size, $mode_outbound)
//     ->save($path('thumb-outbound.jpg'))
// ;

// $imagine->open($path('large.jpg'))
//     ->resize($size2)
//     ->save($path('resize.jpg'))
// ;

// $imagine->open($path('large.jpg'))
//     ->crop($origin, $size)
//     ->save($path('left-top-crop.jpg'))
// ;

// rx('k');


    }


    static private function pack_params($params) {
        $param_string = '';
        foreach ($params as $key => $values) {
            if(true === $values) {
                $param_string .= "($key)";
            }
            else {
                if(!is_array($values)) {
                    throw new InvalidArgumentException('Param values must be an array');
                }
                $vals = implode(',',$values);
                $param_string .= "($key,$vals)";
            }
        }
        return $param_string;
    }
   
   // returns Media_Query
    static function parse_media_query($media_query) {
        $matches = array();
        $params = array();
        // la regex se compose de 3 parties
        // 1. le slug, qui est n'importe quelle chaine ne contenant
        // pas ',' ni '(' ni ')'
        // 2. les params, une chaine composée de tuples accolés: "(key)(key2,value)(key3,value,val2)"
        // 3 l'extension du fichier demandé
        $pattern = 
            '/' . // borne debut de la regex
            '^([^\(\)]+)' . // le slug, capturé
           '((?:\((?:[a-zA-Z0-9]+)(?:,(?:[a-zA-Z0-9,]+))?\))+)' . // la param string, capturés
            '\.(\w+)' . // l'extension, capturée
            '/'; // borne fin de la regex
        
        $match= preg_match($pattern, $media_query, $matches);

        if(!$match) {
            throw new InvalidArgumentException('Invalid media query');
        }
        r($matches);
        list(,$slug, $param_string,$ext) = $matches;
        return new Media_Query($slug, self::unpack_params($param_string), $ext);

    }

    static private function hash_params(array $params) {
        ksort($params);
        if(isset($params['cs'])) unset($params['cs']);
        $str = serialize($params);
        return md5($str.self::$checksum_salt);
    }

    static private function unpack_params($param_string) {
        $matches = array();
        $tuples = array();
        $match = preg_match_all('/([\w0-9]+)(?:,((?:[,\w0-9]+)+))?/', $param_string, $matches, PREG_SET_ORDER);
        foreach($matches as $values) {
            $tuple = array($key = $values[1]);
            if(isset($values[2])) {
                $tuple = array_merge($tuple, explode(',',$values[2]));
            }
            $tuples[$key] = $tuple;
        }
        return $tuples;
    }

}

class Media_Query {

    const algo = 'md5';

    public function __construct($slug, $params, $ext) {
        $this->slug = $slug;
        if(!isset($params['cs'][1])) {
            $this->checksum = false;
        }
        else {
            $this->checksum = $params['cs'][1]; 
        }

        unset($params['cs']);
        ksort($params);
        $this->params = $params;

        $this->ext = $ext;
    }

    public function check($salt) {

        if($this->checksum !== false) {
            return $this->hash($salt) === $this->checksum;
        }
        return false;
        
    }

    public function hash($salt) {
        $chechstring = 
            $this->slug .
            serialize($this->params) .
            $this->ext .
            $salt;

        return hash(self::algo, $chechstring);
    }

    public function file_id() {
        return @$this->params['id'][1] ?: 0;
    }
}