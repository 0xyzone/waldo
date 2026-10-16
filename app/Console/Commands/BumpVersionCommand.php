<?php

namespace App\Console\Commands;

use App\Services\ChangelogService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('version:bump {type=micro : The release type: major, minor, or micro (patch)} {--title= : Title of the release} {--highlight= : Highlights or summary of changes}')]
#[Description('Calculate and bump the application semantic version (major, minor, or micro/patch)')]
class BumpVersionCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = strtolower($this->argument('type'));
        if (! in_array($type, ['major', 'minor', 'micro', 'patch'], true)) {
            $this->error("Invalid version type '{$type}'. Must be one of: major, minor, micro, patch.");

            return Command::FAILURE;
        }

        $current = ChangelogService::getCurrentVersion();
        $next = ChangelogService::getNextVersion($type, $current);

        $this->info("Current Version: v{$current}");
        $this->info("Next Version ({$type}): v{$next}");

        $title = $this->option('title') ?: "Update v{$next}";
        $highlight = $this->option('highlight') ?: "System release v{$next}.";

        $this->table(
            ['Property', 'Value'],
            [
                ['Type', strtoupper($type)],
                ['Current Version', $current],
                ['Target Version', $next],
                ['Title', $title],
                ['Highlight', $highlight],
                ['Compact Display', ChangelogService::getCompactVersion($next)],
            ]
        );

        return Command::SUCCESS;
    }
}
