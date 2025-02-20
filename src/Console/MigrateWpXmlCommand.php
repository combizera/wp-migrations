<?php

namespace Combizera\WpMigration\Console;

use Illuminate\Console\Command;
use Combizera\WpMigration\WordPressXmlParser;
use App\Models\Post;

class MigrateWpXmlCommand extends Command
{
    protected $signature = 'wp:migrate {file}';
    protected $description = 'Migra postagens de um XML do WordPress para o banco de dados';

    public function handle()
    {
        $file = $this->argument('file');

        try {
            $parser = new WordPressXmlParser($file);
            $posts = $parser->getPosts();

            foreach ($posts as $post) {
                Post::create([
                    'title' => $post->title,
                    'slug' => \Str::slug($post->title),
                    'content' => $post->content,
                    'published_at' => $post->publishedAt,
                ]);
            }

            $this->info(count($posts) . ' postagens migradas com sucesso!');
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
        }
    }
}
