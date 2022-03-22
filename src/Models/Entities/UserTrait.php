<?php

namespace WalkerChiu\MorphComment\Models\Entities;

trait UserTrait
{
    /**
     * @param String  $morph_type
     * @param Int     $morph_id
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments($morph_type = null, $morph_id = null)
    {
        return $this->hasMany(config('wk-core.class.morph-comment.comment'), 'user_id', 'id')
                    ->when($morph_type, function ($query, $morph_type) {
                                return $query->where('morph_type', $morph_type);
                            })
                    ->when($morph_id, function ($query, $morph_id) {
                                return $query->where('morph_id', $morph_id);
                            });
    }
}
