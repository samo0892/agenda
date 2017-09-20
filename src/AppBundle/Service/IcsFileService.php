<?php

namespace AppBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

class IcsFileService
{
    public function createIcsFile($meetingName, $meetingStartTime, $meetingEndTime, $tmpFolder, $location, $description)
    {
        $fs = new Filesystem();
        $uid = rand(5, 1500);
        $meetingStartTimestamp = date("Ymd\THis", strtotime($meetingStartTime));
        $meetingEndTimestamp = date("Ymd\THis", strtotime($meetingEndTime));
        $icsContent = <<<EOF
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:$meetingStartTimestamp
DTEND:$meetingEndTimestamp
DTSTAMP: 20060812T125900Z
ORGANIZER;CN=XYZ:mailto:do-not-reply@example.com
UID:$uid
ATTENDEE;PARTSTAT=NEEDS-ACTION;RSVP= TRUE;CN=Sample:emailaddress@testemail.com
DESCRIPTION: $description
LOCATION:$location
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:$meetingName
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR
EOF;

        $icfFile = $fs->dumpFile($tmpFolder, $icsContent); 
    }
}

