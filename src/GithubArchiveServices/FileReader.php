<?php

namespace App\GithubArchiveServices;

class FileReader
{
    public function read(string $path)
    {
        return file_get_contents($path);
    }
}