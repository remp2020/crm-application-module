<?php

namespace Crm\ApplicationModule\Models;

use League\Flysystem\MountManager;

class ApplicationMountManager extends MountManager
{
    public function getFilePath($bucket, $filename): string
    {
        return $bucket . '://' . $filename;
    }

    public function getListContents($bucket)
    {
        $path = $bucket . '://';
        $files = $this->listContents($path);

        $timestamp = [];
        foreach ($files as $key => $row) {
            $timestamp[$key] = $row['timestamp'];
        }
        array_multisort($timestamp, SORT_DESC, $files);

        return $files;
    }
}
