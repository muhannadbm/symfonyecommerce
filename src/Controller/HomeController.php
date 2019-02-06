<?php

namespace App\Controller;
use App\Entity\Admin\Messages;
use App\Entity\User;
use App\Form\Admin\MessagesType;
use App\Form\UserType;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ImageRepository;
use App\Repository\Admin\ProductRepository;
use App\Repository\Admin\SettingRepository;
use App\Repository\ShopcartRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class HomeController extends AbstractController
{



    /**
     * @Route("/{_locale}/home", name="home")
     */
    public function index(SettingRepository $settingRepository,Request $request, CategoryRepository $categoryRepository,ShopcartRepository $shopcartRepository)
    {


        $data=$settingRepository->findAll();

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM product WHERE status='True' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $slider=$statement->fetchAll();
        $sql1="SELECT * FROM product WHERE status='True' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql1);
        $statement->execute();
        $slider1=$statement->fetchAll();



        $sql="SELECT * FROM product WHERE status='new' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $new=$statement->fetchAll();


        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        $cats[0] = '<ul id="menu-v" >';



        return $this->render('home/index.html.twig', [
            'data' => $data,
            'cats' =>$cats,
            'slider' =>$slider,
            'slider1' =>$slider1,
            'new' =>$new,
        ]);
    }



    /**
     * @Route("/{_locale}/allproducts", name="all_products")
     */
    public function all(ProductRepository $productRepository)
    {

        $em=$this->getDoctrine()->getManager();
        $all=$productRepository->findAll();

        $sql="SELECT * FROM product WHERE status='new' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $new=$statement->fetchAll();
        $sql="SELECT * FROM product WHERE status='True' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $slider=$statement->fetchAll();


        $cats = $this->categorytree();
        $cats[0] = '<ul id="menu-v" >';


        return $this->render('home/all.html.twig', [
            'all' => $all,
            'cats' =>$cats,
            'new' =>$new,
            'slider'=>$slider,

        ]);
    }




    /**
     * @Route("/{_locale}/product/{id}", name="product_detail", methods="GET|POST")
     */
    public function ProductDetail ($id,ProductRepository $productRepository)
    {


        $data=$productRepository->findBy(['id'=>$id]);
        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM product WHERE status='new' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $new=$statement->fetchAll();


        return $this->render('home/products.html.twig', [
            'data' => $data,
            'new' =>$new,

        ]);

    }

    /**
     * @Route("/{_locale}/count", name="count_index", methods="GET")
     */

    public function front(ShopcartRepository $shopcartRepository)
    {

        $user= $this->getUser();
        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);



        return $this->render('home/frontbase.html.twig', [
            'shopcount' => $shopcount,
            ]);
    }




    /**
     * @Route("/{_locale}/products/{id}", name="product_details", methods="GET")
     */
    public function ProductDetails ($id,ProductRepository $productRepository,ImageRepository $imageRepository,Request $request)
    {


        $data=$productRepository->findBy(['id'=>$id]);
        $image=$imageRepository->findBy(['product_id'=>$id]);
        $cats= $this->categorytree($parent=0,$user_tree_array='',$request);
        $cats[0] ='<ul id="menu-v">';


        return $this->render('home/products_detail.html.twig', [
            'data' => $data,
            'image' => $image,
            'cats' =>$cats,

        ]);

    }



    /**
     * @Route("/{_locale}/category/{catid}", name="category_products", methods="GET|POST")
     */
    public function CategoryProducts ($catid,CategoryRepository $categoryRepository, Request $request)
    {

        $cats=$this->categorytree($parent=0,$user_tree_array='', $request);
        $cats[0] = '<ul id="menu-v" >';
        $data=$categoryRepository->findBy(['id'=>$catid]);

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM product WHERE status=('True' OR'new')   AND category_id= :catid";
        $statement =$em->getConnection()->prepare($sql);
        $statement ->bindValue('catid', $catid);
        $statement->execute();
        $products=$statement->fetchAll();

        $sql="SELECT * FROM product WHERE status='new' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $new=$statement->fetchAll();

        $sql="SELECT * FROM product WHERE status='True' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $slider=$statement->fetchAll();;
        return $this->render('home/products.html.twig', [
            'data' => $data,
            'new' =>$new,
            'slider' => $slider,
            'products' => $products,
            'cats' =>$cats,
        ]);

}




    public function categorytree($parent=0,$user_tree_array='',Request $request) {

        if(!is_array($user_tree_array))
            $user_tree_array=array();

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM category WHERE status='True' AND parentid= ".$parent;
        $statement =$em->getConnection()->prepare($sql);
        $statement->execute();
        $result=$statement->fetchAll();
        $locale = $request->getLocale();
        if(count($result) > 0){
            $user_tree_array[]="<ul>";
            foreach($result as $row) {

                if ($row["parentid"] == 0) {

                    $user_tree_array[] = "<li><a  >" . $row['title'] . " </a>";
                    $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);


                }
                else {

                    $user_tree_array[] = "<li><a  href='/$locale/category/" . $row['id'] . " '>" . $row['title'] . " </a>";
                    $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);
                } }
                $user_tree_array[] = "</li> </ul>";
            }
            return $user_tree_array;
        }




    /**
     * @Route("/{_locale}/aboutus", name="aboutus")
     */
    public function aboutus(SettingRepository $settingRepository)
    {

        $data=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'data' => $data,
        ]);
    }


    /**
     * @Route("/{_locale}/reference", name="reference")
     */
    public function reference(SettingRepository $settingRepository)
    {

        $data=$settingRepository->findAll();
        return $this->render('home/reference.html.twig', [
            'data' => $data,
        ]);
    }






    /**
     * @Route("/{_locale}/newuser", name="new_user", methods="GET|POST")
     */
    public function new_user(Request $request, UserRepository $userRepository): Response
    {

        $user= new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token');

        if ($form->isSubmitted() ) {
            if ($this->isCsrfTokenValid('user-form', $submittedToken))
            {
                $emaildata=$userRepository->findBy(['email'=>$user->getEmail()] );
                if($emaildata==null) {


                    $em = $this->getDoctrine()->getManager();
                    $user->setRoles("ROLE_USER");
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('success','Your account has been created Successfully');
                    return $this->redirectToRoute('app_login');
        }
        else {  $this->addFlash('error',$user->getEmail()." is Already Registered Please Sign in Or Use another Email");
        } } }





        return $this->render('home/signup.html.twig', [
            'user' => $user,
            'form'=>$form->createView(),

        ]);
    }







    /**
     * @Route("/{_locale}/contact", name="contact", methods="GET|POST")
     */
    public function contact(SettingRepository $settingRepository, Request $request)
    {

        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token');

        if ($form->isSubmitted() ) {
            if($this->isCsrfTokenValid('form-message',$submittedToken))
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $this->addFlash('success','Your Message Was Recieved');
            return $this->redirectToRoute('contact');
        }



        $data=$settingRepository->findAll();
        return $this->render('home/contact.html.twig', [
            'data' => $data,
            'form'=>$form->createView(),
            'message'=>$message,
        ]);
    }






}
