<?php

namespace AppBundle\Controller;

use AppBundle\Form\CreateMeetingType;
use function PHPSTORM_META\elementType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Form\LoginType;
use AppBundle\Entity\Meeting;
use AppBundle\Form\DetailsType;
use AppBundle\Form\StartedMeetingType;
use AppBundle\Form\CompletedDetailsType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Knp\Snappy\Pdf;

class FrontendController extends Controller {
    
    /**
     * @Route("/test", name="test")
     */
    public function testAction() {
        return $this->render('default/aaa.html.twig');
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
                    'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * Renders the login page to use the SkyGate-Agenda
     * 
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request) {
        $user = new User();
        $session = $request->getSession();

        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');
        $userId = $session->get('id');

        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $form->getData();
            $username = $userData->getUser();
            $password = $userData->getPassword();

            $userFromDb = $repository->findOneBy(array(
                'user' => $username
            ));

            if (!$userFromDb) {
                $this->addFlash(
                        'error', 'Benutzer ist nicht vorhanden.'
                );
            } else {
                $userId = $userFromDb->getID();
                $session->set('id', $userId);
                $session->get('id');

                if ($userFromDb instanceof \AppBundle\Entity\User) {

                    $passwordFromDb = $userFromDb->getPassword();
                    //$isPasswordValid = $encoder->isPasswordValid($userFromDb, $password);

                    if ($password == $passwordFromDb) {
                        return $this->render('default/homepage.html.twig'); //, array('name' => $userFromDb->getUser()));
                    }
                }

                $this->addFlash(
                        'error', 'Benutzername oder Passwort ist falsch.'
                );
            }
        }

        return $this->render('default/login.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction() {
        $session = $this->get('session');
        $session->set('id', '');
        return $this->render('default/need_login.html.twig', array());
    }

    /**
     * Renders the home page after succesfull login
     * 
     * @Route("/home", name="home")
     */
    public function homeAction(Request $request) {
        $session = $request->getSession();
        $userId = $session->get('id');

        if ($userId) {
            return $this->render('default/homepage.html.twig');
        } else {
            return $this->render('default/need_login.html.twig', array());
        }
    }

    /**
     * Renders the site to create a new meeting
     * 
     * @Route("/meeting_erstellen", name="create_meeting")
     */
    public function createMeetingAction(Request $request) {
        $meeting = new Meeting();
        $session = $request->getSession();
        $userId = $session->get('id');

        $icsFileName = 'meeting.ics';
        $tmpFolder = ($_SERVER["DOCUMENT_ROOT"] . '/AppBundle/tmp/' . $icsFileName);

        if ($userId) {
            $templatePath = 'emails/created_meeting.html.twig';
            $showForm = true;
            $form = $this->createForm(CreateMeetingType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {                
                $em = $this->getDoctrine()->getManager();
                $meeting = $form->getData();
                $agendas = $meeting->getAgendas();
                
                /**
                 * Adds and saves the uploaded file in $filePath 
                 */
                $file = $form->get('file')->getData();
                if($file){
//                    foreach($file as $file){
                        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                        $file = $file->move(('brochures_directory/' /*.$form->get('name')->getData()*/), $fileName);

                        $filePath = 'file:///' . $file->getRealPath();
                        $filePath = str_replace('\\', '/', $filePath); // Replace backslashes with forwardslashes
                        $meeting->setFile($file);
//                    }
                }
                
                $em->persist($meeting);
                $em->flush();

                /**
                 * Creates the agenda points of the meeting
                 */
                foreach($agendas as $agenda) {
                    $agenda->setMeeting($meeting);
                    $em->persist($agenda);
                }
                $em->flush();
                
                $this->addFlash('notice', 'Das Meeting wurde erfolgreich erstellt.');
                
                
                /**
                 * Creates an ics-file and sends to the participants via mail
                 */
                $meetingName = $form->get('name')->getData();
                $meetingStartTime = $form->get('date')->getData()->format("d-m-Y'T'HHmmss");
                $meetingEndTime = $form->get('date')->getData()->format("d-m-Y'T'HHmmss");
                $location = $form->get('place')->getData();
                
                $this->get('ics_file_service')->createIcsFile($meetingName, $meetingStartTime, $meetingEndTime, $tmpFolder, $location);

                if($file){
                $sendThisMail = $this->get('email_service')
                    ->sendEmailToParticipants($form, $templatePath, $tmpFolder, $filePath, $fileName); //if a file (PDF,Doc,...) is given
                } else {
                    $sendThisMail = $this->get('email_service')
                    ->sendEmailToParticipants($form, $templatePath, $tmpFolder, $filePath='', $fileName=''); //if NOT a file is given
                }
                
                /**
                 * Checks, if sending of the mail was successfull
                 * If creating the meeting was successful redirects to 'actual meeting' else redirect to 'creating meetings'
                 */
                if ($sendThisMail) {
                    $this->addFlash('notice', 'Die Mail wurde erfolgreich gesendet.');
                } else {
                    $this->addFlash('error', 'Die Mail konnte nicht gesendet werden!');
                }

                return $this->redirectToRoute('actual_meetings');
            }

            return $this->render('default/create_meeting.html.twig', array(
                        'form' => $form->createView(), 'showform' => $showForm, 'meeting' => $meeting)
            );
        } else {
            return $this->render('default/need_login.html.twig', array());
        }
    }

    /**
     * Shows all actuall meetings, which aren't finished
     * 
     * @Route("/bevorstehende_meetings", name="actual_meetings")
     */
    public function actualMeetingAction(Request $request) {
        $session = $request->getSession();
        $userId = $session->get('id');

        if ($userId) {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');
            $meetingFromDb = $repository->findByIsComplete('0');
            return $this->render('default/actual_meetings.html.twig', array('meetings' => $meetingFromDb));
        } else {
            return $this->render('default/need_login.html.twig', array());
        }
    }

    /**
     * Shows all meetings, which are finished
     * 
     * @Route("/abgeschlossene_meetings", name="completed_meetings")
     */
    public function completedMeetingAction(Request $request) {
        $session = $request->getSession();
        $userId = $session->get('id');

        if ($userId) {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');
            $meetingFromDb = $repository->findByIsComplete('1');
            return $this->render('default/completed_meetings.html.twig', array('meetings' => $meetingFromDb));
        } else {
            return $this->render('default/need_login.html.twig', array());
        }
    }

    /**
     * Starts a meeting from the list of actual meetings
     * 
     * @Route("/meeting_starten", name="start")
     * @param Request $request
     */
    public function startMeetingAction(Request $request) {
        $session = $request->getSession();
        $userId = $session->get('id');
        $appPath = $this->container->getParameter('kernel.root_dir');
        $webPath = realpath($appPath . '/../web');
        
        if ($userId) {
            $form = $this->createForm(StartedMeetingType::class);
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');
            $agendaRepo = $this->getDoctrine()->getRepository('AppBundle:Agenda');
            $meeting = $repository->findOneBy(['id' => $_GET['id']]);
            $meeting_id = $meeting->getId();
            $agendas = $agendaRepo->findBy(['meeting' => $_GET['id']]);
            $meeting_name = $meeting->getName();
            $meeting_files = $meeting->getFile();
            
            $meeting_minutes = [];
            
            foreach($agendas as $agenda){
                $meeting_minutes[] = $agenda->getMinutes();
//                dump($meeting_minutes);
            }
//            die;
            
            if ($form->isSubmitted() && $form->isValid()) {
                
            }

            return $this->render('default/started_meeting.html.twig', array('agendas' => $agendas, 'meeting_name' => $meeting_name, 'meeting_minutes' => $meeting_minutes, 
                'meeting_files' => $meeting_files, 'form' => $form->createView(), 'meeting_id' => $meeting_id));
        } else {
            return $this->render('default/need_login.html.twig', array());
        }
    }

    /**
     * Deletes finished or not started meetings
     * 
     * @Route("/loeschen", name="delete")
     */
    public function deleteMeetingAction(Request $request) {
        $meeting = new Meeting();
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        $previousUrl = $request->headers->get('referer');

        if ($userId) {
            $meeting = $repo->findOneBy(['id' => $_GET['id']]);
            $em = $this->getDoctrine()->getManager();

            $em->remove($meeting);
            $em->flush();
            $this->addFlash(
                    'notice', 'Das Meeting wurde erfolgreich gelöscht.'
            );

            
            return $this->redirect($previousUrl);
        } else {
            return $this->render('default/login.html.twig');
        }
    }
    

    /**
     * Shows the details of a meeting and possibles to change them
     * 
     * @Route("/details", name="details")
     */
    public function detailsAction(Request $request) {
        $meeting = new Meeting();
        $form = $this->createForm(DetailsType::class, $meeting);
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        $showForm = true;

        if ($userId) {
            $meetings = $repo->findAll();
            $meeting = $repo->findOneBy(['id' => $_GET['id']]);
            $em = $this->getDoctrine()->getManager();
//            s$fileName = $form->get('file')->setData($meeting->getFile());
//            dump($fileName);die;

            if ($meeting) {
                $this->get('detail_service')->getDetails($form, $meeting);
            }
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('delete')->isClicked()) {
                    $em->remove($meeting);
                    $em->flush();
                    $meetings = $repo->findAll();
                    $this->addFlash(
                            'notice', 'Der User wurde erfolgreich gelöscht.'
                    );

                    return $this->render('default/details.html.twig', array('form' => $form->createView(), 'user' => $user));
                } else {
                    $this->get('detail_service')->updateDetails($form, $meeting);

                    $this->addFlash(
                            'notice', 'Die Daten wurden erfolgreich aktualisiert.'
                    );

                    return $this->render('default/details.html.twig', array('form' => $form->createView(), 'meeting' => $meeting));
                }
            }

            return $this->render(
                            'default/details.html.twig', array('form' => $form->createView(), 'showForm' => $showForm)
            );
        } else {
            return $this->render('default/need_login.html.twig', array());
        }
    }

    /**
     * Shows the details of a completed meeting and possibles to change them
     * 
     * @Route("/completed_details", name="completed_details")
     */
    public function completedDetailsAction(Request $request) {
        $meeting = new Meeting();
        $form = $this->createForm(CompletedDetailsType::class, $meeting);
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        $showForm = true;

        if ($userId) {
            $meeting = $repo->findAll();
            $meeting = $repo->findOneBy(['id' => $_GET['id']]);
            $meeting_id = $meeting->getId();
            $em = $this->getDoctrine()->getManager();

            if ($meeting) {
                $this->get('detail_service')->getDetails($form, $meeting);;
            }

            return $this->render(
                            'default/completed_details.html.twig', array('form' => $form->createView(), 'showForm' => $showForm, 'id' => $meeting_id)
            );
        } else {
            return $this->render('default/need_login.html.twig', array());
        }
    } 
    
    /**
     * 
     * @Route("/dokument_speichern", name="save_document")
     */
    public function saveDocumentsAction(Request $request) {
        $meeting = new Meeting();
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        $previousUrl = $request->headers->get('referer');
        $form = $this->createForm(StartedMeetingType::class);
        $form->handleRequest($request);

        if ($userId) {
            $meeting = $repo->findOneBy(['id' => $_GET['id']]);
            $em = $this->getDoctrine()->getManager();

            
            $em->flush();
            $this->addFlash(
                    'notice', 'Das Meeting wurde erfolgreich gelöscht.'
            );

            
            return $this->redirect($previousUrl);
        } else {
            return $this->render('default/login.html.twig');
        }
    }
}
