<?php

namespace WalkerChiu\MorphComment\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;
use WalkerChiu\Core\Models\Entities\LangTrait;

class Comment extends Entity
{
    use LangTrait;

    /**
     * @param Array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.morph-comment.comments');

        $this->fillable = array_merge($this->fillable, [
            'morph_type', 'morph_id',
            'user_id',
            'score',
            'options', 'addresses',
            'is_private', 'is_highlighted',
            'is_enabled',
            'edit_at'
        ]);

        $this->casts = array_merge($this->casts, [
            'options'        => 'json',
            'is_private'     => 'boolean',
            'is_highlighted' => 'boolean',
            'edit_at'        => 'datetime'
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get it's lang entity.
     *
     * @return Lang
     */
    public function lang()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-morph-comment.onoff.core-lang_core')
        ) {
            return config('wk-core.class.core.langCore');
        } else {
            return config('wk-core.class.morph-comment.commentLang');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function langs()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-morph-comment.onoff.core-lang_core')
        ) {
            return $this->langsCore();
        } else {
            return $this->hasMany(config('wk-core.class.morph-comment.commentLang'), 'morph_id', 'id');
        }
    }

    /**
     * Get the owning commentable model.
     */
    public function morph()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('wk-core.class.user'), 'user_id', 'id');
    }

    /**
     * @param $type
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function addresses($type = null)
    {
        return $this->morphMany(config('wk-core.class.morph-address.address'), 'morph')
                    ->when($type, function ($query, $type) {
                                return $query->where('type', $type);
                            });
    }

    /**
     * Get all of the comments for the comment.
     *
     * @param Int  $user_id
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments($user_id = null)
    {
        return $this->morphMany(config('wk-core.class.morph-comment.comment'), 'morph')
                    ->when($user_id, function ($query, $user_id) {
                                return $query->where('user_id', $user_id);
                            });
    }

    /**
     * Check if it belongs to the user.
     * 
     * @param User  $user
     * @return Bool
     */
    public function isOwnedBy($user): bool
    {
        if (empty($user))
            return false;

        return $this->user_id == $user->id;
    }
}
