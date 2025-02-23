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

            $this->info('Starting migration...');

            $bar = $this->output->createProgressBar(count($posts));
            $bar->start();

            foreach ($posts as $post) {
                $categoryId = !empty($post->categories) ? $post->categories[0] : $this->getDefaultCategoryId();

                $slug = $this->generateUniqueSlug(Str::slug($post->title), $categoryId);

                Post::query()->create([
                    'title' => $post->title,
                    'slug' => $slug,
                    'content' => $post->content,
                    'created_at' => $post->publishedAt,
                    'category_id' => $categoryId,
                ]);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $this->info(count($posts) . ' posts migrate with success!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
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
                ['name' => 'Sem Categoria']
            );

        return $defaultCategory->id;
    }

    /**
     * Generate a unique slug for the post.
     * If the slug already exists, append the category slug to make it unique.
     *
     * @param string $slug
     * @param int $categoryId
     * @return string
     */
    private function generateUniqueSlug(string $slug, int $categoryId): string
    {
        $categorySlug = Category::query()->find($categoryId)->slug;

        if (!Post::query()->where('slug', $slug)->exists()) {
            return $slug;
        }

        $newSlug = Str::slug($categorySlug . '-' . $slug);

        $counter = 1;
        while (Post::query()->where('slug', $newSlug)->exists()) {
            $newSlug = Str::slug($categorySlug . '-' . $slug . '-' . $counter);
            $counter++;
        }

        return $newSlug;
    }
}
