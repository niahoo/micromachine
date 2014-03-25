<?php

// test: http://localhost/toulmuz/dyn/image/superkikkoo_fichier__lol.png(id,34)(istrue)(thrzzzzeevals,one,two,three)(cs,483058f920fc6f61e8f12408ca0f351a).jpg

class DynImg_Controller {

    public function generate($context, $media_query_string) {

        $media_query = mod_filestore::parse_media_query($media_query_string);

        r($media_query->hash($context->conf->get('filestore.salt')));
      
        if( !!!   $media_query->check($context->conf->get('filestore.salt'))) {
            return new \micromachine\Response('Invalid Checksum', array(), 403);
            // return Redirect(302,'lol');
        }
        else {
            $image = R::load('image', r($media_query->file_id()));
            if(!$image->id) {
                return new \micromachine\Response('Image not Found', array(), 404);
            }
        }
    }
}