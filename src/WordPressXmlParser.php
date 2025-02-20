<?php

namespace Combizera\WpMigration;

use SimpleXMLElement;
use Exception;

class WordPressXmlParser
{
    protected SimpleXMLElement $xml;

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo XML não encontrado.");
        }

        $this->xml = simplexml_load_file($filePath, "SimpleXMLElement", LIBXML_NOCDATA);
    }

    public function getPosts(): array
    {
        $posts = [];
        foreach ($this->xml->channel->item as $item) {
            $posts[] = new Post(
                (string) $item->title,
                (string) $item->link,
                (string) $item->content,
                (string) $item->pubDate
            );
        }
        return $posts;
    }
}
