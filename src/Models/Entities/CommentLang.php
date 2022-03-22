<?php

namespace WalkerChiu\MorphComment\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class CommentLang extends Lang
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.morph-comment.comments_lang');

        parent::__construct($attributes);
    }
}
