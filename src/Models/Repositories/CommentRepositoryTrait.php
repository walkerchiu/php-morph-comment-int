<?php

namespace WalkerChiu\MorphComment\Models\Repositories;

trait CommentRepositoryTrait
{
    /*
    |--------------------------------------------------------------------------
    | Get comment list to show
    |--------------------------------------------------------------------------
    */

    /**
     * @param Entity  $instance
     * @param String  $code
     * @return Array
     */
    public function getlistOfComments($instance, $code = null): array
    {
        $comments = [];

        foreach ($instance->comments as $comment) {
            if (
                !is_null($code)
                && $comment->findLangByKey('content', 'obj')->code != $code
            ) {
                continue;
            }

            $data = [
                'id'             => $comment->id,
                'user_id'        => $comment->user_id,
                'user_name'      => $comment->user_id ? $comment->user->name : null,
                'score'          => $comment->score,
                'options'        => $comment->options,
                'is_private'     => $comment->is_private,
                'is_highlighted' => $comment->is_highlighted,
                'is_enabled'     => $comment->is_enabled,
                'name'           => $comment->findLangByKey('name'),
                'subject'        => $comment->findLangByKey('subject'),
                'content'        => $comment->findLangByKey('content'),
                'created_at'     => $comment->created_at,
                'updated_at'     => $comment->updated_at,
                'comments'       => $this->getlistOfComments($comment)
            ];
            if (config('wk-morph-comment.onoff.morph-address')) {
                $address = ($comment->user_id) ? $comment->user->addresses('contact')->first()
                                               : $comment->addresses('contact')->first();
                $data['address'] = [
                    'phone' => $address->phone,
                    'email' => $address->email,
                    'name'  => $address->findLangByKey('name')
                ];
            }
            array_push($comments, $data);
        }

        return $comments;
    }
}
