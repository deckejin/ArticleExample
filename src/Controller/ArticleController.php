<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Entity\Article;
use App\Form\ArticleType as FormArticleType;

class ArticleController extends FOSRestController
{
    /**
     * @Rest\Get("/", name="article")
     */
    public function index()
    {
        return $this->render('article/index.html.twig', [
            'articles' => '',
        ]);
    }

    /**
     * Create new article
     * @Rest\Post("/articles/new", name="new_article")
     */
    public function newArticle(Request $request)
    {

        $article = new Article();
        $form = $this->createForm(FormArticleType::class, $article);

        // to have better control over what is submitted, you can use submit
        // $data = json_decode($request->getContent(), true);
        // $form->submit($data);

        $form->handleRequest($request); //recommended way of processing Symfony forms is to use the handleRequest()

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->render('article/article.html.twig', [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'author' => $article->getAuthor(),
                'contents' => $article->getContents(),
                'likes' => $article->getLikes(),
                'read_count' => $article->getReadCount(),
            ]);
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * Get article by id
     * @Rest\Get("/articles/get/{id}", name="get_article")
     */
    public function getArticle($id)
    {

        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->findOneBy(['id' => $id]);
        if (empty($article)) {
            return new Response("Article not found");
        }

        $this->updateReads($article->getId());
        return $this->render('article/article.html.twig', [
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'author' => $article->getAuthor(),
            'contents' => $article->getContents(),
            'likes' => $article->getLikes(),
            'read_count' => $article->getReadCount(),
        ]);
    }


    /**
     * List all articles
     * @Rest\Get("/articles", name="all_articles")
     */
    public function getAllArticles()
    {

        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findall();

        return $this->render('article/show_all.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * Update article by id
     * @Rest\Put("/articles/update/{id}", name="update_article")
     */
    public function updateArticle($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Article::class);

        $article = $repository->findOneBy(['id' => $id]);

        $form = $this->createForm(FormArticleType::class, $article);
        $data = json_decode($request->getContent(), true);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($article)) {
                return new Response("Article not found");
            }
            if (!empty($data["name"])) {
                $article->setTitle($data["title"]);
            }
            if (!empty($data["author"])) {
                $article->setAuthor($data["author"]);
            }
            if (!empty($data["content"])) {
                $article->setContents($data["contents"]);
            }
            if (!empty($data["likes"])) {
                $article->setLikes($data["likes"]);
            }
            if (!empty($data["read_count"])) {
                $article->setReadCount($data["read_count"]);
            }

            $em->flush();

            return $this->render('article/article.html.twig', [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'author' => $article->getAuthor(),
                'contents' => $article->getContents(),
                'likes' => $article->getLikes(),
                'read_count' => $article->getReadCount(),
            ]);
        }

        return $this->render('article/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete article with id
     * @Rest\Delete("/articles/delete/{id}", name="delete_article")
     */
    public function deleteArticle($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Article::class);

        $article = $repository->findOneBy(['id' => $id]);
        if (empty($article)) {
            return new Response("Article not found");
        }

        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute("all_articles");
    }

    /**
     * Increase likes on the article by id
     * @Rest\Put("/articles/update_likes/{id}", name="update_article_likes")
     */
    public function updateArticleLikes($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Article::class);

        $article = $repository->findOneBy(['id' => $id]);
        $likes = $article->getLikes();
        $article->setLikes($likes + 1);

        $em->flush();

        return $this->redirectToRoute("all_articles");
    }



    public function updateReads($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Article::class);

        $article = $repository->findOneBy(['id' => $id]);
        $reads = $article->getReadCount();
        $article->setReadCount($reads + 1);

        $em->flush();
    }
}
