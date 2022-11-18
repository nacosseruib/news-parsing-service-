<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\DownloadRepository;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;



#[Route('/download')]
class DownloadNewsController extends AbstractController
{


    public function __construct()
    {
    }


    #[Route('/news', name: 'app_article_download', methods: ['GET'])]
    public function download(ArticleController $articleController): Response
    {
        try {
            return $articleController->addNews();
        } catch (\Throwable $err) {
            return new JsonResponse(
                ['message' => "Unable to download !"],
                Response::HTTP_BAD_GATEWAY
            );
        }
    }

    public function testDownload(DownloadRepository $downloadRepository): Response
    {
        try {
            $data = $downloadRepository->testNews();
            return new JsonResponse(
                ['message' => $data],
                Response::HTTP_BAD_GATEWAY
            );
        } catch (\Throwable $err) {
            return new JsonResponse(
                ['message' => "Unable to download !"],
                Response::HTTP_BAD_GATEWAY
            );
        }
    }
}