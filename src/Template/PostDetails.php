<?php

namespace silverorange\DevTest\Template;

use silverorange\DevTest\Context;
use League\CommonMark\CommonMarkConverter;

class PostDetails extends Layout
{
    protected function renderPage(Context $context): string
    {
        if ($context->post === null) {
            // Shouldn’t happen because controller switches to NotFound(),
            // but keep a guard anyway.
            return '<p>Post not found.</p>';
        }

        // $converter = new CommonMarkConverter();
        $converter = new \League\CommonMark\CommonMarkConverter();

        $title = htmlspecialchars($context->post['title'], ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars($context->post['author_name'], ENT_QUOTES, 'UTF-8');
        $created = date('F j, Y', strtotime($context->post['created_at']));

        // Convert Markdown → HTML
        // $bodyHtml = $converter->convert($context->post['body'])->getContent();
        $markdown = (string)$context->post['body'];
        // Support both CommonMark v1 and v2:
        if (method_exists($converter, 'convert')) {
            // v2: convert() returns RenderedContentInterface
            /** @var \League\CommonMark\Output\RenderedContentInterface $rendered */
            $rendered = $converter->convert($markdown);
            $bodyHtml = $rendered->getContent();
        } else {
            // v1: convertToHtml() returns string
            $bodyHtml = $converter->convertToHtml($markdown);
        }


        return <<<HTML
            <!-- <p>SHOW CONTENT FOR {$context->content} HERE</p> -->
            <article class="post">
                <h1>{$title}</h1>
                <p class="meta">By {$author} on {$created}</p>
                <div class="post__body">{$bodyHtml}</div>
            </article>
            HTML;
    }
}
