<?php

namespace Zimbop\GitHooks\Commands;

use Illuminate\Console\Command;

class SendPrePushNotify extends Command
{
    private const COMMITS_TO_SHOW = 5;

    protected $signature = 'git:send_push_notify {committer} {branch} {commits}';

    public function handle()
    {
        $branch = $this->argument('branch');
        $committer = $this->argument('committer');
        $commits = $this->argument('commits');

        $branchUrl = config('git_hooks.branches_url') . $branch;
        $commitsWithUrls = $this->addUrlsToCommits($commits, $branchUrl);

        $branch = "[$branch]($branchUrl)";

        $message = "$committer pushed to " . $branch . PHP_EOL . PHP_EOL . $commitsWithUrls;

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
            $this->info('Notifier class is not set in config');
        }

        return 0;
    }

    private function addUrlsToCommits(string $commits, string $branchUrl): string
    {
        $commitsArray = preg_split('~\R~', $commits);

        $commitsArray = array_map(function ($commit) {
            $commitHash = strtok($commit, ' ');
            $commitUrl = config('git_hooks.commits_url') . $commitHash;

            $commitParts = explode(' ', $commit);
            array_shift($commitParts);

            return "[$commitHash]($commitUrl) " . implode(' ', $commitParts);
        }, $commitsArray);

        if (count($commitsArray) > self::COMMITS_TO_SHOW) {
            $commitsArray = array_slice($commitsArray, 0, self::COMMITS_TO_SHOW);
            $commitsArray[] = PHP_EOL . " > " . self::COMMITS_TO_SHOW . " commits";
            $commitsArray[] = "[... See all]($branchUrl)";
        }

        return implode(PHP_EOL, $commitsArray);
    }
}
