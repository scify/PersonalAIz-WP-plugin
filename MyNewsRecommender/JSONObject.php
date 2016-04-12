<?php

/**
* Copyright 2016 IIT , NCSR Demokritos - http://www.iit.demokritos.gr,
                       SciFY NPO - http://www.scify.org
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*    http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

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
