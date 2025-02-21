<?php

namespace Combizera\WpMigration;

class Post
{
    public function __construct
    (
        public string $title,
        public string $slug,
        public string $content,
        public string $publishedAt
    )
    {
        //
    }
}
