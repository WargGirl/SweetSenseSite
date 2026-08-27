<?php

class Page {
    protected $title;

    public function __construct($title = "") {
        $this->title = $title ? $title : SITE_NAME;
    }

    public function renderHeader() {
        global $lang;
        
        $pageTitle = $this->title;

        require_once INCLUDES_PATH . '/site_header.php';
    }

    public function renderFooter() {
        global $lang;

        require_once INCLUDES_PATH . '/site_footer.php';
    }
}






