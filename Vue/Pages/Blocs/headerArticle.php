<?php

    // On insère le css spécifique aux articles
    array_push($this->headers, [
        'tagname' => 'link',
        'content' => '',
        'attributes' => [
            ['name' => 'type', 'value' => 'text/css'],
            ['name' => 'rel', 'value' => 'stylesheet'],
            ['name' => 'href', 'value' => './static/css/article.css'],
        ]
    ]);
