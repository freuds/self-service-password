<?php
#==============================================================================
# LTB Self Service Password
#
# Copyright (C) 2009-2017 Clement OUDOT
# Copyright (C) 2009-2017 LTB-project.org
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# GPL License: http://www.gnu.org/licenses/gpl.txt
#
#==============================================================================

/* @function boolean send_sms_by_api(string $mobile, string $message)
 * Send SMS trough an API
 * @param mobile mobile number
 * @param message text to send
 * @return 1 if message sent, 0 if not
 */
function send_sms_by_api($mobile, $message) {

    //------------------------------
    // sender API Esendex
    //------------------------------
    global $esendex_username, $esendex_password, $esendex_account, $esendex_originator,
        $sms_mail_to, $sms_mail_from, $sms_mail_from_name, $sms_mail_subject;

    $defaultStatus = array("Submitted", "Sent");

    /* Requires PHP SDK  */
    $esendex_message = new \Esendex\Model\DispatchMessage(
        $esendex_originator, /* Send from */
        $mobile, /* Send to any valid number */
        $message,
        \Esendex\Model\Message::SmsType
    );
    $authentication = new \Esendex\Authentication\LoginAuthentication(
        $esendex_account,
        $esendex_username,
        $esendex_password
    );

    // Send SMS
    $service = new \Esendex\DispatchService($authentication);
    $result = $service->send($esendex_message);
    print $result->id();

    // Get SMS status
    $headerService = new \Esendex\MessageHeaderService($authentication);
    $messageStatus = $headerService->message($result->id());
    $returnStatus = (string)$messageStatus->status();
    print $messageStatus->status();

    if ( ! in_array(trim($returnStatus), $defaultStatus) )
    {
        $data = array();
        $body = "Message Status : ".var_export($messageStatus, true)."\n";
        $body .= "Message detail : ".var_export($message, true)."\n";
        $body .= "Destination Number : ".var_export($mobile, true)."\n";
        send_mail("PHPMailer", $sms_mail_to, $sms_mail_from, $sms_mail_from_name, $sms_mail_subject, $body, $data);
        return 0;
    }
    return 1;
}

?>
