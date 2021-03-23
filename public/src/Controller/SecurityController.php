<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\Admin\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\ShopcartRepository;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(ShopcartRepository $shopcartRepository, AuthenticationUtils $authenticationUtils,Request $request,SettingRepository $settingRepository): Response
    {
        // get the login error if there is one
        $data=$settingRepository->findAll();
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        $user= $this->getUSER();
        if($user) {
            return $this->redirectToRoute('userpanel_show');
            $userid= $user->getid();
            $shopcount=$shopcartRepository->getUserShopCartCount($userid);
      
        } else { $shopcount=0; }
 
        
        
        $cats[0] = '';


        return $this->render('security/login.html.twig', ['shopcount' => $shopcount,'last_username' => $lastUsername,'cats' =>$cats, 'error' => $error,'data'=>$data]);
    }



    /**
     * @Route("/loginerror", name="app_login_error")
     */
    public function loginerror(AuthenticationUtils $authenticationUtils,SettingRepository $settingRepository): Response
    {
        // get the login error if there is one
        $data=$settingRepository->findAll();
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $this->addFlash('error','You dont have the authority to enter the requested domain');



        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error,'data'=>$data]);
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
