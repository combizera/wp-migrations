<?php

namespace Combizera\WpMigration;

class Post
{
    public string $title;
    public string $link;
    public string $content;
    public string $publishedAt;

    public function __construct(string $title, string $link, string $content, string $publishedAt)
    {
        $this->title = $title;
        $this->link = $link;
        $this->content = $content;
        $this->publishedAt = $publishedAt;
    }
}
