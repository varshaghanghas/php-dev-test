<?php

namespace silverorange\DevTest;

class Context
{
    // TODO: You can add more properties to this class to pass values to templates

    public string $title = '';

    public string $content = '';

    
    /** @var array<int, array{id:string,title:string,author_name:string,created_at:string}> */
    public array $posts = [];

    /** @var null|array{id:string,title:string,body:string,author_name:string,created_at:string,modified_at:string} */
    public ?array $post = null;
    
}
