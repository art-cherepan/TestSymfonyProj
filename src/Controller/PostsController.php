<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\Mailer;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{
    private $postRepository;
    private $entityManager;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/posts', name: 'blog_posts')]
    public function posts()
    {
        $posts = $this->postRepository->findAll();

        return $this->render('posts/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/posts/new', name: 'new_blog_post')]
    public function addPost(Request $request, Slugify $slugify)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug($slugify->slugify($post->getTitle()));
            $post->setCreatedAt(new \DateTime());

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $this->redirectToRoute('blog_posts');
        }
        return $this->render('posts/post.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/posts/search', name: 'blog_search')]
    public function search(Request $request)
    {
        $query = $request->query->get('q');
        $posts = $this->postRepository->searchByQuery($query);

        return $this->render('blog/query_post.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/posts/{slug}', name: 'blog_show')]
    public function show(Post $post)
    {
        return $this->render('posts/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/posts/{slug}/edit', name: 'blog_post_edit')]
    public function edit(Post $post, Request $request, Slugify $slugify)
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug($slugify->slugify($post->getTitle()));
            $this->entityManager->flush();

            return $this->redirectToRoute('blog_posts');
        }

        return $this->render('posts/post.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/posts/{slug}/delete', name: 'blog_post_delete')]
    public function delete(Post $post)
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->redirectToRoute('blog_posts');
    }
}
