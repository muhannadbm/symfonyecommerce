<?php 
// src/Controller/CityController.php
namespace App\Controller;


use App\Entity\City;
use App\Form\CityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CityRepository;



/**
 * @Route("/admin")
 */

class CityController extends AbstractController
{

     /**
     * @Route("/city", name="city_index", methods="GET|POST")
     */
    public function index(CityRepository $cityRepository): Response
    {

        return $this->render('admin/city/index.html.twig', [


            'cities' => $cityRepository->findAll()]);
    }



    /**
     * @Route("/city/addcity", name="addcity", methods="GET|POST")
     */
    public function new (Request $request): Response
    {
        $submittedToken = $request->request->get('_token');
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($city);
            $em->flush();

            return $this->redirectToRoute('city_index');
        }

        return $this->render('admin/city/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }






    /**
     * @Route("city/{id}/edit", name="city_edit", methods="GET|POST")
     */
    public function edit(Request $request, City $city): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success','');

            return $this->redirectToRoute('city_index');
        }

        return $this->render('admin/city/edit.html.twig', [
            'city' => $city,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("city/delete/{id}", name="city_delete", methods="DELETE")
     */
    public function delete(Request $request, City $city): Response
    {
        if ($this->isCsrfTokenValid('delete'.$city->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($city);
            $em->flush();
        }

        return $this->redirectToRoute('city_index');
    }





}
