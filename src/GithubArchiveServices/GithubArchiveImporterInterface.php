<?php

namespace App\GithubArchiveServices;

interface GithubArchiveImporterInterface
{
    /**
     * Import an event to the database
     *
     * @param array $event
     */
    public function import(array &$event): void;
}