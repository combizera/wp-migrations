<?php

namespace Combizera\WpMigration\Console;

use App\Models\Category;
use Illuminate\Console\Command;
use Combizera\WpMigration\WpXmlParser;
use App\Models\Post;
use Illuminate\Support\Str;

class MigrateWpXmlCommand extends Command
{
    protected $signature = 'wp:migrate {file}';
    protected $description = 'Migrate posts from a WordPress XML to the database.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $file = $this->argument('file');

        try {
            $parser = new WpXmlParser($file);
            $posts = $parser->getPosts();

            $totalItems = count($parser->xml->channel->item ?? []);
            $totalPosts = count($posts);
            $totalPublished = count(array_filter($posts, fn($post) => $post->isPublished === 1));
            $totalUnpublished = $totalPosts - $totalPublished;

            $this->info("📄 XML file: {$file}");
            $this->info("🔍 Total items: {$totalItems}");
            $this->info("📝 Total posts: {$totalPosts}");
            $this->info("📢 Published: {$totalPublished} | ⏳ Draft: {$totalUnpublished}");
            $this->info("📂 Loading categories...");

            $existingCategories = Category::query()->pluck('slug')->toArray();
            $newCategories = [];

            foreach ($posts as $post) {
                foreach ($post->categories as $category) {
                    if (!in_array($category, $existingCategories)) {
                        $newCategories[] = $category;
                        $existingCategories[] = $category;
                    }
                }
            }

            foreach ($newCategories as $categoryName) {
                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName]
                );
            }

            $totalNewCategories = count($newCategories);
            $this->info("✅  {$totalNewCategories} new categories created.");

            $this->info("📌 Starting post migration...");

            $bar = $this->output->createProgressBar($totalPosts);
            $bar->start();

            foreach ($posts as $post) {
                $categoryId = !empty($post->categories) ? $post->categories[0] : $this->getDefaultCategoryId();
                $slug = $parser->parseSlug($post->title, $categoryId);

                Post::query()->create([
                    'category_id' => $categoryId,
                    'title' => $post->title,
                    'slug' => $slug,
                    'content' => $post->content,
                    'is_published' => $post->isPublished,
                    'created_at' => $post->createdAt,
                    'updated_at' => $post->updatedAt,
                ]);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("🎉 {$totalPosts} posts successfully migrated!");
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }

    /**
     * Get the default category ID
     *
     * @return int
     */
    private function getDefaultCategoryId(): int
    {
        $defaultCategory = Category::query()
            ->firstOrCreate(
                ['slug' => 'uncategorized'],
                ['name' => 'Uncategorized']
            );

        return $defaultCategory->id;
    }
}
