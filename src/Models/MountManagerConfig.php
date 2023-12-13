<?php

namespace Crm\ApplicationModule\Models;

use League\Flysystem\FilesystemOperator;

class MountManagerConfig
{
    private array $filesystems = [];

    public function mountFilesystem(string $key, FilesystemOperator $filesystemOperator, string $group = null)
    {
        $this->filesystems[$key] = [
            'filesystem' => $filesystemOperator,
            'group' => $group,
        ];
    }

    public function getFilesystems(): array
    {
        return $this->filesystems;
    }
}
