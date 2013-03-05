<?php

namespace micromachine;

class Response {

    private $body;
    private $headers;
    private $code;

    public function __construct($body, $headers=array(), $code=200) {
        $this->body = $body;
        $this->headers = $headers;
        $this->code = $code;
    }

    public function send_headers() {
        foreach($this->headers as $hname => $hvalue) {
            header("$hname: $hvalue");
        }
        if($this->code != 200) {
            header('X-Status-By: micromachine', true, $this->code);
        }        
    }

    public function output() {
        echo $this->body;
    }
    /*/
    public function code_header() {
        return self::HTTP_STATUS($this->code);
    }

    public static function HTTP_STATUS($code) {
        switch($code) {
            default:
                rx('code inconnu');
        }
    }
    //*/
}
