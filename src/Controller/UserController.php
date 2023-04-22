<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class UserController extends AbstractController
{
/*    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
*/
    #[Route('/register', name: 'register')]
    public function register(ManagerRegistry $doctrine,Request $request,UserPasswordHasherInterface $passwordHasher): Response
    {
        $user=new User();
        $form=$this->createForm(RegisterType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager=$doctrine->getManager();
            $user->setRole('ROLE_USER');
            $hashedPassword=$passwordHasher->hashPassword($user,$user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTime());
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_task');
        }
        return $this->render('user/register.html.twig', [
            'form' =>$form->createView()
        ]);
    }

    #[Route('/', name: 'login')]
    public function login(AuthenticationUtils $authenticantionUtils) {
        $error = $authenticantionUtils->getLastAuthenticationError();
        $lastUserName=$authenticantionUtils->getLastUsername();
        return $this->render('user/login.html.twig', array(
            'error' => $error,
            'last_username' => $lastUserName
        ));
    } 



}
