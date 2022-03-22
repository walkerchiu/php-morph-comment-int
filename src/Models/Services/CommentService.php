<?php

namespace WalkerChiu\MorphComment\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class CommentService
{
    use CheckExistTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.morph-comment.commentRepository'));
    }

    /**
     * @param Int  $comment_id
     * @param Int  $owner_user_id
     * @param Int  $viewer_user_id
     * @param Int  $author_user_id
     * @return Int
     */
    public function countComments(int $comment_id, int $owner_user_id, $viewer_user_id = null, $author_user_id = null): int
    {
        $data = [
            'morph_type' => config('wk-core.class.morph-comment.comment'),
            'morph_id'   => $comment_id
        ];

        if ($owner_user_id != $viewer_user_id) {
            $data['is_enabled'] = 1;
            if (
                empty($viewer_user_id)
                || $viewer_user_id != $author_user_id
            ) {
                $data['is_private'] = 0;
            }

            return App::make(config('wk-core.class.morph-comment.commentRepository'))
                        ->whereByArray($data)
                        ->when($viewer_user_id, function ($query) use ($data, $viewer_user_id) {
                            return $query->orWhere( function ($query) use ($data, $viewer_user_id) {
                                return $query->where('morph_type', $data['morph_type'])
                                             ->where('morph_id', $data['morph_id'])
                                             ->where('is_enabled', $data['is_enabled'])
                                             ->where('user_id', $viewer_user_id);
                            });
                        })
                        ->count();
        }

        return App::make(config('wk-core.class.morph-comment.commentRepository'))
                    ->whereByArray($data)
                    ->count();
    }
}
