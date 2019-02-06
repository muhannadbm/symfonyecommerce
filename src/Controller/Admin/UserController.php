<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\User;
use App\Form\UserType;
use http\Env\Request;
use http\Env\Response;
use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/user", name="admin_user")
     */
    public function index()
    {

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('admin/user/frontbase.html.twig', [
            'users' => $users,
        ]);
    }


    /**
     * @Route("/admin/user/new", name="admin_user_new",methods="GET|POST")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('admin_user');

        }


        return $this->render('admin/user/create_form.html.twig'

            , [
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * @Route("/admin/user/edit/{id}", name="admin_user_edit", methods="GET|POST")
     */
    public function edit(\Symfony\Component\HttpFoundation\Request $request, User $users): \Symfony\Component\HttpFoundation\Response


    {
        $form = $this->createForm(UserType::class, $users);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_user');

        }

        return $this->render('admin/user/edit_form.html.twig', [
            'user' => $users,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="admin_user_delete")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param User $users
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(\Symfony\Component\HttpFoundation\Request $request, User $users): \Symfony\Component\HttpFoundation\Response
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($users);
        $em->flush();
        return $this->redirectToRoute('admin_user');
    }


}

