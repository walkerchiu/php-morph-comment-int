<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\MorphComment\Models\Entities\Comment;
use WalkerChiu\MorphComment\Models\Entities\CommentLang;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'score' => $faker->randomDigitNotNull
    ];
});

$factory->define(CommentLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['subject', 'content']),
        'value' => $faker->sentence
    ];
});
