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
     * @return array<object> Json entries as objects
     */
    public function getArchive(DateTimeInterface $date, int $hour): array;
}