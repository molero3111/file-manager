<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FileUploadProgress implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $fileId;
  public $progress;

  public function __construct($fileId, $progress)
  {
    $this->fileId = $fileId;
    $this->progress = $progress;
  }

  public function broadcastOn()
  {
      return ['file-upload'];
  }

  public function broadcastAs()
  {
      return 'upload-progress';
  }
}