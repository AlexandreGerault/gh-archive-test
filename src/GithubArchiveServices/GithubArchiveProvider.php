<?php

namespace App\GithubArchiveServices;

use DateTimeInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubArchiveProvider implements GithubArchiveProviderInterface
{
    private HttpClientInterface $http;
    private Filesystem $filesystem;
    private FileReader $reader;

    private const GH_ARCHIVE_BASE_URL = 'https://data.gharchive.org/';
    private const FILE_DIR = 'uploads/gh-archives/';
    private const FILE_DATE_FORMAT = 'Y-m-d'; // Year - Month - Day

    /**
     * ArchiveProvider constructor.
     *
     * @param HttpClientInterface $http
     * @param Filesystem $filesystem
     */
    public function __construct(HttpClientInterface $http, Filesystem $filesystem, FileReader $reader)
    {
        $this->http = $http;
        $this->filesystem = $filesystem;
        $this->reader = $reader;
    }

    /**
     * Download the gz file from a given date or load the file if it has been already downloaded.
     *
     * @param DateTimeInterface $date The day we want to get the data from
     * @param int $hour
     * @return array<string> all json string entries
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getArchive(DateTimeInterface $date, int $hour): array
    {
        $filename = $this->getFilenameFrom($date, $hour);

        if (! $this->filesystem->exists(self::FILE_DIR . $filename)) {
            $this->download($date, $hour);
        }

        return explode("\n", $this->load($filename));
    }

    /**
     * @param DateTimeInterface $date
     * @param int $hour
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function download(DateTimeInterface $date, int $hour): void
    {
        $filename = $this->getFilenameFrom($date, $hour);

        // Download the archive file for a given day and hour
        $response = $this->http->request(
            'GET',
            self::GH_ARCHIVE_BASE_URL . $filename
        );

        $data = $response->getContent();

        // Save the file content to a new file
        $this->filesystem->dumpFile(self::FILE_DIR . $filename, $data);
    }

    /**
     * Load a gzip archive and decode it. Returns a decoded string
     *
     * @param string $filename
     * @return string
     */
    private function load(string $filename): string
    {
        $payload = gzdecode($this->reader->read(self::FILE_DIR . $filename));
        if ($payload) {
            return $payload;
        }
        // Else throw error
        throw new RuntimeException("Error loading file ${filename}...");
    }

    /**
     * Helper function to generate filename.
     * Format: YYYY-MM-DD-HH.json.gz
     *
     * @param DateTimeInterface $date
     * @param int $hour
     * @return string
     */
    private function getFilenameFrom(DateTimeInterface $date, int $hour)
    {
        return $date->format(self::FILE_DATE_FORMAT) . "-" . $hour . ".json.gz";
    }
}