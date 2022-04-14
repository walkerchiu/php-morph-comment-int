<?php

namespace WalkerChiu\MorphComment\Console\Commands;

use WalkerChiu\Core\Console\Commands\Cleaner;

class MorphCommentCleaner extends Cleaner
{
    /**
     * The name and signature of the console command.
     *
     * @var String
     */
    protected $signature = 'command:MorphCommentCleaner';

    /**
     * The console command description.
     *
     * @var String
     */
    protected $description = 'Truncate tables';

    /**
     * Execute the console command.
     *
     * @return Mixed
     */
    public function handle()
    {
        parent::clean('morph-comment');
    }
}
