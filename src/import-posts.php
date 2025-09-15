#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use silverorange\DevTest\Config;
use silverorange\DevTest\Database;

/**
 * Import all JSON post files from ./data into the database.
 * Each file should contain: id, title, body (Markdown), author (author id),
 * created_at, modified_at, and either published (bool) OR published_at (timestamp).
 */

// --- connect using your existing Config + Database classes ---
$config = new Config();
$pdo = (new Database($config->dsn))->getConnection();

// Optional but helpful if you want exceptions from PDO:
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- gather files ---
$files = glob(__DIR__ . '/../data/*.json') ?: [];
if ($files === []) {
    fwrite(STDERR, "No JSON files found in ./data\n");
    exit(1);
}

// --- figure out which “published” column your table has ---
$hasPublished = false;
$hasPublishedAt = false;

$colsStmt = $pdo->query(
    "SELECT column_name FROM information_schema.columns WHERE table_name = 'posts'"
);
$cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN);
if (is_array($cols)) {
    $hasPublished   = in_array('published', $cols, true);
    $hasPublishedAt = in_array('published_at', $cols, true);
}

// --- prepare an upsert matching your schema ---
if ($hasPublished && $hasPublishedAt) {
    $sql = <<<SQL
        INSERT INTO posts (id, title, body, created_at, modified_at, author, published, published_at)
        VALUES (:id, :title, :body, :created_at, :modified_at, :author, :published, :published_at)
        ON CONFLICT (id) DO UPDATE SET
            title = EXCLUDED.title,
            body = EXCLUDED.body,
            created_at = EXCLUDED.created_at,
            modified_at = EXCLUDED.modified_at,
            author = EXCLUDED.author,
            published = EXCLUDED.published,
            published_at = EXCLUDED.published_at
    SQL;
} elseif ($hasPublished) {
    $sql = <<<SQL
        INSERT INTO posts (id, title, body, created_at, modified_at, author, published)
        VALUES (:id, :title, :body, :created_at, :modified_at, :author, :published)
        ON CONFLICT (id) DO UPDATE SET
            title = EXCLUDED.title,
            body = EXCLUDED.body,
            created_at = EXCLUDED.created_at,
            modified_at = EXCLUDED.modified_at,
            author = EXCLUDED.author,
            published = EXCLUDED.published
    SQL;
} elseif ($hasPublishedAt) {
    $sql = <<<SQL
        INSERT INTO posts (id, title, body, created_at, modified_at, author, published_at)
        VALUES (:id, :title, :body, :created_at, :modified_at, :author, :published_at)
        ON CONFLICT (id) DO UPDATE SET
            title = EXCLUDED.title,
            body = EXCLUDED.body,
            created_at = EXCLUDED.created_at,
            modified_at = EXCLUDED.modified_at,
            author = EXCLUDED.author,
            published_at = EXCLUDED.published_at
    SQL;
} else {
    // Fallback: no published columns at all
    $sql = <<<SQL
        INSERT INTO posts (id, title, body, created_at, modified_at, author)
        VALUES (:id, :title, :body, :created_at, :modified_at, :author)
        ON CONFLICT (id) DO UPDATE SET
            title = EXCLUDED.title,
            body = EXCLUDED.body,
            created_at = EXCLUDED.created_at,
            modified_at = EXCLUDED.modified_at,
            author = EXCLUDED.author
    SQL;
}

$upsert = $pdo->prepare($sql);

// --- import them ---
foreach ($files as $file) {
    $json = json_decode((string)file_get_contents($file), true);
    if (!is_array($json)) {
        fwrite(STDERR, "Skipping {$file}: invalid JSON\n");
        continue;
    }

    // Basic guards (adjust as you like)
    foreach (['id','title','body','author','created_at','modified_at'] as $required) {
        if (!array_key_exists($required, $json)) {
            fwrite(STDERR, "Skipping {$file}: missing '{$required}'\n");
            continue 2;
        }
    }

    // Bind values that exist in the chosen SQL
    $params = [
        ':id'          => $json['id'],
        ':title'       => $json['title'],
        ':body'        => $json['body'],        // Markdown stored as-is
        ':created_at'  => $json['created_at'],
        ':modified_at' => $json['modified_at'],
        ':author'      => $json['author'],
    ];
    if ($hasPublished)   { $params[':published']    = (bool)($json['published']    ?? true); }
    if ($hasPublishedAt) { $params[':published_at'] = $json['published_at'] ?? null; }

    $upsert->execute($params);

    echo "Imported: {$json['id']} — {$json['title']}\n";
}

echo "Done.\n";
