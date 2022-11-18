<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpClient\NativeHttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;




#[Route('/article')]
class ArticleController extends AbstractController
{
    private static $entityManager;
    private  $manager;
    public function __construct(EntityManagerInterface $em)
    {
        $this->client = new NativeHttpClient();
        ArticleController::$entityManager = $em;
        $this->manager = $em;
    }

    // public static function getEntityManager()
    // {
    //     return ArticleController::$entityManager;
    // }

    /**
     * Require ROLE_ADMIN for all the actions of this controller
     *
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, PaginatorInterface $paginator,): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $allArticles = [];
        $newsHighlihgt = null;
        try {
            $allArticles = $articleRepository->findAll(['id' => 'desc']);
        } catch (\Doctrine\ORM\NoResultException $e) {
        }

        try {
            $newsHighlihgt = $articleRepository->findOneBy([], ['id' => 'DESC']);
        } catch (\Doctrine\ORM\NoResultException $e) {
        }

        // Paginate the results of the query
        $articlesPaginated = $paginator->paginate($allArticles, $request->query->getInt('page', 1), 10);

        return $this->render('article/news_listing.html.twig', [
            'articles' => (count($articlesPaginated) > 0 ? $articlesPaginated : []),
            'newsHighlihgt' => ($newsHighlihgt != null ? $newsHighlihgt : null),
            'maxPages'      => count($allArticles),
        ]);
    }



    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArticleRepository $articleRepository): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->add($article, true);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->add($article, true);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $articleRepository->remove($article, true);
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }






    //LIVE
    public function addNews()
    {
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
                foreach ($content['results'] as $value) {
                    try {
                        foreach ($value['multimedia'] as $value2) break;

                        $article = new Article();
                        $article->setTitle($value['title']);
                        $article->setDescription($value['abstract']);
                        $article->setPicture($value2['url']);
                        $article->setStatus(1);
                        $article->setCreatedAt(new \DateTime('now'));
                        $article->setUpdatedAt(new \DateTime('now'));

                        $this->manager->persist($article);
                        $this->manager->flush();
                    } catch (\Throwable $err) {
                        dd($err);
                    }
                }
            }
            $message = "API for news download is working fine. News download complete";
            dd($message);
        } catch (\Throwable $err) {
            $message = "Unable to reach API for news download. Server not responding!";
            dd($err);
        }
    }
}