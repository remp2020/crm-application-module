<?php

namespace Crm\ApplicationModule\Models;

use League\Flysystem\FilesystemException;
use League\Flysystem\MountManager;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class ApplicationMountManager extends MountManager
{
    public const BUCKET_DELIMITER = '://';

    private array $groups = [];

    private FinfoMimeTypeDetector $mimeTypeDetector;

    public function __construct(protected MountManagerConfig $mountManagerConfig)
    {
        $filesystems = [];
        foreach ($mountManagerConfig->getFilesystems() as $key => $filesystem) {
            $this->groups[$filesystem['group'] ?? null][] = $key;
            $filesystems[$key] = $filesystem['filesystem'];
        }

        $this->mimeTypeDetector = new FinfoMimeTypeDetector();

        parent::__construct($filesystems);
    }

    public function getFilePath($bucket, $filename): string
    {
        return $bucket . self::BUCKET_DELIMITER . $filename;
    }

    public static function getFileName(string $path): string
    {
        $filePath = explode(self::BUCKET_DELIMITER, $path);

        return end($filePath);
    }

    /**
     * @throws FilesystemException
     */
    public function getListContents($bucket, ?int $sort = SORT_DESC, ?string $sortColumn = 'lastModified'): array
    {
        $path = $bucket . self::BUCKET_DELIMITER;
        $files = $this->listContents($path)->toArray();

        if (isset($sort)) {
            $files = $this->sortFiles($files, $sort, $sortColumn);
        }

        return $files;
    }

    /**
     * @throws FilesystemException
     */
    public function getContentsForGroup($group, ?int $sort = SORT_DESC, ?string $sortColumn = 'lastModified'): array
    {
        $files = [];

        if (!array_key_exists($group, $this->groups)) {
            return $files;
        }

        foreach ($this->groups[$group] as $bucket) {
            $files[] = $this->getListContents($bucket, null);
        }
        $files = array_merge(...$files);

        if (isset($sort)) {
            $files = $this->sortFiles($files, $sort, $sortColumn);
        }

        return $files;
    }

    public function getBucketsForGroup($group): array
    {
        return $this->groups[$group] ?? [];
    }

    private function sortFiles($files, $sort, $column): array
    {
        $columnData = [];
        foreach ($files as $key => $row) {
            $columnData[$key] = $row[$column];
        }
        array_multisort($columnData, $sort, $files);

        return $files;
    }
}
