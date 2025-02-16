<?php

namespace App\Jobs;

use App\Events\FileUploadProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
class SendUploadProgress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileId;
    protected $chunkIndex;
    protected $totalChunks;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileId, $chunkIndex, $totalChunks)
    {
        $this->fileId = $fileId;
        $this->chunkIndex = $chunkIndex;
        $this->totalChunks = $totalChunks;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        event(new FileUploadProgress(
            $this->fileId,
            ($this->chunkIndex + 1) / $this->totalChunks * 100
        ));
    }
}