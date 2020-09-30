<?php

namespace App\Command;

use App\GithubArchiveServices\GithubArchiveImporterInterface;
use App\GithubArchiveServices\GithubArchiveProviderInterface;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportGithubArchiveCommand extends Command
{
    protected static $defaultName = "app:gh-import";
    private DateTimeInterface $date;
    private const READABLE_DATE_FORMAT = 'l, F jS Y'; // English format: Day, Month Nth
    private const READABLE_TIME_FORMAT = '%hh %imin %ss'; // English format: Day, Month Nth
    private const INPUT_DATE_FORMAT = 'j/m/Y'; // French input format

    private GithubArchiveProviderInterface $archiveProvider;
    private GithubArchiveImporterInterface $archiveImporter;
    private EntityManagerInterface $em;

    /**
     * ImportGhArchiveCommand constructor.
     * @param GithubArchiveProviderInterface $archiveProvider
     * @param GithubArchiveImporterInterface $archiveImporter
     * @param EntityManagerInterface $em
     */
    public function __construct(
        GithubArchiveProviderInterface $archiveProvider,
        GithubArchiveImporterInterface $archiveImporter,
        EntityManagerInterface $em
    ) {
        $this->archiveProvider = $archiveProvider;
        $this->archiveImporter = $archiveImporter;
        $this->em = $em;

        parent::__construct();
    }


    protected function configure()
    {
        parent::configure();

        $this
            ->setName("Import GH Archive")
            ->setDescription("Import a day of GH Archive into the database")
            ->setHelp("app:gh-import [day]")
            ->addArgument(
                "day",
                InputOption::VALUE_REQUIRED,
                "From what day to you want to import GH archive data?"
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->date = DateTime::createFromFormat(
            self::INPUT_DATE_FORMAT,
            $input->getArgument('day')
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = new DateTime();
        $io = new SymfonyStyle($input, $output);
        $io->title('Import GitHub archive from ' . $this->date->format(self::READABLE_DATE_FORMAT));

        $progressBar = new ProgressBar($output, 24);
        $progressBar->start();
        for ($i = 0; $i < 24; $i++) {
            $events = $this->archiveProvider->getArchive($this->date, $i);
            $this->archiveImporter->import($events);

            $progressBar->advance();
        }
        $endTime = new DateTime();
        $elapsed = $endTime->diff($startTime, true);
        $progressBar->finish();
        $io->newLine();
        $io->writeln("End of imports ! Execution time: " . $elapsed->format(self::READABLE_TIME_FORMAT));

        return Command::SUCCESS;
    }
}