<?php

namespace App\Controller;

use App\Entity\Etudiants;
use App\Twig\ConfigExtension;
use App\Form\EtudiantsType;
use App\Form\ProfileEtudiantsType;
use App\Form\ProfileEtudiantsEditPassType;
use App\Repository\EtudiantsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security as secure;


class EtudiantsController extends AbstractController
{
    
    #[Route(path: '/etudiants_profile', name: 'app_profile_etudiants')]
    public function profile_user(Request $request , secure $security , EtudiantsRepository $utilisateursRepository): Response
    {

        $usr = $security->getUser();
      

        $form = $this->createForm(ProfileEtudiantsType::class, $usr);
        
        $form->handleRequest($request);

        if ($form->isSubmitted()  ) {
            // encode the plain password
            $meme_array = ["image/jpeg", "image/jpg", "image/pjpeg", "image/png", "image/x-png", "image/gif"];

            if(array_search($form->get('image')->getData()->getFile()->getMimeType(),$meme_array)!==FALSE){

                $usr->getImage()->manualRemove($usr->getImage()->getAbsolutePath());
                $usr->getImage()->upload();
                $toto = $utilisateursRepository->save($usr, true);
                //dd($toto);
                $this->get('session')->getFlashBag()->add('success', "MOD_SUCCESS");
                // do anything else you need here, like send an email

            }else{
                $this->get('session')->getFlashBag()->add('danger', "msg_doc_inv1");
            }
                   
            

            
        }

        return $this->render('profile_etudiants/profile_etudiants.html.twig', [
            'Profile' => $usr ,
            'ProfileUser' => $form->createView(),
        
        ]);
       
    }

     



    #[Route(path: '/etudiants_pep', name: 'app_profile_etudiants_pep')]
    public function profile_user_change_pass(Request $request , secure $security , EtudiantsRepository $utilisateursRepository , UserPasswordHasherInterface $passwordEncoder): Response
    {
        $user = $security->getUser();
        $form = $this->createForm(ProfileEtudiantsEditPassType::class, $user);
        $form->handleRequest($request);

    
        if ($form->isSubmitted() && $form->isValid() ) {
            

            $old_pwd = $form->get('oldPassword')->getData();
            $new_pwd = $form->get('password')['first']->getData(); 
         //  dd($old_pwd);
            $checkPass = $passwordEncoder->isPasswordValid($user ,$old_pwd) ;     
            // encode the plain password
            if ($checkPass === true) {  

                $this->get('session')->getFlashBag()->add('success', "MOD_SUCCESS");
                    
                    $encoded = $passwordEncoder->hashPassword($user,$new_pwd);
                    $user->setPassword($encoded);
                    $utilisateursRepository->save($user, true);
                    return new RedirectResponse($this->generateUrl('app_login'));
                
                
            } else {  
                $this->get('session')->getFlashBag()->add('success', "MOD_DANGER");
                
                return new RedirectResponse($this->generateUrl('app_profile_user_pep'));    
            }
            
            // do anything else you need here, like send an email

            
        }

        return $this->render('profile_etudiants/profile_etudiants_edit_pass.html.twig', [
            'ProfileUserEditPass' => $form->createView(),
            
        ]);
    }










}
