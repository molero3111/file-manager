<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
class SendUploadProgress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $fileId;
    protected $chunkIndex;
    protected $totalChunks;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $fileId, $chunkIndex, $totalChunks)
    {
        $this->fileId = $fileId;
        $this->userId = $userId;
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
        // Send HTTP POST to Socket.IO server
        $client = new \GuzzleHttp\Client();
        $client->post(config('app.socket_io_url'), [
            'json' => [
                'event' => 'upload-progress',
                'userId' => $this->userId,
                'data' => [
                    'fileId' => $this->fileId,
                    'progress' => ($this->chunkIndex + 1) / $this->totalChunks * 100
                ],
            ]
        ]);

    }
}