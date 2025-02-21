<?php

namespace Combizera\WpMigration;

use SimpleXMLElement;
use Exception;

class WordPressXmlParser
{
    protected SimpleXMLElement $xml;

    /**
     * @throws Exception
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("XML File not found.");
        }

        // LIBXML_NOCDATA - Merge CDATA as text nodes
        $this->xml = simplexml_load_file($filePath, "SimpleXMLElement", LIBXML_NOCDATA);

        if ($this->xml === false) {
            throw new Exception("Error loading XML file.");
        }
    }

    /**
     * @throws Exception
     */
    public function getPosts(): array
    {
        $posts = [];

        if (!isset($this->xml->channel) || !isset($this->xml->channel->item)) {
            throw new Exception("Invalid XML structure. Channel or items not found.");
        }

        foreach ($this->xml->channel->item as $item) {
            $namespaces = $item->getNamespaces(true);

            if (isset($namespaces['content'])) {
                $contentNamespace = $namespaces['content'];
                $contentEncoded = $item->children($contentNamespace)->encoded;
                $content = (string) $contentEncoded;

                $content = preg_replace([
                    '/<!--(.*?)-->/',
                    '/\s*class="wp-[^"]*"/',
                    '/\s+/'
                ], ['','', ' '], $content);

                $content = trim(preg_replace("/[\r\n]+/", "\n", $content));

            } else {
                $content = '';
            }

            $posts[] = new Post(
                (string) $item->title,
                (string) $item->link,
                $content,
                //(string) $item->pubDate
            );
        }

        return $posts;
    }
}