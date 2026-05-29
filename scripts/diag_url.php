<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$doc       = \App\Models\Document::whereNotNull('azure_path')->first();
$account   = config('filesystems.disks.azure.account');
$key       = config('filesystems.disks.azure.key');
$container = config('filesystems.disks.azure.container');
$path      = $doc->azure_path;

$helper   = new \MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper($account, $key);
$expiry   = new \DateTime('+1 hour');
$sasToken = $helper->generateBlobServiceSharedAccessSignatureToken(
    'b',
    $container . '/' . $path,
    'r',
    $expiry
);

$encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));
$sasUrl = 'https://' . $account . '.blob.core.windows.net/' . $container . '/' . $encodedPath . '?' . $sasToken;

echo "SAS URL:\n" . $sasUrl . "\n";
