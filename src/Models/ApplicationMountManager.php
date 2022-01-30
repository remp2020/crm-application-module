<?php

namespace Crm\ApplicationModule\Models;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;

class ApplicationMountManager extends MountManager
{
    private $groups = [];

    public function __construct(array $filesystems = [])
    {
        parent::__construct($filesystems);
    }

    public function mountFilesystem($prefix, FilesystemInterface $filesystem, ?string $group = null)
    {
        $this->groups[$group][] = $prefix;
        return parent::mountFilesystem($prefix, $filesystem); //
    }

    public function getFilePath($bucket, $filename): string
    {
        return $bucket . '://' . $filename;
    }

    public function getListContents($bucket, ?int $sort = SORT_DESC, ?string $sortColumn = 'timestamp')
    {
        $path = $bucket . '://';
        $files = $this->listContents($path);

        if (isset($sort)) {
            $files = $this->sortFiles($files, $sort, $sortColumn);
        }

        return $files;
    }

    public function getContentsForGroup($group, ?int $sort = SORT_DESC, ?string $sortColumn = 'timestamp')
    {
        $files = [];
        foreach ($this->groups[$group] as $bucket) {
            $files[] = $this->getListContents($bucket, null);
        }
        $files = array_merge(...$files);

        if (isset($sort)) {
            $files = $this->sortFiles($files, $sort, $sortColumn);
        }

        return $files;
    }

    public function getBucketsForGroup($group)
    {
        return $this->groups[$group] ?? [];
    }

    private function sortFiles($files, $sort, $column)
    {
        $columnData = [];
        foreach ($files as $key => $row) {
            $columnData[$key] = $row[$column];
        }
        array_multisort($columnData, $sort, $files);

        return $files;
    }
}
