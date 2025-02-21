<?php

namespace Combizera\WpMigration;

use SimpleXMLElement;
use Exception;

class WordPressXmlParser
{
    protected SimpleXMLElement $xml;

    /**
     * Load the XML file and initialize the parser
     *
     * @throws Exception
     * @param string $filePath
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
     * Extracts and return an array of posts from the XML file
     *
     * @throws Exception
     * @return array
     */
    public function getPosts(): array
    {
        $posts = [];

        if (!isset($this->xml->channel) || !isset($this->xml->channel->item)) {
            throw new Exception("Invalid XML structure. Channel or items not found.");
        }

        foreach ($this->xml->channel->item as $item) {
            $namespaces = $item->getNamespaces(true);
            $content = isset($namespaces['content'])
                ? $this->parseContent($item->children($namespaces['content'])->encoded)
                : '';

            $posts[] = new Post(
                (string) $item->title,
                (string) $item->link,
                $content,
                //(string) $item->pubDate
            );
        }

        return $posts;
    }

    /**
     * Cleans and format the XML content.
     *
     * @param SimpleXMLElement|null $content
     * @return string
     */
    private function parseContent(?SimpleXMLElement $content): string
    {
        if (!$content) {
            return '';
        }

        $content = (string) $content;

        $content = preg_replace([
            '/<!--(.*?)-->/',
            '/\s*class="wp-[^"]*"/',
            '/\s+/'
        ], ['','', ' '], $content);

        return trim(preg_replace("/[\r\n]+/", "\n", $content));
    }
}