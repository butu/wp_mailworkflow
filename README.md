# TYPO3 extension `wp_mailworkflow`


This extension offers the possibility, to send mails from a defined queue.
For example: Someone is registering on your website and now should get a set of Mails in the next days or weeks.

## Installation
Install the extension and include the TypoScript Template.

## Mailset and Mails
You can define a Mailset in the TYPO3 List-Module, based on some Mails. Each Mail has the following settings:
- Title (only an internal label)
- Days To Send (the amount of days from creating a recipient to send this mail)
- Send Time (define a daytime, when you want to send this mail)
- Mail-Subject
- Mail-Text

## Recipients
In our Backend-Module you can create Recipients. Of course, you can do this also in your Extbase-Extension. A Recipient has the following fields:
- Start (define the Start Date and Time for sending the first Mail)
- First Name
- Last Name
- Email
- Parameter1 (optional for your use)
- Parameter2
- Parameter3
- Parameter4
- Parameter5
- Mail Group (choose one of your predefined Mailsets)

You can use every field from above as placeholder in your Mailtext.

For example:
Hello {firstName},

Available placeholders are: {firstName}, {lastName}, {email}, {parameter1}, {parameter2}, {parameter3}, {parameter4}, {parameter5},

On saving the Recipient, the Mailqueue get filled with all Mails from the Mailset.

## Queue
In our Backend-Module you find another view: Queue. Here you can see all Mails to the Recipients. You can delete Mails which are not sent. You see the Send-date and -time of already sent Mails.

## Scheduler
You need a Scheduler-Task to send Mails out:
- go to the Scheduler in your TYPO3-Backend
- create a new Task and choose "Execute console commands"
- at "Schedulable Command. Save and reopen to define command arguments" choose: "wpmailworkflow:send-queue: Send mails from the queue."
- save and then fill out the appearing Arguments
- make other Settings of your choice and save

The mails get send as defined in your TYPO3 configuration in the Installtool.

## Make your Mail fancy
Copy the delivered HTML Templates to your Sitepackage and adapt the paths in the Scheduler settings.
Now you can create a nice style of your Mails.
