<?php
class gallery
{
var $id;
var $filename = "";
var $name;
var $desc = "";
var $long_desc = "";
var $date = "";
var $images = array();
var $galleries = array();

function gallery($id)
    {
        $this->id = $id;
    }
}


?>
