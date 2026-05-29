<?php

return [
    'diskList' => ['home'],
    'leftDisk' => 'home',
    'rightDisk' => null,
    'leftPath' => '',
    'rightPath' => null,
    'windowsConfig' => 2,
    'cache' => null,
    'fileDownload' => true,
    'middleware' => ['web', 'admin.auth'],
    'aclStrategy' => 'blacklist',
    'acl' => false,
    'aclHideFromFM' => true,
    'hiddenFiles' => true,
    'aclRulesFile' => '',
    'aclRepository' => Alexusmai\LaravelFileManager\Services\ACLService\ConfigACLRepository::class,
    'maxUploadFileSize' => null,
    'allowFileTypes' => [],
    'maxFilenameLength' => 40,
    'slugifyNames' => true,
    'links' => 'skip',
];
