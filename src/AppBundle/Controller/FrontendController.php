<?php

namespace AppBundle\Controller;

use AppBundle\Entity\File;
use AppBundle\Form\CreateMeetingType;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

class FrontendController extends Controller {
    
    /**
     * @Route("/test", name="test")
     */
    public function testAction() {
        return $this->render('pdf/pdf.html.twig');
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
                $files = $meeting->getFiles();
                
                
                /**
                 * Adds and saves the uploaded file in $filePath 
                 */
                foreach($files as $file){
                    if($file){
                        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                        $file = $file->move('brochures_directory', $fileName);

                        $filePath = 'file:///' . $file->getRealPath();
                        $filePath = str_replace('\\', '/', $filePath); // Replace backslashes with forwardslashes

                        $meetingFile = new File;
                        $meetingFile->setName($fileName);
                        $meetingFile->setPath($filePath);
                        $meetingFile->setMeeting($meeting);
                        $fileArray[] = $meetingFile;
                    }
                }
                
                if(!empty($fileArray)){
                    $meeting->setFiles($fileArray);
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
                
                $meetingStartHour = $form->get('startTime')->getData()->format("H:m");
                $meetingStartTime = $form->get('date')->getData()->format("d-m-Y ");
                $meetingStartTime .= $meetingStartHour;
                $meetingEndHour = $form->get('endTime')->getData()->format("H:m");
                $meetingEndTime = $form->get('date')->getData()->format("d-m-Y ");
                $meetingEndTime .= $meetingEndHour;
                $description = $form->get('description')->getData();
                $location = $form->get('place')->getData();
                
                $this->get('ics_file_service')->createIcsFile($meetingName, $meetingStartTime, $meetingEndTime, $tmpFolder, $location, $description);

                if(isset($file)){
                    $sendThisMail = $this->get('email_service')
                    ->sendEmailToParticipants($form, $templatePath, $tmpFolder, $fileArray); //if a file (PDF,Doc,...) is given
                } else {
                    $sendThisMail = $this->get('email_service')
                    ->sendEmailToParticipants($form, $templatePath, $tmpFolder, $filePath=''); //if NOT a file is given
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

            date_default_timezone_set('Europe/Berlin');
        if ($userId) {
            $headNames = array('Name','Date', 'Place', 'StartTime', 'Options');
            $dir = isset($_GET['dir']) ? $_GET['dir'] : 'DESC';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : '' ;
            
            if ($dir == "ASC") {
                $dir = "DESC";
            } else {
                $dir = "ASC";
            }
            
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');

            if($sort == 'Options'){
                $sort = '';
            }
            if($sort){
                $query = $repository->createQueryBuilder("meeting")
                    ->select('meeting.id', 'meeting.name', 'meeting.date', 'meeting.place', 'meeting.startTime')
                    ->where('meeting.isComplete = 0')
                    ->orderBy("meeting." . lcfirst($sort), $dir)
                    ->getQuery();
            }else{
                $query = $repository->createQueryBuilder("meeting")
                        ->select('meeting.id', 'meeting.name', 'meeting.date', 'meeting.place', 'meeting.startTime')
                        ->where('meeting.isComplete = 0')
                        ->orderBy("meeting.date", 'ASC')
                        ->getQuery();
            }
            
            $meetingFromDb = $query->execute(); //execute();
            $page = $request->query->has("page") && intval($request->query->get("page")) > 1 ? intval($request->query->get("page")) : 1;


        $pagerfanta = new Pagerfanta(new ArrayAdapter($meetingFromDb));
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page);

        $currentPageResults = $pagerfanta->getCurrentPageResults();
            
            return $this->render('default/actual_meetings.html.twig', array('meetings' => $currentPageResults, 'dir' => $dir, 'sort' => $sort, 'my_pager' => $pagerfanta, 'headNames' => $headNames));
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
            $headNames = array('Name','Date', 'Place', 'Options');
            $dir = isset($_GET['dir']) ? $_GET['dir'] : 'DESC';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : '' ;
            
            if ($dir == "ASC") {
                $dir = "DESC";
            } else {
                $dir = "ASC";
            }
            
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');
            if($sort == 'Options'){
                $sort = '';
            }
            if($sort){
                $query = $repository->createQueryBuilder("meeting")
                    ->select('meeting.id', 'meeting.name', 'meeting.date', 'meeting.place', 'meeting.startTime')
                    ->where('meeting.isComplete = 1')
                    ->orderBy("meeting." .lcfirst($sort), $dir)
                    ->getQuery();
            }else{
                $query = $repository->createQueryBuilder("meeting")
                        ->select('meeting.id', 'meeting.name', 'meeting.date', 'meeting.place', 'meeting.startTime')
                        ->where('meeting.isComplete = 1')
                        ->orderBy("meeting.date", 'DESC')
                        ->getQuery();
            }
            
            $meetingFromDb = $query->execute(); //execute();
            
            $page = $request->query->has("page") && intval($request->query->get("page")) > 1 ? intval($request->query->get("page")) : 1;


        $pagerfanta = new Pagerfanta(new ArrayAdapter($meetingFromDb));
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page);

        $currentPageResults = $pagerfanta->getCurrentPageResults();
            
            
            
            return $this->render('default/completed_meetings.html.twig', array('meetings' => $currentPageResults, 'dir' => $dir, 'sort' => $sort, 'my_pager' => $pagerfanta, 'headNames' => $headNames));
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
        $startedMeeting = new \AppBundle\Entity\StartedMeeting();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        
        if ($userId) {
            $form = $this->createForm(StartedMeetingType::class);
            
            $repository = $this->getDoctrine()->getRepository('AppBundle:Meeting');
            $agendaRepo = $this->getDoctrine()->getRepository('AppBundle:Agenda');
            $fileRepo = $this->getDoctrine()->getRepository('AppBundle:File');
            $meeting = $repository->findOneBy(['id' => $_GET['id']]);
            $meeting_id = $meeting->getId();
            $agendas = $agendaRepo->findBy(['meeting' => $_GET['id']]);
            $meeting_files = $fileRepo->findBy(['meeting' => $_GET['id']]);
            $meeting_name = $meeting->getName();
            $meeting_date = $meeting->getDate()->format('d.m.Y');
            $meeting_description = $meeting->getDescription();
            $meeting_type = $meeting->getType();
            
            $meeting_minutes = [];
            foreach($agendas as $agenda){
                $meeting_minutes[] = $agenda->getMinutes();
            }
            
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $startedMeeting = $form->getData();
                foreach($startedMeeting as $sm){
                    foreach($sm as $s){
                        $date = $s->getDate()->format('d.m.Y');
                        $notice = $s->getNotice();
                        $person = $s->getPerson();
                        $type = $s->getType();
                    }
                }
                
                $pdfFilename = 'dokument_' .$meeting_name. '.pdf';
//                dump($pdfFilename);die;
                $pdfFolder = 'uploads/pdf-files/';
                
                $pdfConfiguration = array_merge(array(
                    'disable-external-links' => true,
                    'page-size' => 'A4',
                    'disable-javascript' => true,
                    'margin-top'    => 0,
                    'margin-right'  => 0,
                    'margin-bottom' => 0,
                    'margin-left'   => 0,
                    'quiet' => true
                ));
                
//                $html = $this->renderView('pdf/pdf.html.twig', array('startedMeetings' => $sm, 'date' => $date, 'notice' => $notice, 'person' => $person, 'type' => $type,
//                    'meeting_name' => $meeting_name, 'meeting_date' => $meeting_date));
//                return new Response(
//                    $this->get('knp_snappy.pdf')->getOutputFromHtml(array($html, $pdfFolder)),
//                    200,
//                    array(
//                        'Content-Type'        => 'application/pdf',
//                        'Content-Disposition' => sprintf('attachment; filename="%s"', $pdfFilename)
//                    )
//                );
                
                
                $this->get('knp_snappy.pdf')->generateFromHtml(
                    $this->renderView(
                        'pdf/pdf.html.twig', array(
                        'startedMeetings' => $sm, 'date' => $date, 'notice' => $notice, 'person' => $person, 'type' => $type,
                        'meeting_name' => $meeting_name, 'meeting_date' => $meeting_date, 'meeting_type' => $meeting_type)
                    ),
                    'uploads/pdf-files/' .$pdfFilename,
                    $pdfConfiguration,
                    true //overwriting existing file
                );
                
                
                
                $participants = $meeting->getEmails();
                $pdfFile = 'uploads/pdf-files/' .$pdfFilename;
                $participant = explode(';', $participants);
                foreach($participant as $p)
                {
                   $message = (new \Swift_Message('Protokoll für das Meeting ' .$meeting_name))
                    ->setFrom('baldede@skygate.de')
                    ->setTo($p)
                    ->setBody(
                        $this->renderView(
                            // app/Resources/views/Emails/registration.html.twig
                            'emails/created_protocoll_mail.html.twig',
                            array('meeting_name' => $meeting_name,
                                'meeting_date' => $meeting_date,
                                'pdf_file' => $pdfFile, 'meeting_type' => $meeting_type)
                        ),
                        'text/html'
                    );

                    $this->get('mailer')->send($message);
                }
                if($message){
                    $meeting = $repo->findOneBy(['id' => $_GET['id']]);
                    $em = $this->getDoctrine()->getManager();
                    $meeting->setPdfFile($pdfFilename);
                    $meeting->setIsComplete(1);
                    $em->flush();
                } else {
                    echo "Fehler!";
                }
                
                return $this->redirectToRoute('home');
            }

            return $this->render('default/started_meeting.html.twig', array('agendas' => $agendas, 'meeting_name' => $meeting_name, 'meeting_minutes' => $meeting_minutes, 
                'meeting_files' => $meeting_files, 'form' => $form->createView(), 'meeting_id' => $meeting_id, 'meeting_description' => $meeting_description));
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
        $meeting_files = new File;
        $form = $this->createForm(DetailsType::class, $meeting);
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $fileRepo = $this->getDoctrine()->getRepository('AppBundle:File');
        $agendaRepo = $this->getDoctrine()->getRepository('AppBundle:Agenda');
        $userId = $session->get('id');
        $showForm = true;
        $icsFileName = 'meeting.ics';
        $tmpFolder = ($_SERVER["DOCUMENT_ROOT"] . '/AppBundle/tmp/' . $icsFileName);

        if ($userId) {
            $meetings = $repo->findAll();
            $meeting = $repo->findOneBy(['id' => $_GET['id']]);
            $meeting_files = $fileRepo->findBy(['meeting' => $_GET['id']]);
            $agendas = $agendaRepo->findBy(['meeting' => $_GET['id']]);
//            dump($meeting_files);die;
            $em = $this->getDoctrine()->getManager();
            
            if ($meeting) {
                $this->get('detail_service')->getDetails($form, $meeting, $meeting_files, $agendas);
            }
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('delete')->isClicked()) {
                    $em->remove($meeting);
                    $em->flush();
                    $meetings = $repo->findAll();
                    $this->addFlash(
                            'notice', 'Das Meeting wurde erfolgreich gelöscht.'
                    );

                    return $this->render('default/actual_meetings.html.twig', array('form' => $form->createView(),'meetings' => $meetings));
                } else {
                    $updatedDetails = $this->get('detail_service')->updateDetails($form, $meeting);
                    
                /**
                 * Creates an ics-file and sends to the participants via mail
                 */
                $meetingName = $form->get('name')->getData();
                
                $meetingStartHour = $form->get('startTime')->getData()->format("H:m");
                $meetingStartTime = $form->get('date')->getData()->format("d-m-Y ");
                $meetingStartTime .= $meetingStartHour;
                $meetingEndHour = $form->get('endTime')->getData()->format("H:m");
                $meetingEndTime = $form->get('date')->getData()->format("d-m-Y ");
                $meetingEndTime .= $meetingEndHour;
                $description = $form->get('description')->getData();
                $location = $form->get('place')->getData();
                
                $this->get('ics_file_service')->createIcsFile($meetingName, $meetingStartTime, $meetingEndTime, $tmpFolder, $location, $description);
                    
                $fileArray = $fileRepo->findBy(['meeting' => $_GET['id']]);
                $templatePath = 'emails/updated_meeting_mail.html.twig';
                if($updatedDetails){
                    $sendThisMail = $this->get('email_service')
                    ->sendEmailToParticipants($form, $templatePath, $tmpFolder, $fileArray); //if a file (PDF,Doc,...) is given
                }

                    $this->addFlash(
                            'notice', 'Die Daten wurden erfolgreich aktualisiert.'
                    );

                    return $this->render('default/details.html.twig', array('form' => $form->createView(), 'meeting' => $meeting));
                }
            }

            return $this->render(
                            'default/details.html.twig', array('form' => $form->createView(), 'showForm' => $showForm, 'meeting_files' => $meeting_files)
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
        $fileRepo = $this->getDoctrine()->getRepository('AppBundle:File');
        $agendaRepo = $this->getDoctrine()->getRepository('AppBundle:Agenda');
        $userId = $session->get('id');
        $showForm = true;
        $meeting_files = $fileRepo->findBy(['meeting' => $_GET['id']]);
        $agendas = $agendaRepo->findBy(['meeting' => $_GET['id']]);

        if ($userId) {
            $meeting = $repo->findAll();
            $meeting = $repo->findOneBy(['id' => $_GET['id']]);
            $meeting_id = $meeting->getId();
            $em = $this->getDoctrine()->getManager();

            if ($meeting) {
                $this->get('detail_service')->getDetails($form, $meeting, $meeting_files, $agendas);
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
    
    /**
     * 
     * @Route("/protokoll_anzeigen", name="show_protocoll")
     */
    public function showProtocoll(Request $request){
        $meeting = new Meeting();
        $session = $request->getSession();
        $repo = $this->getDoctrine()->getRepository('AppBundle:Meeting');
        $userId = $session->get('id');
        
        if($userId){
            $meeting = $repo->findOneBy(['id' => $_GET['id']]);
            $pdfFile = $meeting->getPdfFile();
            $path = 'uploads/pdf-files/';
            $response = new BinaryFileResponse('uploads/pdf-files/' .$pdfFile);

            $response->headers->set('Content-Type', 'application/pdf');
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE, //use ResponseHeaderBag::DISPOSITION_ATTACHMENT to save as an attachement
                $pdfFile
            );
            
//            return new BinaryFileResponse('uploads/pdf-files/' .$pdfFile);
            return $response;
        }
    }
}
