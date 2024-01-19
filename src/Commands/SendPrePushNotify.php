<?php

namespace Zimbop\GitHooks\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPrePushNotify extends Command
{
    const COMMITS_TO_SHOW = 5;

    protected $signature = 'git:send_push_notify {committer} {branch} {commits} {forced=0}';

    public function handle()
    {
        $branch = $this->argument('branch');
        $committer = $this->argument('committer');
        $commits = $this->argument('commits');
        $forced = $this->argument('forced');

        $branchUrl = config('git_hooks.branches_url') . $branch;
        $commitsWithUrls = $this->addUrlsToCommits($commits, $branchUrl);

        $branchWithProject = config('git_hooks.project_name') . "/" . $branch;

        $branch = "[$branchWithProject]($branchUrl)";

        $forcedText = $forced == "1" ? '❗ forcibly' : '';

        $message = "$committer $forcedText pushed to " . $branch . PHP_EOL . PHP_EOL . $commitsWithUrls;

        if (empty($message)) {
            return 0;
        }

        try {
            $notifierClassName = config('git_hooks.notifier');

            app($notifierClassName)->notify(
                urlencode($message),
                $notifierClassName::CHAT_DEV
            );
        } catch (\Exception $exception) {
            Log::error('[Pre Push Git Hook] ' . $exception->getMessage());
        }

        return 0;
    }

    private function addUrlsToCommits(string $commits, string $branchUrl): string
    {
        if (empty($commits)) {
            return '';
        }

        $commitsArray = preg_split('~\R~', $commits);

        $commitsArray = array_map(function ($commit) {
            $commitHash = strtok($commit, ' ');
            $commitUrl = config('git_hooks.commits_url') . $commitHash;

            $commitParts = explode(' ', $commit);
            array_shift($commitParts);

            $commitMessage = implode(' ', $commitParts);

            // escape reserved Telegram characters
            $commitMessage = str_replace(
                ['_', '[', ']', '*', '`'],
                ['\\_', '\\[', '\\]', '\\*', '\\`'],
                $commitMessage
            );

            return "[$commitHash]($commitUrl) $commitMessage";
        }, $commitsArray);

        if (count($commitsArray) > self::COMMITS_TO_SHOW) {
            $commitsArray = array_slice($commitsArray, 0, self::COMMITS_TO_SHOW);
            $commitsArray[] = PHP_EOL . " > " . self::COMMITS_TO_SHOW . " commits";
            $commitsArray[] = "[... See all]($branchUrl)";
        }

        return implode(PHP_EOL, $commitsArray);
    }
}
