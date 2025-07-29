<?php

namespace silverorange\DevTest\Template;

use silverorange\DevTest\Context;

class PostDetails extends Layout
{
    protected function renderPage(Context $context): string
    {
        return <<<HTML
            <p>SHOW CONTENT FOR {$context->content} HERE</p>
            HTML;
    }
}
