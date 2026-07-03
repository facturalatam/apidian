<?php
/**
 * Script para migrar storage_path() a StorageService en todos los controladores.
 * Ejecutar desde la raíz del proyecto: php scripts/migrate_storage_to_s3.php
 */

// $basePath = dirname(__DIR__);

// // Files to process
// $files = [
//     'app/Http/Controllers/Api/CreditNoteController.php',
//     'app/Http/Controllers/Api/DebitNoteController.php',
//     'app/Http/Controllers/Api/BatchController.php',
//     'app/Http/Controllers/Api/ConfigurationController.php',
//     'app/Http/Controllers/Api/AddCostumersDocumentsXML.php',
//     'app/Http/Controllers/Api/InvoiceAIUController.php',
//     'app/Http/Controllers/AcceptRejectDocumentController.php',
//     'app/Http/Controllers/DocumentController.php',
//     'app/Http/Controllers/SellerLoginController.php',
// ];

// // Also check for other controllers with storage_path
// $additionalFiles = [];
// $controllerDir = $basePath . '/app/Http/Controllers';
// $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerDir));
// foreach ($iterator as $file) {
//     if ($file->isFile() && $file->getExtension() === 'php') {
//         $relativePath = str_replace($basePath . '/', '', str_replace('\\', '/', $file->getPathname()));
//         $content = file_get_contents($file->getPathname());
//         if (strpos($content, 'storage_path') !== false && !in_array($relativePath, $files)) {
//             // Skip already-migrated files
//             if ($relativePath !== 'app/Http/Controllers/Api/InvoiceController.php' 
//                 && $relativePath !== 'app/Http/Controllers/Api/DownloadController.php') {
//                 $additionalFiles[] = $relativePath;
//             }
//         }
//     }
// }

// $files = array_unique(array_merge($files, $additionalFiles));

// $totalReplacements = 0;

// foreach ($files as $relFile) {
//     $fullPath = $basePath . '/' . $relFile;
//     if (!file_exists($fullPath)) {
//         echo "SKIP (not found): {$relFile}\n";
//         continue;
//     }
    
//     $content = file_get_contents($fullPath);
//     $original = $content;
//     $fileReplacements = 0;

//     // 1. Add StorageService import if not present
//     if (strpos($content, 'use App\\Services\\StorageService;') === false) {
//         // Add after 'use Storage;' or after namespace/last use statement
//         if (preg_match('/^use Storage;$/m', $content)) {
//             $content = preg_replace('/^use Storage;$/m', "use Storage;\nuse App\\Services\\StorageService;", $content, 1);
//             $fileReplacements++;
//         } elseif (preg_match('/^(use [^;]+;)$/m', $content, $m)) {
//             // Add after last use statement
//             $lastUsePos = strrpos($content, $m[1]);
//             $content = substr($content, 0, $lastUsePos + strlen($m[1])) . "\nuse App\\Services\\StorageService;" . substr($content, $lastUsePos + strlen($m[1]));
//             $fileReplacements++;
//         }
//     }

//     // 2. Replace directory creation patterns
//     // Pattern: if (!is_dir(storage_path("app/public/..."))) { mkdir(...); }
//     $content = preg_replace_callback(
//         '/if\s*\(\s*!is_dir\s*\(\s*storage_path\s*\(\s*"app\/public\/([^"]+)"\s*\)\s*\)\s*\)\s*\{\s*\n\s*mkdir\s*\(\s*storage_path\s*\(\s*"app\/public\/\1"\s*\)\s*\)\s*;\s*\n\s*\}/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::ensureDirectory("public/' . $matches[1] . '");';
//         },
//         $content
//     );

//     // 3. Replace file_get_contents(storage_path("app/xml/...")) patterns (signed XML reads)
//     $content = preg_replace_callback(
//         '/file_get_contents\s*\(\s*storage_path\s*\(\s*"app\/xml\/([^"]+)"\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::get("xml/' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 4. Replace base64_encode(file_get_contents(storage_path("app/..."))) patterns
//     $content = preg_replace_callback(
//         '/base64_encode\s*\(\s*file_get_contents\s*\(\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::getBase64Auto("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 5. Replace GuardarEn = storage_path("app/...") patterns
//     $content = preg_replace_callback(
//         '/->GuardarEn\s*=\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return '->GuardarEn = StorageService::tempPath("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 6. Replace signToSend(storage_path("app/...")) patterns
//     $content = preg_replace_callback(
//         '/->signToSend\s*\(\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return '->signToSend(StorageService::tempPath("' . $matches[1] . '"))';
//         },
//         $content
//     );

//     // 7. Replace getResponseToObject(storage_path("app/...")) patterns
//     $content = preg_replace_callback(
//         '/->getResponseToObject\s*\(\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return '->getResponseToObject(StorageService::tempPath("' . $matches[1] . '"))';
//         },
//         $content
//     );

//     // 8. Replace $this->zipBase64(..., storage_path("app/...")) - 4th param
//     $content = preg_replace_callback(
//         '/zipBase64\s*\(\s*(\$\w+)\s*,\s*(\$\w+)\s*,\s*(\$\w+->sign\([^)]+\))\s*,\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'zipBase64(' . $matches[1] . ', ' . $matches[2] . ', ' . $matches[3] . ', StorageService::tempPath("' . $matches[4] . '")';
//         },
//         $content
//     );

//     // 9. Replace fopen(storage_path("app/..."), ...) patterns
//     $content = preg_replace_callback(
//         '/fopen\s*\(\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)\s*,/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'fopen(StorageService::tempPath("' . $matches[1] . '"),';
//         },
//         $content
//     );

//     // 10. Replace file_exists(storage_path("app/...")) patterns
//     $content = preg_replace_callback(
//         '/file_exists\s*\(\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::exists("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 11. Replace bare file_get_contents(storage_path("app/...")) (not inside base64_encode)
//     $content = preg_replace_callback(
//         '/(?<!base64_encode\()file_get_contents\s*\(\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::get("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 12. Replace Storage::download("...") with StorageService::download("...")
//     // Only for patterns that use the public/ path
//     $content = preg_replace_callback(
//         '/Storage::download\s*\(\s*"(public\/[^"]+)"\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::download("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 13. Replace base64_encode(file_get_contents(storage_path('app/...'))) with single quotes
//     $content = preg_replace_callback(
//         "/base64_encode\s*\(\s*file_get_contents\s*\(\s*storage_path\s*\(\s*'app\/([^']+)'\s*\)\s*\)\s*\)/",
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::getBase64Auto("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 14. Replace file_get_contents(storage_path('app/...')) with single quotes  
//     $content = preg_replace_callback(
//         "/file_get_contents\s*\(\s*storage_path\s*\(\s*'app\/([^']+)'\s*\)\s*\)/",
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::get("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 15. Replace file_exists(storage_path('app/...')) with single quotes
//     $content = preg_replace_callback(
//         "/file_exists\s*\(\s*storage_path\s*\(\s*'app\/([^']+)'\s*\)\s*\)/",
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::exists("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 16. Replace response()->download(storage_path("app/..."))
//     $content = preg_replace_callback(
//         '/response\(\)\s*->\s*download\s*\(\s*storage_path\s*\(\s*"app\/([^"]+)"\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             $fileReplacements++;
//             return 'StorageService::download("' . $matches[1] . '")';
//         },
//         $content
//     );

//     // 17. Replace response()->download(storage_path("...")); for non-app/ paths
//     $content = preg_replace_callback(
//         '/response\(\)\s*->\s*download\s*\(\s*storage_path\s*\(\s*"([^"]+)"\s*\)\s*\)/',
//         function($matches) use (&$fileReplacements) {
//             // Only for paths starting with 'app/'
//             if (strpos($matches[1], 'app/') === 0) {
//                 $fileReplacements++;
//                 return 'StorageService::download("' . substr($matches[1], 4) . '")';
//             }
//             return $matches[0]; // Don't modify non-app paths
//         },
//         $content
//     );

//     // Write file if changes were made
//     if ($content !== $original) {
//         file_put_contents($fullPath, $content);
//         $totalReplacements += $fileReplacements;
//         echo "UPDATED ({$fileReplacements} replacements): {$relFile}\n";
//     } else {
//         echo "NO CHANGES: {$relFile}\n";
//     }
// }

// echo "\nTotal replacements: {$totalReplacements}\n";
// echo "Migration complete.\n";
