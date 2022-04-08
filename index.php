<?php

    function ReadMapFile(string $file, bool $ZipResult = false, bool $MakeDirectory = false, bool $url = false)
    {
        $file = file_get_contents($file);
        if($MakeDirectory === true)
            $file = PathFilesClear($file);
        $FileData = json_decode($file, true);
        if(!is_array($FileData) || count($FileData) != 7)
            return false;
        else
            return MakeSourcesFiles($FileData, $ZipResult, $MakeDirectory);
    }

    function PathFilesClear(string $file)
    {
        $file = str_replace("webpack:///", "", $file);
//        $file = str_replace("../", "~.back.~/", $file);
        $file = str_replace("../", "", $file);
        return $file;
    }

    function MakeSourcesFiles(array $FileData, bool $ZipResult = false, bool $MakeDirectory = false)
    {
        $FileData = AddCopyRight($FileData);
        if($MakeDirectory === true)
            $FileData = MakeFolders($FileData);
        else
            $FileData = FileNameCleaner($FileData);
        foreach ($FileData['sources'] as $SourcesKey => $SourcesValue)
        {
            MakeFile($SourcesValue, $FileData['sourcesContent'][$SourcesKey]);
        }

        if($ZipResult)
            ZipResult();
        return true;
    }

    function FileNameCleaner(array $FileData)
    {
        foreach ($FileData['sources'] as $SourcesKey => $SourcesValue)
        {
            $Exploded = explode("/", $SourcesValue);
            $FileData['sources'][$SourcesKey] = $Exploded[count($Exploded) - 1];
        }

        return $FileData;
    }

    function AddCopyRight(array $FileData)
    {
        foreach ($FileData['sources'] as $SourcesKey => $SourcesValue)
        {
            $FileData['sourcesContent'][$SourcesKey] = "\n/*\n-----------------\nMamad H . A . M |\n-----------------\nURL File:" . $FileData['sources'][$SourcesKey] . "\n-----------------\n*/\n\n" . $FileData['sourcesContent'][$SourcesKey];
        }

        return $FileData;
    }

    function MakeFolders(array $FileData)
    {
        foreach ($FileData['sources'] as $SourcesKey => $SourcesValue)
        {
            $Exploded = explode("/", $SourcesValue);
            $DirTemp = "Source/";
            if(count($Exploded) > 1)
            {
                foreach ($Exploded as $ExplodedKey => $ExplodedValue)
                {
                    if(count($Exploded) - 1 > $ExplodedKey)
                    {
                        if (!is_dir($DirTemp . $ExplodedValue)) {
                            mkdir($DirTemp . $ExplodedValue);
                            $DirTemp .= "$ExplodedValue/";
                        }
                        else
                        {
                            $DirTemp .= "$ExplodedValue/";
                        }
                    }
                }
            }
        }

        return $FileData;
    }

    function MakeFile(string $FileName, string $Data)
    {
        if (file_exists("Source/$FileName"))
        {
            $FileName = "$FileName~!" . time() . "!~";
        }
        try {
//            $myfile = fopen("Source/$FileName", "w") or die("Unable to open file!");
            $myfile = fopen("Source/$FileName", "w");
            if($myfile)
            {
                $txt = "$Data";
                fwrite($myfile, $txt);
                fclose($myfile);
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

    }

    function ZipResult()
    {
        // Get real path for our folder
        $rootPath = realpath('Source');

// Initialize archive object
        $zip = new ZipArchive();
        $zip->open('file' . time() . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Initialize empty "delete list"
        $filesToDelete = array();

// Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);

                // Add current file to "delete list"
                // delete it later cause ZipArchive create archive only after calling close function and ZipArchive lock files until archive created)
                if ($file->getFilename() != 'important.txt')
                {
                    $filesToDelete[] = $filePath;
                }
            }
        }

// Zip archive will be created only after closing object
        $zip->close();

// Delete all files from "delete list"
        foreach ($filesToDelete as $file)
        {
            unlink($file);
        }
    }


    if(ReadMapFile("https://static-assets-prod.epicgames.com/account-management/ue/static/webpack/app.epic-account-management.2b750113ccf79b8da318.js.map", true, true, true))
        echo "Ok!";
    else
        echo "False";
?>
