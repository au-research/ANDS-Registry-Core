"""Module defining base class for services.
"""

import email.mime
import email.mime.multipart
import email.mime.text
import smtplib
import sys


class BaseService:
    """Base class for all services.

    Instance attributes available to subclasses:
    debug -- Boolean value; true iff debugging has been enabled.
    conn -- The database connection.
    params -- The service parameters.

    Subclasses must implement the method do_update_citation_data().
    """

    # Additional options required by the service.
    # Change this setting in your subclass.
    required_options = set()

    def __init__(self, conn, params):
        """Constructor. Requires conn and params parameters.

        conn -- The database connection.
        params -- The service parameters.
        """
        # Make sure all required options have been provided.
        options_missing = self.required_options - set(params)
        if options_missing:
            print('Required options missing from section ',
                  params['module'], ':', sep='')
            for missing in options_missing:
                print(missing)
            sys.exit(3)

        self._conn = conn
        self._params = params
        self._debug = 'debug' in params
        self._html_output = 'html_output' in params

    def send_one_email(self, recipient_email,
                       from_header, subject_header,
                       body_text, body_html, body_csv=None):
        """Send one email.

        Arguments:
        recipient_email -- The email address to use as the recipient and
            as the contents of the "To" header.
        from_header -- The address to be put in the "From" header.
        subject_header -- The subject line of the email.
        body_text -- Part one of the body, as plain text.
        body_html -- Part two of the body, as HTML.
        """
        if 'no_emails' in self._params:
            # The user has requested that no emails be sent,
            # so go no further.
            return
        try:
            sender = self._params['sender_email']
            msg = email.mime.multipart.MIMEMultipart('mixed')
            msg_intro = email.mime.multipart.MIMEMultipart('alternative')
            msg['Subject'] = subject_header
            msg['From'] = from_header
            msg['To'] = recipient_email
            text = body_text
            html = body_html
            part1 = email.mime.text.MIMEText(text, 'plain')
            part2 = email.mime.text.MIMEText(html, 'html')
            msg_intro.attach(part1)
            msg_intro.attach(part2)
            msg.attach(msg_intro)
            if body_csv:
                part3 = email.mime.text.MIMEText(body_csv, 'plain')
                part3.add_header("Content-Disposition", "attachment",
                                 filename="linkchecker.csv")
                msg.attach(part3)
            if self._debug:
                print("DEBUG: I would now run sendmail with:",
                      file=sys.stderr)
                print("DEBUG: sender:", sender, file=sys.stderr)
                print("DEBUG: recipient_email:", recipient_email,
                      file=sys.stderr)
                print("DEBUG: msg:", msg.as_string(), file=sys.stderr)
            my_smtp = smtplib.SMTP(self._params['smtp_host'])
            my_smtp.sendmail(sender, recipient_email, msg.as_string())
            my_smtp.quit()
        except Exception as e:
            print('Exception:', e)

    def print_text_or_html(self, text, html):
        """Print either text or HTML, depending on the setting
        of html_output.

        Arguments:
        text -- The message to be printed, as plain text.
        html -- The message to be printed, as HTML.
        """
        if self._html_output:
            print(html)
        else:
            print(text)
