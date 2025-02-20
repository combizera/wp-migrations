<?php

namespace Combizera\WpMigration;

class Post
{
    public function __construct
    (
        public string $title,
        public string $slug,
        public string $content,
        // TODO: Pegar a data de publicação do post e inserir no created_at do Laravel
        //public string $publishedAt
    )
    {
        //
    }
}
