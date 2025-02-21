<?php

namespace Combizera\WpMigration;

use Carbon\Carbon;
use SimpleXMLElement;
use Exception;

class WpXmlParser
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
            var_dump((string) $item->pubDate);
            $namespaces = $item->getNamespaces(true);
            $content = isset($namespaces['content'])
                ? $this->parseContent($item->children($namespaces['content'])->encoded)
                : '';
            $publishedAt = isset($item->pubDate)
                ? $this->parseDate((string) $item->pubDate)
                : Carbon::now();

            $posts[] = new Post(
                (string) $item->title,
                (string) $item->link,
                $content,
                $publishedAt
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

    /**
     * Convert WP date format to Laravel `created_at` format
     *
     * @param string $pubDate
     * @return string
     */
    private function parseDate(string $pubDate): string
    {
        //TODO: no final do comando ele fale quantas postagens estavam sem data
        if (empty($pubDate)) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        try {
            return Carbon::createFromFormat(DATE_RSS, $pubDate)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}