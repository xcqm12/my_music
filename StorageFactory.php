<?php
require_once 'GitHubStorage.php';
require_once 'GiteeStorage.php';

class StorageFactory {
    public static function create() {
        if (STORAGE_DRIVER == 'github') {
            return new GitHubStorage();
        } elseif (STORAGE_DRIVER == 'gitee') {
            return new GiteeStorage();
        } else {
            throw new Exception('Unsupported storage driver');
        }
    }
}