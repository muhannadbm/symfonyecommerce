<?php

namespace App\Controller\Userpanel;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/userpanel")
 */

class UserpanelController extends AbstractController
{
    /**
     * @Route("/", name="userpanel")
     */
    public function index()
    {
        return $this->redirectToRoute('userpanel_show');
    }

    /**
     * @Route("/edit", name="userpanel_edit", methods="GET|POST")
     */
    public function edit(Request $request): Response
    {


        $usersession=$this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($usersession->getid());

        if($request->isMethod('POST')) {
             $submittedtoken=$request->request->get('token');
             if($this->isCsrfTokenValid('user-form', $submittedtoken)){
                 $user->setName($request->request->get("name"));

                 $user->setAddress($request->request->get("address"));
                 $user->setCity($request->request->get("city"));
                 $user->setPhone($request->request->get("phone"));
                 $this->getDoctrine()->getManager()->flush();
                 $this->addFlash('success','User Info Has Been Updated Successfully');
                 return $this->redirectToRoute('userpanel_show');


}


        }


        return $this->render('userpanel/_form.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/show", name="userpanel_show",methods="GET")
     */
    public function show()
    {
        return $this->render('userpanel/show.html.twig');
    }


}
