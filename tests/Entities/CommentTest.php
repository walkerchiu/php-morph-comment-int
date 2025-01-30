<?php

namespace WalkerChiu\MorphComment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\MorphComment\Models\Entities\Comment;
use WalkerChiu\MorphComment\Models\Entities\CommentLang;

class CommentTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\MorphComment\MorphCommentServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Comment.
     *
     * For WalkerChiu\MorphComment\Models\Entities\MorphComment
     * 
     * @return void
     */
    public function testMorphComment()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-comment.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-comment.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-comment.soft_delete', 1);

        // Give
        $record_1 = factory(Comment::class)->create();
        $record_2 = factory(Comment::class)->create();
        $record_3 = factory(Comment::class)->create(['is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Comment::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $record_2->delete();
            $records = Comment::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Comment::withTrashed()
                    ->find(2)
                    ->restore();
            $record_2 = Comment::find(2);
            $records = Comment::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);

        // Return Lang class
            // When
            $class = $record_2->lang();
            // Then
            $this->assertEquals($class, CommentLang::class);

        // Scope query on enabled records
            // When
            $records = Comment::ofEnabled()
                               ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Comment::ofDisabled()
                               ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
