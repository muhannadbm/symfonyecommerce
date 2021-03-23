<?php

namespace App\Controller\Userpanel;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ShopcartRepository;

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
    public function edit(ShopcartRepository $shopcartRepository, Request $request): Response
    {

        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        
        $cats[0] = '';
        $usersession=$this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($usersession->getid());
        
        $user= $this->getUSER();
        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);
        $user= $this->getUSER();
        if($user) {
      
            $userid= $user->getid();
            $shopcount=$shopcartRepository->getUserShopCartCount($userid);
      
        } else { $shopcount=0; }
 

        if($request->isMethod('POST')) {
             $submittedtoken=$request->request->get('token');
             if($this->isCsrfTokenValid('user-form', $submittedtoken)){
                //  $user->setName($request->request->get("name"));

                 $user->setAddress($request->request->get("address"));
                 $user->setCity($request->request->get("city"));
                 $user->setPhone($request->request->get("phone"));
                 $this->getDoctrine()->getManager()->flush();
                 $this->addFlash('success','User Info Has Been Updated Successfully');
                 return $this->redirectToRoute('userpanel_show');


}


        }


        return $this->render('userpanel/_form.html.twig', ['shopcount' => $shopcount,'user' => $user,'cats' =>$cats]);
    }

    /**
     * @Route("/show", name="userpanel_show",methods="GET")
     */
    public function show(ShopcartRepository $shopcartRepository, Request $request)
    {
        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        
        $cats[0] = '';

        $user= $this->getUSER();
        if($user) {
      
            $userid= $user->getid();
            $shopcount=$shopcartRepository->getUserShopCartCount($userid);
      
        } else { $shopcount=0; }
 


        return $this->render('userpanel/show.html.twig', ['shopcount' => $shopcount,'cats' =>$cats]);
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
