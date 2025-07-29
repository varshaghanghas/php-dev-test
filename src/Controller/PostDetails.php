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

    public function getContext(): Context
    {
        $context = new Context();

        if ($this->post === null) {
            $context->title = 'Not Found';
            $context->content = "A post with id {$this->params[0]} was not found.";
        } else {
            $context->title = $this->post->title;
            $context->content = $this->params[0];
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

    protected function loadData(): void
    {
        // TODO: Load post from database here. $this->params[0] is the post id.
        $this->post = null;
    }
}
