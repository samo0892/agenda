<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontendControllerTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testShowPost($url) 
    {
        $client = static::createClient(); //this method returns a client, which is like a browser that you'll use to crawl your site
        $crawler = $client->request('GET', $url); //the client get all the urls from our application from 'urlProvider'
        
        $this->assertTrue($client->getResponse()->isSuccessful()); 
    }
    
    public function urlProvider()
    {
        //this is an array of most of the urls of our application
        return array(
            array('/home'),
            array('/meeting_erstellen'),
            array('/bevorstehende_meetings'),
            array('/abgeschlossene_meetings'),
            array('/details'),
            array('/logout')
        );
    }
    
    public function testCreateMeeting()
    {
        $client = static::createClient(array(), array('HTTP_HOST' => 'skygate-agenda.dev'));
        $client->followRedirects(true);
        $client->request('GET', '/meeting_erstellen'); //the client get all the urls from our application
//        $crawler = $client->request('GET', '/meeting_erstellen');
//        $form = $crawler->selectButton('submit')->form();
        
        $form['name'] = 'Meeting 1';
        $form['date'] = '10-10-2017';
        $form['startTime'] = '16:00';
        $form['endTime'] = '17:30';
        $form['emails'] = 'baldede@skygate.de';
        $form['type'] = 'Sitzung';
        $form['description'] = 'Das ist eine Beschreibung';
        $form['name'] = 'Agenda 1';
        $form['minutes'] = '10';
        $form['files'] = '';
//        $crawler = $client->submit($form);
    }
    
    public function testMenuLink()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/home');
        $link = $crawler
                ->filter('a:contains("Meeting erstellen")') // find all links with the text "Meeting erstellen"
                ->eq(0) // select the second link in the list
//                ->link()
        ;

        // and click it
//        $crawler = $client->click($link);
    }
    
    public function testBrowsing()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'bevorstehende_meetings');
        $crawler = $client->request('GET', 'home');
        $client->back(); //go back to 'bevorstehende_meetings'
        $client->forward(); //and again to 'home'
        $client->reload(); //reload the 'home' site

        // Clears all cookies and the history
        $client->restart();
    }
    
    public function testLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'login');
        
        $form['user'] = 'skygate';
        $form['password'] = 'asd';
        
        $this->assertEquals(
    200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
    $client->getResponse()->getStatusCode()
);
    }
}

