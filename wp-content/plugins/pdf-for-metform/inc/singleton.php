<?php 

namespace PDF4Metform\Inc;

trait Singleton{

    public static $instance;

    public static function instance(){
        
        if( ! static::$instance ){
            static::$instance = new Self();
        } 
        return static::$instance;
    }

}