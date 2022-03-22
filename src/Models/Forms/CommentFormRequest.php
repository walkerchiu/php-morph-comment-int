<?php

namespace WalkerChiu\MorphComment\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class CommentFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (int) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'morph_type'     => trans('php-morph-comment::system.morph_type'),
            'morph_id'       => trans('php-morph-comment::system.morph_id'),
            'user_id'        => trans('php-morph-comment::system.user_id'),
            'score'          => trans('php-morph-comment::system.score'),
            'options'        => trans('php-morph-comment::system.options'),
            'addresses'      => trans('php-morph-comment::system.addresses'),
            'is_private'     => trans('php-morph-comment::system.is_private'),
            'is_highlighted' => trans('php-morph-comment::system.is_highlighted'),
            'is_enabled'     => trans('php-morph-comment::system.is_enabled'),

            'subject'        => trans('php-morph-comment::system.subject'),
            'content'        => trans('php-morph-comment::system.content'),

            'phone'          => trans('php-morph-comment::system.phone'),
            'email'          => trans('php-morph-comment::system.email'),
            'name'           => trans('php-morph-comment::system.name')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'morph_type'     => 'required_with|string',
            'morph_id'       => 'required_with|integer|min:1',
            'user_id'        => ['required_without:name','integer','min:1','exists:'.config('wk-core.table.user').',id'],
            'score'          => ['nullable','numeric','min:'.config('wk-morph-comment.score.min'),'max:'.config('wk-morph-comment.score.max')],
            'options'        => 'nullable|json',
            'addresses'      => 'nullable|json',
            'is_private'     => 'boolean',
            'is_highlighted' => 'boolean',
            'is_enabled'     => 'boolean',

            'subject'        => 'nullable|string|max:255',
            'content'        => 'required|string',

            'phone'          => '',
            'email'          => 'nullable|email',
            'name'           => 'required_without:user_id|string|max:255'
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.morph-comment.comments').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'              => trans('php-core::validation.required'),
            'id.integer'               => trans('php-core::validation.integer'),
            'id.min'                   => trans('php-core::validation.min'),
            'id.exists'                => trans('php-core::validation.exists'),
            'morph_type.required_with' => trans('php-core::validation.required_with'),
            'morph_type.string'        => trans('php-core::validation.string'),
            'morph_id.required_with'   => trans('php-core::validation.required_with'),
            'morph_id.integer'         => trans('php-core::validation.integer'),
            'morph_id.min'             => trans('php-core::validation.min'),
            'user_id.required_without' => trans('php-core::validation.required_without'),
            'user_id.integer'          => trans('php-core::validation.integer'),
            'user_id.min'              => trans('php-core::validation.min'),
            'user_id.exists'           => trans('php-core::validation.exists'),
            'score.numeric'            => trans('php-core::validation.numeric'),
            'score.min'                => trans('php-core::validation.min'),
            'score.max'                => trans('php-core::validation.max'),
            'options.json'             => trans('php-core::validation.json'),
            'addresses.json'           => trans('php-core::validation.json'),
            'is_private.boolean'       => trans('php-core::validation.boolean'),
            'is_highlighted.boolean'   => trans('php-core::validation.boolean'),
            'is_enabled.boolean'       => trans('php-core::validation.boolean'),

            'subject.string'           => trans('php-core::validation.string'),
            'subject.max'              => trans('php-core::validation.max'),
            'content.required'         => trans('php-core::validation.required'),
            'content.string'           => trans('php-core::validation.string'),

            'email.email'              => trans('php-core::validation.email'),
            'name.required_without'    => trans('php-core::validation.required_without'),
            'name.string'              => trans('php-core::validation.string'),
            'name.max'                 => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (
                isset($data['morph_type'])
                && isset($data['morph_id'])
            ) {
                if (
                    config('wk-morph-comment.onoff.site')
                    && !empty(config('wk-core.class.site.site'))
                    && $data['morph_type'] == config('wk-core.class.site.site')
                ) {
                    $result = DB::table(config('wk-core.table.site.sites'))
                                ->where('id', $data['morph_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('morph_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-morph-comment.onoff.group')
                    && !empty(config('wk-core.class.group.group'))
                    && $data['morph_type'] == config('wk-core.class.group.group')
                ) {
                    $result = DB::table(config('wk-core.table.group.groups'))
                                ->where('id', $data['morph_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('morph_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-morph-comment.onoff.mall-order')
                    && !empty(config('wk-core.class.mall-order.order'))
                    && $data['morph_type'] == config('wk-core.class.mall-order.order')
                ) {
                    $result = DB::table(config('wk-core.table.mall-order.orders'))
                                ->where('id', $data['morph_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('morph_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-morph-comment.onoff.mall-shelf')
                    && !empty(config('wk-core.class.mall-shelf.stock'))
                    && $data['morph_type'] == config('wk-core.class.mall-shelf.stock')
                ) {
                    $result = DB::table(config('wk-core.table.mall-shelf.stocks'))
                                ->where('id', $data['morph_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('morph_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-morph-comment.onoff.morph-image')
                    && !empty(config('wk-core.class.morph-image.image'))
                    && $data['morph_type'] == config('wk-core.class.morph-image.image')
                ) {
                    $result = DB::table(config('wk-core.table.morph-image.images'))
                                ->where('id', $data['morph_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('morph_id', trans('php-core::validation.exists'));
                } elseif ( $data['morph_type'] == config('wk-core.class.morph-comment.comment') ) {
                    $result = DB::table(config('wk-core.table.morph-comment.comments'))
                                ->where('id', $data['morph_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('morph_id', trans('php-core::validation.exists'));
                }
            }

            if (isset($data['score'])) {
                switch (config('wk-morph-comment.score.type')) {
                    case "both":
                        if (
                            !is_integer($data['score'])
                            && !is_float($data['score'])
                        )
                            $validator->errors()->add('score', trans('php-core::validation.numeric'));
                        break;
                    case "integer":
                        if (!is_integer($data['score']))
                            $validator->errors()->add('score', trans('php-core::validation.integer'));
                        break;
                    case "float":
                        if (!is_float($data['score']))
                            $validator->errors()->add('score', trans('php-core::validation.float'));
                        break;
                }
            }
        });
    }
}
