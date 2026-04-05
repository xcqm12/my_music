<?php
// StorageFactory.php
require_once 'GitHubStorage.php';
require_once 'GiteeStorage.php';
require_once 'OneDriveStorage.php';

class StorageFactory {
    public static function create() {
        switch(STORAGE_DRIVER) {
            case 'github': return new GitHubStorage();
            case 'gitee': return new GiteeStorage();
            case 'onedrive': return new OneDriveStorage();
            default: throw new Exception('Unsupported storage driver');
        }
    }
}