<?php

namespace App\Tests\Units\GithubArchiveServices;

use App\GithubArchiveServices\FileReader;
use App\GithubArchiveServices\GithubArchiveProvider;
use DateTime;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubArchiveProviderTest extends TestCase
{
    public function testItCanProvideArchiveIfFileHasOneJsonEntry()
    {
        // Test initialization
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $filesystemMock = $this->createMock(Filesystem::class);
        $readerMock = $this->createMock(FileReader::class);
        $readerMock->method('read')->willReturn(gzencode("{}"));
        $githubArchiveProvider = new GithubArchiveProvider($httpClientMock, $filesystemMock, $readerMock);

        // Test actions
        $archivePayload = $githubArchiveProvider->getArchive(
            DateTime::createFromFormat('Y-m-d', '2018-06-28'),
            15
        );

        // Test assertions
        $this->assertCount(1, $archivePayload);
    }

    public function testItFailsLoadingEmptyFile()
    {
        // Test exceptions
        $this->expectException(RuntimeException::class);

        // Test initialization
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $filesystemMock = $this->createMock(Filesystem::class);
        $readerMock = $this->createMock(FileReader::class);
        $readerMock->method('read')->willReturn(gzencode(""));
        $githubArchiveProvider = new GithubArchiveProvider($httpClientMock, $filesystemMock, $readerMock);

        // Test actions
        $githubArchiveProvider->getArchive(
            DateTime::createFromFormat('Y-m-d', '2018-06-28'),
            15
        );
    }
}