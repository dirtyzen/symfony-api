<?php


namespace App\Controller;


use App\Entity\BlogPost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{

    private const POSTS = [

        [
            'id' => 1,
            'slug' => 'blog-bir',
            'title' => 'Birinci Blog Başlığı'
        ],

        [
            'id' => 2,
            'slug' => 'blog-iki',
            'title' => 'İkinci Blog Başlığı'
        ],

        [
            'id' => 3,
            'slug' => 'blog-uc',
            'title' => 'Üçüncü Blog Başlığı'
        ]

    ];

    /**
     * @Route("/{page}", name="blog_list", defaults={"page":null}, requirements={"page":"\d+"}, methods={"GET"})
     */
    public function list($page = 1, Request $request)
    {
        $limit = (int) $request->get('limit', 10);

        $repository = $this->getDoctrine()->getRepository(BlogPost::class);
        $items = $repository->findAll();

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'data' => array_map(function (BlogPost $item){
                return $this->generateUrl('blog_by_slug', ['slug' => $item->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            }, $items)
        ]);
    }

    /**
     * @Route("/post/{id}", name="blog_by_id", requirements={"id":"\d+"}, methods={"GET"})
     * @ParamConverter("post", class="App:BlogPost")
     */
    public function post($post)
    {
        return $this->json($post);
    }

    /**
     * @Route("/post/{slug}", name="blog_by_slug", methods={"GET"})
     * @ParamConverter("post", class="App:BlogPost", options={"mapping" : {"slug" : "slug"}})
     */
    public function postBySlug($post)
    {
        //$this->getDoctrine()->getRepository(BlogPost::class)->findOneBy(['slug' => $slug])
        return $this->json($post);
    }

    /**
     * @Route("/add", name="blog_add", methods={"POST"})
     */
    public function add(Request $request)
    {

        /** @var Serializer $serializer **/
        $serializer = $this->get('serializer');

        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();

        return $this->json($blogPost);
    }

    /**
     * @Route("/post/{id}", name="blog_delete", requirements={"id" : "\d+"}, methods={"DELETE"})
     */
    public function delete(BlogPost $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}