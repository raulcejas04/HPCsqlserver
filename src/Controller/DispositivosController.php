<?php

namespace App\Controller;

use App\Entity\dispositivos;
use App\Form\DispositivosType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/dispositivos")
 */
class DispositivosController extends AbstractController
{
    /**
     * @Route("/", name="dispositivos_index", methods={"GET"})
     */
    public function index(): Response
    {
        $dispositivos = $this->getDoctrine()
            ->getRepository(dispositivos::class)
            ->findAll();

        return $this->render('dispositivos/index.html.twig', [
            'dispositivos' => $dispositivos,
        ]);
    }

    /**
     * @Route("/new", name="dispositivos_new", methods={"GET","POST"})
     */
    public function new(Request $request, PaginatorInterface $paginator, SessionInterface $session )
    {
        $dispositivo = new Dispositivos();
        $form = $this->createForm(DispositivosType::class, $dispositivo);
        $form->handleRequest($request);
        $pagination = $this->pagination( $paginator, $request, $session, 0 );

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($dispositivo);
            $entityManager->flush();

            return $this->redirectToRoute('dispositivos_new');
        }

        $dispositivos = $this->getDoctrine()
            ->getRepository(dispositivos::class)
            ->findAll();

        return $this->render('dispositivos/new.html.twig', [
            'dispositivo' => $dispositivo,
            'form' => $form->createView(),
            'dispositivos' =>$dispositivos, 'pagination' => $pagination, 'page'=>$pagination->getCurrentPageNumber()
        ]);

    }

    /**
     * @Route("/new/{page<\d*>}", name="dispositivos_new_page")
     */
    public function new_page( Request $request, PaginatorInterface $paginator, SessionInterface $session, int $page )
    {

        $pagination = $this->pagination( $paginator, $request, $session, $page );

        // Render the twig view
        /*return $this->render('usuario/index.twig',
                ['pagination' => $pagination]
            );*/
        return $this->render('dispositivos/new.html.twig',
            ['pagination' => $pagination, 'page'=>$page ]
        );


    }

    /**
     * @Route("/{idDispositivoHpc}", name="dispositivos_show", methods={"GET"})
     */
    public function show(Dispositivos $dispositivo): Response
    {
        return $this->render('dispositivos/show.html.twig', [
            'dispositivo' => $dispositivo,
        ]);
    }

    /**
     * @Route("/{idDispositivoHpc}/edit", name="dispositivos_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Dispositivos $dispositivo): Response
    {
        $form = $this->createForm(DispositivosType::class, $dispositivo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('dispositivos_index');
        }

        return $this->render('dispositivos/edit.html.twig', [
            'dispositivo' => $dispositivo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{idDispositivoHpc}", name="dispositivos_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Dispositivos $dispositivo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dispositivo->getIdDispositivoHpc(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($dispositivo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dispositivos_new');
    }
    function pagination( PaginatorInterface $paginator, Request $request, SessionInterface $session, int $page)
    {
        $em = $this->getDoctrine()->getManager();
        $dispositivosRepository = $em->getRepository(dispositivos::class);
        $allDispositivosQuery = $dispositivosRepository->createQueryBuilder('p')
            ->orderBy('p.descripcion')
            ->getQuery();
        if( $page >0 )
            $indice=$page;
        else
            $indice=$request->query->getInt('page', 1);
        $pagination = $paginator->paginate(
        // Doctrine Query, not results
            $allDispositivosQuery,
            // Define the page parameter
            $indice,
            // Items per page
            10
        );
        $pagination->setTemplate('dispositivos/my_pagination.html.twig');

        return $pagination;
    }
}
