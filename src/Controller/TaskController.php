<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskController extends AbstractController
{
    #[Route('/tareas', name: 'app_task')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $tasks=$doctrine->getRepository(Task::class)->findby([],['id'=>'DESC']);
        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
            'tasks'=>$tasks
        ]);
    }

    #[Route('/tarea/{id}', name: 'detail_task')]
    public function detail(Task $task): Response
    {
        if (!$task){
            return $this->redirect('app-task');
        }else {
            return $this->render('task/detail.html.twig', [
                'controller_name' => 'TaskController::detail',
                'task'=>$task
            ]);
        }
    }

    #[Route('/crea-tarea', name: 'create_task')]
    public function create(ManagerRegistry $doctrine,Request $request,UserInterface $user): Response
    {
        $task=new Task();
        $form=$this->createForm(TaskType::class,$task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new \DateTime('now'));
            $task->setUser($user);
            $em=$doctrine->getManager();
            $em->persist($task);
            $em->flush();
            return $this->redirect($this->generateUrl('detail_task',['id'=>$task->getId()]));
        }
        return $this->render('task/create.html.twig',[
            'edit' => false,
            'form' => $form->createView()
        ]);
    }

    #[Route('/mis-tareas', name: 'my_tasks')]
    public function myTasks(UserInterface $user): Response
    {
        $tasks=$user->getTasks();
        return $this->render('task/my-tasks.html.twig', [
            'controller_name' => 'TaskController',
            'tasks'=>$tasks
        ]);
    }

    #[Route('/edita-tarea/{id}', name: 'edit_task')]
    public function edit(ManagerRegistry $doctrine,UserInterface $user, Request $request,Task $task): Response
    {
        if ($user && $user->getId() == $task->getUser()->getId()) {
            $form=$this->createForm(TaskType::class,$task);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em=$doctrine->getManager();
                $em->persist($task);
                $em->flush();
                return $this->redirect($this->generateUrl('detail_task',['id'=>$task->getId()]));
            }
            return $this->render('task/create.html.twig',[
                'edit' => true,
                'form' => $form->createView()
            ]);
        } 
        else {
            return $this->redirectToRoute('app_task');
        }
    }

    #[Route('/borra-tarea/{id}', name: 'delete_task')]
    public function delete(ManagerRegistry $doctrine,UserInterface $user, Request $request,Task $task): Response
    {
        if ($user && $user->getId() == $task->getUser()->getId()) {
            if ($task){
                $em=$doctrine->getManager();
                $em->remove($task);
                $em->flush();
            }
        } 
        return $this->redirectToRoute('app_task');
    }


}
