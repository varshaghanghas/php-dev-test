<?php

namespace silverorange\DevTest\Controller;

use silverorange\DevTest\Context;
use silverorange\DevTest\Model\Post;
use silverorange\DevTest\Template;

class PostIndex extends Controller
{
    /**
     * @var array<Post>
     */
    private array $posts = [];

    public function getContext(): Context
    {
        $context = new Context();
        $context->title = 'Posts';
        $context->posts = $this->posts;
        $context->content = strval(count($this->posts));
        return $context;
    }

    public function getTemplate(): Template\Template
    {
        return new Template\PostIndex();
    }

    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = "$table.$column";
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        $stmt = $this->db->prepare(
            "SELECT 1
               FROM information_schema.columns
              WHERE table_name = :t
                AND column_name = :c"
        );
        $stmt->execute([':t' => $table, ':c' => $column]);
        $cache[$key] = (bool)$stmt->fetchColumn();
        return $cache[$key];
    }

    protected function loadData(): void
    {
        // TODO: Load posts from database here.
        // $this->posts = [];
        $filters = [];
        if ($this->hasColumn('posts', 'published')) {
            $filters[] = 'p.published = TRUE';
        } elseif ($this->hasColumn('posts', 'published_at')) {
            $filters[] = 'p.published_at IS NOT NULL';
        }
        $where = $filters ? ('WHERE ' . implode(' AND ', $filters)) : '';

        // Prefer published_at for ordering if it exists, else created_at.
        $orderBy = $this->hasColumn('posts', 'published_at')
            ? 'p.published_at DESC'
            : 'p.created_at DESC';

        $sql = <<<SQL
            SELECT
                p.id, p.title, p.created_at,
                a.full_name AS author_name
            FROM posts p
            INNER JOIN authors a ON a.id = p.author
            {$where}
            ORDER BY {$orderBy}
        SQL;

        $stmt = $this->db->query($sql);
        $this->posts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
