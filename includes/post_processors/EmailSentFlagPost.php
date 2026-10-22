<?php namespace ProcessWire;
// Post processor for Email Sent Flag - clear the email-sent flag and show warning if it exists
// notify user about email sent flag and provide option to clear it
$emailSentPath = $this->wire('config')->paths->logs.'tracy/email-sent';
// a GET (the link in the notice below) needs the action token or a same-origin request, so another site can't
// clear the flag; the link in error emails therefore just lands on the admin, where this notice offers the link
if(($this->wire('input')->post->clearEmailSent && $this->wire('session')->CSRF->hasValidToken()) || ($this->wire('input')->get->clearEmailSent && TracyDebugger::validActionRequest())) {
    if(file_exists($emailSentPath)) {
        $removed = unlink($emailSentPath);
    }
    if (!isset($removed) || !$removed) {
        $this->wire()->error( __('No file to remove'));
    }
    else {
        $this->wire()->message(__("email-sent file deleted successfully"));
        $this->wire('session')->redirect(str_replace(array('?clearEmailSent=1', '&clearEmailSent=1'), '', TracyDebugger::urlWithoutActionToken($this->wire('input')->url(true))));
    }
}

if(file_exists($emailSentPath)) {
    $this->wire()->warning('Tracy Debugger "Email Sent" flag has been set. <a href="'.TracyDebugger::actionUrl('clearEmailSent').'">Clear it</a> to continue receiving further emails', Notice::allowMarkup);
}
