<?php

namespace silverorange\DevTest\Controller;

use silverorange\DevTest\Context;
use silverorange\DevTest\Template;
use silverorange\DevTest\Model;

class PostDetails extends Controller
{
    /**
     * TODO: When this property is assigned in loadData this PHPStan override
     * can be removed.
     *
     * @phpstan-ignore property.unusedType
     */
    private ?Model\Post $post = null;
    private ?string $authorName = null;

    public function getContext(): Context
    {
        $context = new Context();

        if ($this->post === null) {
            $context->title = 'Not Found';
            $context->content = "A post with id {$this->params[0]} was not found.";
            return $context;
        } else {
            // $context->title = $this->post->title;
            // $context->content = $this->params[0];

            $context->title = $this->post->title;
            $context->post = [
                'id'           => $this->post->id,
                'title'        => $this->post->title,
                'body'         => $this->post->body,
                'author_name'  => (string)$this->authorName,
                'created_at'   => $this->post->created_at,
                'modified_at'  => $this->post->modified_at,
            ];


        }

        return $context;
    }

    public function getTemplate(): Template\Template
    {
        if ($this->post === null) {
            return new Template\NotFound();
        }

        return new Template\PostDetails();
    }

    public function getStatus(): string
    {
        if ($this->post === null) {
            return $this->getProtocol() . ' 404 Not Found';
        }

        return $this->getProtocol() . ' 200 OK';
    }

    /**
     * Helper: check if a column exists on a table (cached per request).
     */
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
        // TODO: Load post from database here. $this->params[0] is the post id.
        // $this->post = null;
        
        // Build a WHERE clause that matches your schema:
        // - If posts.published exists (boolean), require TRUE
        // - else if posts.published_at exists (timestamp), require NOT NULL
        // - else no published filter
        $filters = ['p.id = :id'];

        if ($this->hasColumn('posts', 'published')) {
            $filters[] = 'p.published = TRUE';
        } elseif ($this->hasColumn('posts', 'published_at')) {
            $filters[] = 'p.published_at IS NOT NULL';
        }

        $where = implode(' AND ', $filters);

        $sql = <<<SQL
            SELECT
                p.id, p.title, p.body, p.created_at, p.modified_at, p.author,
                a.full_name AS author_name
            FROM posts p
            INNER JOIN authors a ON a.id = p.author
            WHERE {$where}
            LIMIT 1
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $this->params[0]]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $this->post = null;
            $this->authorName = null;
            return;
        }

        $post = new Model\Post();
        $post->id          = $row['id'];
        $post->title       = $row['title'];
        $post->body        = $row['body'];
        $post->created_at  = $row['created_at'];
        $post->modified_at = $row['modified_at'];
        $post->author      = $row['author'];

        $this->post = $post;
        $this->authorName = $row['author_name'];
    }
}
