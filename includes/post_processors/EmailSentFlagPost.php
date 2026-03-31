// Post processor for Email Sent Flag - clear the email-sent flag and show warning if it exists
// notify user about email sent flag and provide option to clear it
$emailSentPath = $this->wire('config')->paths->logs.'tracy/email-sent';
if($this->wire('input')->post->clearEmailSent || $this->wire('input')->get->clearEmailSent) {
    if(file_exists($emailSentPath)) {
        $removed = unlink($emailSentPath);
    }
    if (!isset($removed) || !$removed) {
        $this->wire()->error( __('No file to remove'));
    }
    else {
        $this->wire()->message(__("email-sent file deleted successfully"));
        $this->wire('session')->redirect(str_replace(array('?clearEmailSent=1', '&clearEmailSent=1'), '', $this->wire('input')->url(true)));
    }
}

if(file_exists($emailSentPath)) {
    $this->wire()->warning('Tracy Debugger "Email Sent" flag has been set. <a href="'.$this->wire('input')->url(true).($this->wire('input')->queryString() ? '&' : '?').'clearEmailSent=1">Clear it</a> to continue receiving further emails', Notice::allowMarkup);
}
