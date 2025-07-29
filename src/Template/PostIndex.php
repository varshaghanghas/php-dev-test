<?php

namespace silverorange\DevTest\Template;

use silverorange\DevTest\Context;

class PostIndex extends Layout
{
    protected function renderPage(Context $context): string
    {
        return <<<HTML
            <p>SHOW ALL {$context->content} POSTS HERE</p>
            HTML;
    }
}
