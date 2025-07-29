<?php

namespace silverorange\DevTest\Template;

use silverorange\DevTest\Context;

class Root extends Layout
{
    protected function renderPage(Context $context): string
    {
        return <<<HTML
            <p>Things are up and running—you’ve got this!</p>
            HTML;
    }
}
