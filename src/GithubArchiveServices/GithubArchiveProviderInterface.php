<?php

namespace App\GithubArchiveServices;

use DateTimeInterface;

interface GithubArchiveProviderInterface
{
    /**
     * Responsibility to provide the archive for the given date.
     *
     * @param DateTimeInterface $date
     * @param int $hour
     * @return array<string> Json entries as string
     */
    public function getArchive(DateTimeInterface $date, int $hour): array;
}