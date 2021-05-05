<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\usuarioType;
use App\Form\usuarioType2;

use App\Repository\UsuarioDispositivoRepository;
//use App\Repository\UsuarioRepository;
//use App\Repository\DispositivoRepository;

use App\Entity\dispositivos;
use App\Entity\usuario;
use App\Entity\usuarioDispositivo;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
* @Route("/usuario")
*/
class UsuarioController extends AbstractController
{

     /**
     * @Route("/", name="usuario")
     */
    public function index(): Response
    {

//        HOLA
//        ESTOY EN MAIN

        $usuarios = $this->getDoctrine()
            ->getRepository(usuario::class)
            ->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController', 'usuarios' => $usuarios
        ]);
    }

    /**
     * @Route("/prueba", name="prueba")
     */
    public function prueba( Request $request)
    {
        return $this->render('usuario/prueba.twig');
    }

    /**
     * @Route("/list", name="usuario_list")
     */
    public function list( Request $request, PaginatorInterface $paginator, SessionInterface $session )
    {

        $pagination = $this->pagination( $paginator, $request, $session, 0 );
            
        // Render the twig view
        /*return $this->render('usuario/index.twig', 
                ['pagination' => $pagination]
            );*/
        return $this->render('usuario/index.twig', 
            ['pagination' => $pagination, 'page'=>$pagination->getCurrentPageNumber() ]
        );


    }

    /**
     * @Route("/list/{page<\d*>}", name="usuario_list_page")
     */
    public function list_page( Request $request, PaginatorInterface $paginator, SessionInterface $session, int $page )
    {

        $pagination = $this->pagination( $paginator, $request, $session, $page );
            
        // Render the twig view
        /*return $this->render('usuario/index.twig', 
                ['pagination' => $pagination]
            );*/
        return $this->render('usuario/index.twig', 
            ['pagination' => $pagination, 'page'=>$page ]
        );


    }




    /**
     * @Route("/{id}/show/{page}", name="usuario_show")
     */
    public function show( usuario $usuario, Request $request, EntityManagerInterface $em, 
        PaginatorInterface $paginator, SessionInterface $session, int $page  ): Response
    {
        //dd($usuario);
        if (null === $usuario ) {
//            throw $this->createNotFoundException('No existe el Usuario para el id '.$id);
            throw $this->createNotFoundException('No existe el Usuario para el id '.$request->get('id'));
        }

        $originalDisp = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($usuario->getUsuarioDispositivo() as $usuarioDispositivo) {
            $originalDisp->add($usuarioDispositivo->getDispositivos());
        }


        $form = $this->createForm( UsuarioType::class, $usuario );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($form->getData());
            $em->flush();
            // redirect back to some edit page
            return $this->redirectToRoute('usuario_edit', ['id' => $usuario->getIdUserHpc()]);

        }

        $pagination = $this->pagination( $paginator, $request, $session, $page );

        return $this->render('usuario/index.twig', [
            'usuarioForm' => $form->createView(),
            'usuario' => $usuario,
            'pagination' => $pagination,
            'page' => $page
        ]);
    }


     /**
     * @Route("/{id}/edit/{page}", name="usuario_edit")
     */
    public function edit( usuario $usuario, Request $request, 
        EntityManagerInterface $em, SessionInterface $session, int $page  ): Response
    {
        //dd($usuario);
        if (null === $usuario ) {
//            throw $this->createNotFoundException('No existe el Usuario para el id '.$id);
            throw $this->createNotFoundException('No existe el Usuario para el id '.$request->get('id'));
        }

        $originalDisp = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($usuario->getUsuarioDispositivo() as $usuarioDispositivo) {
            $originalDisp->add($usuarioDispositivo->getDispositivos());
        }


        $form = $this->createForm(UsuarioType::class, $usuario );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($form->getData());
            $em->flush();
            // redirect back to some edit page
            return $this->redirectToRoute('usuario_edit', ['id' => $usuario->getIdUserHpc()]);

        }


        return $this->render('usuario/edit.twig', [
            'usuario' => $usuario,
            'usuarioForm' => $form->createView(),
            'page'=>$page
        ]);
    }
    /**
     * @Route("/new", name="usuario_new", methods={"GET","POST"})
     */
    public function new(Request $request, PaginatorInterface $paginator, SessionInterface $session): Response
    {
        $pagination = $this->pagination( $paginator, $request, $session, 0 );
        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType2::class, $usuario);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('usuario_new');
        }
        $usuarios = $this->getDoctrine()
            ->getRepository(Usuario::class)
            ->findAll();

//<<<<<<< HEAD
        return $this->render('usuario/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'usuarios' => $usuarios,
            'pagination' => $pagination,
            'page'=>$pagination->getCurrentPageNumber()
        ]);
    }
    /**
     * @Route("/new/{page<\d*>}", name="usuario_new_page")
     */
    public function new_page( Request $request, PaginatorInterface $paginator, SessionInterface $session, int $page )
    {

        $pagination = $this->pagination($paginator, $request, $session, $page);

        return $this->render('usuario/new.html.twig',
            ['pagination' => $pagination, 'page' => $page]
        );
    }
    /**
     * @Route("/{idUserHpc}", name="usuarios_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Usuario $usuario): Response
    {
        if ($this->isCsrfTokenValid('delete'.$usuario->getIdUserHpc(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('usuario_new');
    }
//=======
    function pagination( PaginatorInterface $paginator, Request $request, SessionInterface $session, int $page)
    {
            //$session = new Session();
            //$session->start();

            //$page=$paginator->getCurrentPageNumber();
            //echo "page ".$page."<br>";
            
            // Retrieve the entity manager of Doctrine
            $em = $this->getDoctrine()->getManager();
            
            // Get some repository of data, in our case we have an Appointments entity
            $usuariosRepository = $em->getRepository(usuario::class);
                    
            // Find all the data on the Appointments table, filter your query as you need
            //->where('p.activo != :activo')
            //->setParameter('activo', '1')

            $allUsuariosQuery = $usuariosRepository->createQueryBuilder('p')
                ->orderBy('p.nombre')
                ->getQuery();

            //echo "request ".$request->query->getInt('page', 1)."<br>";
            //echo "page1 ".$page."<br>";
            if( $page >0 )
                $indice=$page;
            else
                $indice=$request->query->getInt('page', 1);

            // Paginate the results of the query
            $pagination = $paginator->paginate(
                // Doctrine Query, not results
                $allUsuariosQuery,
                // Define the page parameter
                $indice,
                // Items per page
                10
            );

            $pagination->setTemplate('usuario/my_pagination.html.twig');

        return $pagination;

    }

//>>>>>>> origin
}
