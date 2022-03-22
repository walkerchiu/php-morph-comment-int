<?php

namespace WalkerChiu\MorphComment\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;
use WalkerChiu\MorphComment\Models\Repositories\CommentRepositoryTrait;

class CommentRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;
    use CommentRepositoryTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.morph-comment.comment'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @param Bool    $is_enabled
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(string $code, array $data, $is_enabled = null, $auto_packing = false)
    {
        $instance = $this->instance;
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $data = array_map('trim', $data);
        $repository = $instance->with(['langs' => function ($query) use ($code) {
                                    $query->ofCurrent()
                                          ->ofCode($code);
                                }])
                                ->whereHas('langs', function ($query) use ($code) {
                                    return $query->ofCurrent()
                                                 ->ofCode($code);
                                })
                                ->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['morph_type']), function ($query) use ($data) {
                                                return $query->where('morph_type', $data['morph_type']);
                                            })
                                            ->unless(empty($data['morph_id']), function ($query) use ($data) {
                                                return $query->where('morph_id', $data['morph_id']);
                                            })
                                            ->unless(empty($data['user_id']), function ($query) use ($data) {
                                                return $query->where('user_id', $data['user_id']);
                                            })
                                            ->unless(empty($data['score']), function ($query) use ($data) {
                                                return $query->where('score', $data['score']);
                                            })
                                            ->unless(
                                                !isset($data['is_private'])
                                                || is_null($data['is_private'])
                                            , function ($query) use ($data) {
                                                return $query->where('is_private', $data['is_private']);
                                            })
                                            ->when(isset($data['is_highlighted']), function ($query) use ($data) {
                                                return $query->where('is_highlighted', $data['is_highlighted']);
                                            })
                                            ->unless(empty($data['edit_at']), function ($query) use ($data) {
                                                return $query->where('edit_at', $data['edit_at']);
                                            })
                                            ->unless(empty($data['subject']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'subject')
                                                          ->where('value', 'LIKE', "%".$data['subject']."%");
                                                });
                                            })
                                            ->unless(empty($data['content']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'content')
                                                          ->where('value', 'LIKE', "%".$data['content']."%");
                                                });
                                            })
                                            ->unless(
                                                empty($data['orderBy'])
                                                && empty($data['orderType'])
                                            , function ($query) use ($data) {
                                                return $query->orderBy($data['orderBy'], $data['orderType']);
                                            }, function ($query) {
                                                return $query->orderBy('created_at', 'DESC');
                                            });
                                }, function ($query) {
                                    return $query->orderBy('created_at', 'ASC');
                                });

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-morph-comment.output_format'), config('wk-morph-comment.pagination.pageName'), config('wk-morph-comment.pagination.perPage'));

            if (in_array(config('wk-morph-comment.output_format'), ['array', 'array_pagination'])) {
                switch (config('wk-morph-comment.output_format')) {
                    case "array":
                        $entities = $factory->toCollection($repository);
                        // no break
                    case "array_pagination":
                        $entities = $factory->toCollectionWithPagination($repository);
                        // no break
                    default:
                        $output = [];
                        foreach ($entities as $instance) {
                            if (config('wk-morph-comment.onoff.morph-address')) {
                                $address = ($instance->user_id)
                                    ? $instance->user->addresses('contact')->first()
                                    : $instance->addresses('contact')->first();
            
                                $data = $instance->toArray();
                                array_push($output,
                                    array_merge($data, [
                                        'subject' => $instance->findLangByKey('subject'),
                                        'content' => $instance->findLangByKey('content'),
                                        'address' => [
                                            'phone' => $address->phone,
                                            'email' => $address->email,
                                            'name'  => $address->findLang($code, 'name')
                                        ]
                                    ])
                                );
                            } else {
                                $data = $instance->toArray();
                                array_push($output,
                                    array_merge($data, [
                                        'subject' => $instance->findLangByKey('subject'),
                                        'content' => $instance->findLangByKey('content')
                                    ])
                                );
                            }
                        }
                }
                return $output;
            } else {
                return $factory->output($repository);
            }
        }

        return $repository;
    }

    /**
     * @param Comment       $instance
     * @param Array|String  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
        $data = [
            'id' => $instance ? $instance->id : '',
            'basic'    => [],
            'comments' => []
        ];

        if (empty($instance))
            return $data;

        $this->setEntity($instance);

        if (is_string($code)) {
            $data['basic'] = [
                  'morph_type'     => $instance->morph_type,
                  'morph_id'       => $instance->morph_id,
                  'user_id'        => $instance->user_id,
                  'score'          => $instance->score,
                  'options'        => $instance->options,
                  'is_private'     => $instance->is_private,
                  'is_highlighted' => $instance->is_highlighted,
                  'is_enabled'     => $instance->is_enabled,
                  'subject'        => $instance->findLang($code, 'subject'),
                  'content'        => $instance->findLang($code, 'content'),
                  'edit_at'        => $instance->edit_at,
                  'created_at'     => $instance->created_at,
                  'updated_at'     => $instance->updated_at
            ];
            if (config('wk-morph-comment.onoff.morph-address')) {
                $address = ($instance->user_id) ? $instance->user->addresses('contact')->first()
                                              : $instance->addresses('contact')->first();
                $data['basic'] = array_merge($data['basic'], [
                    'phone' => $address->phone,
                    'email' => $address->email,
                    'name'  => $address->findLang($code, 'name')
                ]);
            }

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                    'morph_type'     => $instance->morph_type,
                    'morph_id'       => $instance->morph_id,
                    'user_id'        => $instance->user_id,
                    'score'          => $instance->score,
                    'options'        => $instance->options,
                    'is_private'     => $instance->is_private,
                    'is_highlighted' => $instance->is_highlighted,
                    'is_enabled'     => $instance->is_enabled,
                    'subject'        => $instance->findLang($language, 'subject'),
                    'content'        => $instance->findLang($language, 'content'),
                    'edit_at'        => $instance->edit_at,
                    'created_at'     => $instance->created_at,
                    'updated_at'     => $instance->updated_at
                ];
                if (config('wk-morph-comment.onoff.morph-address')) {
                    $address = ($instance->user_id) ? $instance->user->addresses('contact')->first()
                                                  : $instance->addresses('contact')->first();
                    $data['basic'][$language] = array_merge($data['basic'], [
                        'phone' => $address->phone,
                        'email' => $address->email,
                        'name'  => $address->findLang($language, 'name')
                    ]);
                }
            }
        }

        $data['comments'] = $this->getlistOfComments($instance);

        return $data;
    }
}
