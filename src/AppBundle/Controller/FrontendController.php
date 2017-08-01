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
use AppBundle\Form\CompletedDetailsType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class FrontendController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }



    /**
     * Renders the login page to use the SkyGate-Agenda
     * 
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
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
                            return $this->render('default/homepage.html.twig');//, array('name' => $userFromDb->getUser()));
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
    public function logoutAction()
    {
        $session = $this->get('session');
        $session->set('id', '');
        return $this->render('default/need_login.html.twig', array());
    }

    /**
     * Renders the home page after succesfull login
     * 
     * @Route("/home", name="home")
     */
    public function homeAction(Request $request)
    {
        $session = $request->getSession();
        $userId = $session->get('id');

        if($userId) {
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
    public function createMeetingAction(Request $request)
    {
        $meeting = new Meeting();
        $session = $request->getSession();
        $userId = $session->get('id');
        $fs = new Filesystem();
        
        
        
        $tmpFolder = 'AppBundle/tmp/';
        $icsFileName = 'meeting.ics';

        if($userId) {
            $templatePath = 'emails/created_meeting.html.twig';
            $showForm = true;
            $meeting->addAgenda('');

            $form = $this->createForm(CreateMeetingType::class, $meeting);
            $form->handleRequest($request);
                
//            
//            $meetingStartTime = $form->get('date')->getData($meeting->getDate());
//            $location = $form->get('place')->getData($meeting->getPlace());
//            $string = serialize($meetingStartTime);
////            $a = date('Ymd\THis', strtotime($string));
////            var_dump($a);die;
//            
//            $icsContent = "
//                BEGIN:VCALENDAR
//                VERSION:2.0
//                CALSCALE:GREGORIAN
//                METHOD:REQUEST
//                BEGIN:VEVENT
//                DTSTART:".date('Ymd\THis', strtotime($string))."
//                DTSTAMP:".date('Ymd\THis', strtotime($string))."
//                ORGANIZER;CN=XYZ:mailto:do-not-reply@example.com
//                UID:".rand(5, 1500)."
//                ATTENDEE;PARTSTAT=NEEDS-ACTION;RSVP= TRUE;CN=Sample:emailaddress@testemail.com
//                DESCRIPTION: requested Phone/Video Meeting Request
//                LOCATION:" .$location. "
//                SEQUENCE:0
//                STATUS:CONFIRMED
//                SUMMARY:Meeting has been scheduled by 
//                TRANSP:OPAQUE
//                END:VEVENT
//                END:VCALENDAR"
//            ;
//            
//            $icfFile = $fs->dumpFile($tmpFolder.$icsFileName, $icsContent);

            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form->get('file')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file = $file->move(('brochures_directory'), $fileName);
                $filePath = 'file:///' .$file->getRealPath();
                $filePath = str_replace('\\', '/', $filePath); // Replace backslashes with forwardslashes
                $meeting2 = $meeting->setFile($file);
                $em = $this->getDoctrine()->getManager();
                $em->persist($meeting);
                $em->flush();
                
                $mailBody = $this->get('email_service')->renderHtmlMail($form, $templatePath, $filePath);
                $mailBody .= "<a href='$filePath'>" .$fileName. "</a>";
                $subject = "Ein neues Meeting wurde erstellt" ;
                $sendTo = $form->get('objective')->getData($meeting->getObjective());
                
                $sendThisMail = $this->get('email_service')->sendHtmlEmail($subject, $mailBody, $sendTo /*,$tmpFolder, $a */);
                
                if($sendThisMail){
                    $this->addFlash('notice', 'Die Mail wurde erfolgreich gesendet.');
                }else{
                    $this->addFlash('error', 'Die konnte nicht gesendet werden!');
                }

                //clears the form fields
                unset($meeting);
                unset($form);
                
                //creates a new blank form
                $meeting = new Meeting();
                $form = $this->createForm(CreateMeetingType::class, $meeting);


                //if creating the meeting was successful
                $this->addFlash(
                    'notice', 'Meeting wurde erfolgreich erstellt.'
                );
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
    public function actualMeetingAction(Request $request)
    {
        $session = $request->getSession();
        $userId = $session->get('id');

        if ($userId){
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');
            $meetingFromDb = $repository->findByIsComplete(NULL);

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
    public function completedMeetingAction(Request $request)
    {
        $session = $request->getSession();
        $userId = $session->get('id');

        if($userId) {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');
            $isComplete = '1';

            $meetingFromDb = $repository->findBy(array(
                'isComplete' => $isComplete
            ));
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
    public function startMeetingAction(Request $request){
        
    }
    
    /**
     * Deletes finished or not started meetings
     * 
     * @Route("/loeschen", name="delete")
     */
    public function deleteMeetingAction(Request $request){
        $meeting = new Meeting();
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        $previousUrl = $request->headers->get('referer');
        
        if($userId){
            $meeting = $repo->findOneBy(['meeting_id' => $_GET['id']]);
            $em = $this->getDoctrine()->getManager();
            
            $em->remove($meeting);
            $em->flush();
            $this->addFlash(
                'notice', 'Das Meeting wurde erfolgreich gelöscht.'
            );

            return $this->redirect($previousUrl);
        }else {
            return $this->render('default/login.html.twig');
        }    
    }
    
    /**
     * Sends a mail to all participant of a meeting
     * 
     * @Route("/send_mail", name="send_mail")
     */
    public function sendMailAction(Request $request){
        
    }
    
    /**
     * Shows the details of a meeting and possibles to change them
     * 
     * @Route("/details", name="details")
     */
    public function detailsAction(Request $request){
        $meeting = new Meeting();
        $form = $this->createForm(DetailsType::class, $meeting);
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        $showForm = true;
        
        if ($userId) {
            $meetings = $repo->findAll();
            $meeting = $repo->findOneBy(['meeting_id' => $_GET['id']]);
            $em = $this->getDoctrine()->getManager();
//            s$fileName = $form->get('file')->setData($meeting->getFile());
//            dump($fileName);die;
            
            if ($meeting) {
                $form->get('meeting_name')->setData($meeting->getMeetingName());
                $form->get('date')->setData($meeting->getDate());
                $form->get('time')->setData($meeting->getTime());
                $form->get('place')->setData($meeting->getPlace());
                $form->get('objective')->setData($meeting->getObjective());
                $form->get('isAttending')->setData($meeting->getIsAttending());
//                $form->get('file')->setData($meeting->getFile());
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
                        $newData = $meeting;
                        $newData->setMeetingName($form->get('meeting_name')->getData());
                        $newData->setDate($form->get('date')->getData());
                        $newData->setPlace($form->get('place')->getData());
                        $newData->setObjective($form->get('objective')->getData());
                        $newData->setIsAttending($form->get('isAttending')->getData());
                        $newData->setFile($form->get('file')->getData());

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($meeting);
                        $em->flush();

                        $this->addFlash(
                                'notice', 'Die Daten wurden erfolgreich aktualisiert.'
                        );

                        return $this->render('default/details.html.twig', array('form' => $form->createView(), 'meeting' => $meeting));
                    }
            }
            
            return $this->render(
                            'default/details.html.twig', array('form' => $form->createView(), 'showForm' => $showForm)
            );
        }else {
            return $this->render('default/need_login.html.twig', array());
        }
    }
    
    /**
     * Shows the details of a completed meeting and possibles to change them
     * 
     * @Route("/completed_details", name="completed_details")
     */
    public function completedDetailsAction(Request $request){
        $meeting = new Meeting();
        $form = $this->createForm(CompletedDetailsType::class, $meeting);
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        $showForm = true;
        
        if($userId){
            $meeting = $repo->findAll();
            $meeting = $repo->findOneBy(['meeting_id' => $_GET['id']]);
            $meeting_id = $meeting->getMeetingId();
            $em = $this->getDoctrine()->getManager();
            
            if ($meeting){
                $form->get('meeting_name')->setData($meeting->getMeetingName());
                $form->get('date')->setData($meeting->getDate());
                $form->get('time')->setData($meeting->getTime());
                $form->get('place')->setData($meeting->getPlace());
                $form->get('objective')->setData($meeting->getObjective());
                $form->get('isAttending')->setData($meeting->getIsAttending());
                $form->get('file')->setData($meeting->getFile());
            }
            
            return $this->render(
                            'default/completed_details.html.twig', array('form' => $form->createView(), 'showForm' => $showForm, 'meeting_id' => $meeting_id)
            );
            
        }else {
            return $this->render('default/need_login.html.twig', array());
        }
    }
}
