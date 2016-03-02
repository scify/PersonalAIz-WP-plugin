<?php

class JSONObject {

    public $id;
    public $language;
    public $recommended;
    public $timestamp;
    public $text;
    public $category;
    public $tag;

    public function __construct($id, $language, $recommended, $timestamp, $text, $category, $tag) {
        $this->id = $id;
        $this->language = $language;
        $this->recommended = $recommended;
        $this->timestamp = $timestamp;
        $this->text = $text;
        $this->category = $category;
        $this->tag = $tag;
    }

}
