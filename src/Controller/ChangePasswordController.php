<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPassword;
use App\Form\ForceChangePasswordType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChangePasswordController extends AbstractController
{
    /**
     * @Route("/force-password", name="app_changepassword_force")
     * @throws TransportExceptionInterface
     */
    public function forceChangePassword(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        if(!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ForceChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );

            $user->setPassword($newPassword);
            $user->setLastChangePasswordDate(new DateTime());

            $uPassword = new UserPassword();
            $uPassword->setUser($user);
            $uPassword->setPassword($newPassword);

            $entityManager->persist($user);
            $entityManager->persist($uPassword);
            $entityManager->flush();

            $email = (new Email())
                ->from('powiadomienia@example.com')
                ->to($user->getEmail())
                ->subject('Potwierdzenie zmiany hasła.')
                ->text("Twoje hasło zostało pomyślnie zaktualizowane. Jeżeli to nie Ty je zmieniłeś skontaktuj się z administratorem.")
                ->html("<h2>Twoje hasło zostało pomyślnie zaktualizowane.</h2> <p>Jeżeli to nie Ty je zmieniłeś skontaktuj się z administratorem.</p>");

            $mailer->send($email);

            $this->addFlash('success', 'Zmiana hasła zakończona powodzeniem.');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('change-password/force.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
