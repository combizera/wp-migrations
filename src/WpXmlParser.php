<?php

namespace Combizera\WpMigration;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Str;
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
            $namespaces = $item->getNamespaces(true);

            if (!isset($namespaces['wp'])) {
                continue;
            }

            //TODO: deixar um log do tipo '150' itens no arquivo xml, '50' itens são do tipo post. Analisando
            $wpData = $item->children($namespaces['wp']);
            if (!isset($wpData->post_type) || (string) $wpData->post_type !== 'post') {
                continue;
            }

            $content = isset($namespaces['content'])
                ? $this->parseContent($item->children($namespaces['content'])->encoded)
                : '';

            $publishedAt = isset($item->pubDate)
                ? $this->parseDate((string) $item->pubDate)
                : Carbon::now();

            $categories = $this->parseCategories($item);

            $posts[] = new Post(
                (string) $item->title,
                (string) $item->link,
                $content,
                $publishedAt,
                $categories
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

    /**
     * Parse categories from the XML file
     *
     * @param SimpleXMLElement $item
     * @return array<int> List of category IDs
     */
    private function parseCategories(SimpleXMLElement $item): array
    {
        $categories = [];

        foreach ($item->category as $category) {
            $categoryName = trim((string) $category);

            if (!empty($categoryName)) {
                $existingCategory = Category::query()->firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName]
                );

                $categories[] = $existingCategory->id;
            }
        }

        return $categories;
    }

    /**
     * Generate a unique slug for the post.
     * If the slug already exists in the same category, append a number (ex: slug-1, slug-2).
     *
     * @param string $title
     * @param int $categoryId
     * @return string
     */
    public function parseSlug(string $title, int $categoryId): string
    {
        $baseSlug = Str::slug($title);

        if (!\App\Models\Post::query()->where('slug', $baseSlug)->exists()) {
            return $baseSlug;
        }

        $counter = 1;
        $newSlug = $baseSlug . '-' . $counter;

        while (\App\Models\Post::query()->where('slug', $newSlug)->exists()) {
            $counter++;
            $newSlug = $baseSlug . '-' . $counter;
        }

        return $newSlug;
    }
}