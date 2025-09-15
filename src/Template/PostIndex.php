<?php

namespace silverorange\DevTest\Template;

use silverorange\DevTest\Context;

class PostIndex extends Layout
{
    /** @var array<int, array{id:string,title:string,author_name:string,created_at:string}> */
    private array $posts = [];

    public function getContext(): Context
    {
        $context = new Context();
        $context->title = 'Posts';
        $context->posts = $this->posts;
        $context->content = (string)count($this->posts);
        return $context;
    }

    public function getTemplate(): Template\Template
    {
        return new Template\PostIndex();
    }

    protected function loadData(): void
    {
        // Choose the “published” check that matches your schema:
        // Option A (boolean):
        $where = 'p.published = TRUE';
        // Option B (timestamp):
        // $where = 'p.published_at IS NOT NULL';

        // Choose your chronology column. ‘created_at’ is fine, but if you
        // have ‘published_at’, it’s usually the better choice.
        $orderBy = 'p.created_at DESC';
        // If you have published_at, prefer: $orderBy = 'p.published_at DESC';

        $sql = <<<SQL
            SELECT
                p.id, p.title, p.created_at,
                a.full_name AS author_name
            FROM posts p
            INNER JOIN authors a ON a.id = p.author
            WHERE {$where}
            ORDER BY {$orderBy}
        SQL;

        $stmt = $this->db->query($sql);
        $this->posts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    protected function renderPage(Context $context): string
    {
        $items = '';
        foreach ($context->posts as $post) {
            $title  = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
            $author = htmlspecialchars($post['author_name'], ENT_QUOTES, 'UTF-8');
            $items .= <<<HTML
                <li class="posts-list__item">
                    <a href="/posts/{$post['id']}" class="posts-list__link">{$title}</a>
                    <span class="posts-list__byline">by {$author}</span>
                </li>
            HTML;
        }

        return <<<HTML
            <!-- <p>SHOW ALL {$context->content} POSTS HERE</p> -->
            <h1>Posts</h1>
            <ul class="posts-list">
                {$items}
            </ul>
            HTML;
    }
}
