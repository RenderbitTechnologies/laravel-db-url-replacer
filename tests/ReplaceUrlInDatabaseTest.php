<?php

namespace Renderbit\DbUrlReplacer\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Renderbit\DbUrlReplacer\DbUrlReplacerServiceProvider;

class ReplaceUrlInDatabaseTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [DbUrlReplacerServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sample_articles', function ($table) {
            $table->id();
            $table->text('content')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('sample_posts', function ($table) {
            $table->id();
            $table->text('body')->nullable();
            $table->text('tags')->nullable();
        });

        DB::table('sample_articles')->insert([
            ['content' => 'Visit http://example.com', 'notes' => 'http://example.com in notes'],
            ['content' => 'No link here', 'notes' => null],
        ]);

        DB::table('sample_posts')->insert([
            ['body' => 'Post at http://example.com/path', 'tags' => 'http://example.com/tag'],
            ['body' => 'Clean post', 'tags' => null],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('sample_articles');
        Schema::dropIfExists('sample_posts');
        parent::tearDown();
    }

    public function test_invalid_urls_throw_errors()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'not-a-url',
            'newUrl' => 'https://new.example.com',
        ])->expectsOutput('Invalid old URL: not-a-url')
          ->assertExitCode(1);
    }

    public function test_invalid_table_name_fails()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com',
            '--tables' => 'invalid_table'
        ])->expectsOutput('Invalid table(s): invalid_table')
          ->assertExitCode(1);
    }

    public function test_invalid_column_name_fails()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com',
            '--columns' => 'notfound'
        ])->expectsOutput('None of the specified columns were found in any table.')
          ->assertExitCode(1);
    }

    public function test_dry_run_detects_matches_without_updating()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com',
            '--dry-run' => true
        ])->expectsOutput('Dry run complete. No changes were made.')
          ->assertExitCode(0);

        $this->assertDatabaseHas('sample_articles', [
            'content' => 'Visit http://example.com'
        ]);
    }

    public function test_actual_replacement_changes_content()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com'
        ])->expectsOutput('Replacement complete.')
          ->assertExitCode(0);

        $this->assertDatabaseHas('sample_articles', [
            'content' => 'Visit https://new.example.com'
        ]);
    }

    public function test_filters_by_table_and_column()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com',
            '--tables' => 'sample_articles',
            '--columns' => 'notes'
        ])->assertExitCode(0);

        $this->assertDatabaseHas('sample_articles', [
            'notes' => 'https://new.example.com in notes'
        ]);
    }

    public function test_invalid_new_url_fails()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'not-a-url',
        ])->expectsOutput('Invalid new URL: not-a-url')
          ->assertExitCode(1);
    }

    public function test_no_matches_found_outputs_message()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://nonexistent.com',
            'newUrl' => 'https://new.example.com',
        ])->expectsOutput('No matches found.')
          ->assertExitCode(0);
    }

    public function test_replaces_across_multiple_tables()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('sample_articles', [
            'content' => 'Visit https://new.example.com'
        ]);
        $this->assertDatabaseHas('sample_posts', [
            'body' => 'Post at https://new.example.com/path'
        ]);
    }

    public function test_filters_by_column_only_across_tables()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com',
            '--columns' => 'content'
        ])->assertExitCode(0);

        // content column should be replaced
        $this->assertDatabaseHas('sample_articles', [
            'content' => 'Visit https://new.example.com'
        ]);
        // notes column should NOT be replaced
        $this->assertDatabaseHas('sample_articles', [
            'notes' => 'http://example.com in notes'
        ]);
    }

    public function test_tables_with_mix_of_valid_and_invalid_fails()
    {
        $this->artisan('db:replace-url', [
            'oldUrl' => 'http://example.com',
            'newUrl' => 'https://new.example.com',
            '--tables' => 'sample_articles,nonexistent_table'
        ])->expectsOutput('Invalid table(s): nonexistent_table')
          ->assertExitCode(1);
    }
}
