<?php

namespace App\Console\Commands;

use App\Actions\DeleteSubmissionFiles;
use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Illuminate\Console\Command;

class PruneSubmissions extends Command
{
    /**
     * @var string
     */
    protected $signature = 'zytecs:prune-submissions {--dry-run : List what would be deleted without deleting}';

    /**
     * @var string
     */
    protected $description = 'Delete closed, completed or declined submissions (and files) older than the retention period';

    public function handle(DeleteSubmissionFiles $deleteFiles): int
    {
        $days = (int) config('zytecs.retention.closed_submission_days');
        $finalStatuses = array_map(fn (SubmissionStatus $status) => $status->value, array_filter(SubmissionStatus::cases(), fn (SubmissionStatus $status) => $status->isFinal()));

        $query = Submission::with('files')
            ->whereIn('status', $finalStatuses)
            ->whereNotNull('closed_at')
            ->where('closed_at', '<', now()->subDays($days));

        $count = 0;

        $query->chunkById(100, function ($submissions) use ($deleteFiles, &$count) {
            foreach ($submissions as $submission) {
                $count++;

                if ($this->option('dry-run')) {
                    $this->line($submission->reference);

                    continue;
                }

                $deleteFiles->handle($submission);
                $submission->delete();
            }
        });

        $this->info(($this->option('dry-run') ? 'Would delete ' : 'Deleted ')."{$count} submission(s) closed more than {$days} days ago.");

        return self::SUCCESS;
    }
}
