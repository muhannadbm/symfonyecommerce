<?php

namespace App\Controller;

use App\Entity\Shopcart;
use App\Form\ShopcartType;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shopcart")
 */
class ShopcartController extends AbstractController
{
    /**
     * @Route("/", name="shopcart_index", methods="GET")
     */
    public function index(ShopcartRepository $shopcartRepository,Request $request): Response
    {
        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        $cats[0] = '';


        $user= $this->getUSER();

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT p.title,p.sellprice,p.image, s.* FROM shopcart s,product p WHERE s.productid = p.id and userid= :userid";
        $statement =$em->getConnection()->prepare($sql);
        $statement ->bindValue('userid', $user->getid());
        $statement->execute();
        $shopcart=$statement->fetchAll();



        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);





        return $this->render('shopcart/index.html.twig', [
            'cats' =>$cats,
            'shopcarts' => $shopcart,
            'shopcount' => $shopcount,]);
    }







    /**
     * @Route("/new", name="shopcart_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $shopcart = new Shopcart();
        $form = $this->createForm(ShopcartType::class, $shopcart);
        $form->handleRequest($request);
        echo $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('add-item',$submittedToken)) {
        if ($form->isSubmitted() ) {
            
            $em = $this->getDoctrine()->getManager();
            $user=$this->getUser();
            $shopcart->setUserid($user->getid());
            $em->persist($shopcart);
            $em->flush();

            $this->addFlash('success','تم إضافة المنتج للسلة بنجاح');
            return $this->redirectToRoute('home');
        } }

        return $this->render('shopcart/new.html.twig', [
            'cats' =>$cats,
            'shopcart' => $shopcart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="shopcart_show", methods="GET")
     */
    public function show(Shopcart $shopcart, Request $request): Response
    {
        return $this->render('shopcart/show.html.twig', [ 'cats' =>$cats,'shopcart' => $shopcart]);
    }

    /**
     * @Route("/{id}/edit", name="shopcart_edit", methods="GET|POST")
     */
    public function edit(Request $request, Shopcart $shopcart): Response
    {
        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        $cats[0] = '';

        $form = $this->createForm(ShopcartType::class, $shopcart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('shopcart_index', ['id' => $shopcart->getId()]);
        }

        return $this->render('shopcart/edit.html.twig', [
            'cats' =>$cats,
            'shopcart' => $shopcart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="shopcart_delete", methods="DELETE")
     */
    public function delete(Request $request, Shopcart $shopcart): Response
    {
        if ($this->isCsrfTokenValid('delete'.$shopcart->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($shopcart);
            $em->flush();
        }
        $this->addFlash('success','تم إزالة المنتج من السلة بنجاح');
        return $this->redirectToRoute('shopcart_index');
    }




    public function categorytree($parent=0,$user_tree_array='',Request $request) {

        if(!is_array($user_tree_array))
            $user_tree_array=array();
    
        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM category WHERE parentid= ".$parent;
        $statement =$em->getConnection()->prepare($sql);
        $statement->execute();
        $result=$statement->fetchAll();
        $locale = $request->getLocale();
        if(count($result) > 0){
            $user_tree_array[]="<ul class='dropdown-menu multi'> 	<div class='row'>
            <div class='col-sm-3'>
                <ul class='multi-column-dropdown'>";
            foreach($result as $row) {
    
                if ($row["parentid"] == 0) {
                    $user_tree_array[] = "<li class='dropdown '><a  class='dropdown-toggle  hyper' data-toggle='dropdown' ><span>" . $row['title'] . "<b class='caret'></b></span> </a>";
                    $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);
    
    
                }
                else {
    
                    $user_tree_array[] = "<li><a  href='/category/" . $row['id'] . " '><i class='fa fa-angle-right' aria-hidden='true'></i>" . $row['title'] . " </a>";
                    $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);
                } }
                $user_tree_array[] = "</li></ul>  </div> </ul>";
            }
            return $user_tree_array;
        }
}
