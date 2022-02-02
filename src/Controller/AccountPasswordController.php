<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    /**
     * @Route("/account/infos/modification-password", name="account_password")
     */
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $encoder
    ): Response {

        $notification = null;

        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->issubmitted() && $form->isValid()) {
            $old_pswd = $form->get('old_password')->getData();
            //dd($old_pswd);
            //Méthode comparant le mot de passe saisi avec le mot de passe en db
            if ($encoder->isPasswordValid($user, $old_pswd)) {
                //récupération et encodage du nouveau mot de passe
                $new_pswd = $form->get('new_password')->getData();
                $password = $encoder->hashPassword($user, $new_pswd);

                $user->setPassword($password);

                //$this->entityManager->persist($user); nécessaire seulement à la création et non à la mise à jour
                $entityManager->flush();

                $notification = "Votre mot de passe a été modifié avec succès !";
            } else {
                $notification = "Votre mot de passe actuel n'est pas le bon !";
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
