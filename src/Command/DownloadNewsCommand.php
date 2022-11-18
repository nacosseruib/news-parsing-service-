<?php

namespace App\Command;

use App\Controller\ArticleController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\HttpClient\NativeHttpClient;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;






class DownloadNewsCommand extends Command
{
    protected $client;
    protected $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
        $this->client = new NativeHttpClient();
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('news')
            ->setDescription('News Downloaded')
            ->setHelp('The news are to be downloaded from and API');
        //->addArgument('mode', InputArgument::REQUIRED, '1-[Live] or 0-[Test] mode');
    }


    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '',
            '=====News Download Starts========',
            '',
        ]);
        //$output->writeln($input->getArgument('mode') ? 'Live mode' : 'Test mode');

        //$mode = $input->getArgument('mode');
        $output->writeln('checking server...');
        $output->writeln('downloading news...');
        $output->writeln('');
        //
        try {
            $response = $this->client->request(
                'GET',
                'https://api.nytimes.com/svc/topstories/v2/arts.json?api-key=y79LTHPuxDBptqmVosjQACNKjY6GBEpc',
                [
                    'extra' => ['trace_content' => false],
                ]
            );
        } catch (\Throwable $err) {
        }
        $content = json_decode($response->getContent(), true);
        try {
            $article = new Article();
            if ($content['num_results'] > 0) {
                try {
                    foreach ($content['results'] as $value) {
                        foreach ($value['multimedia'] as $value2) break;
                        $article = new Article();
                        $article->setTitle($value['title']);
                        $article->setDescription($value['abstract']);
                        $article->setPicture($value2['url']);
                        $article->setStatus(1);
                        $article->setCreatedAt(new \DateTime('now'));
                        $article->setUpdatedAt(new \DateTime('now'));
                        //$this->entityManager = ArticleController::getEntityManager();
                        $this->entityManager->persist($article);
                        $this->entityManager->flush();
                    }
                    $message = "API for news download is working fine. News download complete. Check main page to view download";
                    $output->writeln($message);
                } catch (\Throwable $err) {
                    $output->writeln($err);
                }
            }
        } catch (\Throwable $err) {
            $message = "Unable to reach API for news download. Server not responding!";
            $output->writeln($err);
        }
        //
        $output->writeln([
            '',
            '=====ends========',
            '',
        ]);

        return 0;
    }
}