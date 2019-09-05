# eReg-Binder v2.0
This is the eRegulatory Binder REDCap plugin. The plugin contains 3 reports

1. PROTOCOL VERSION/AMENDMENT TRACKING LOG - eReg binder  (va_tracking.php)
2. Consent Form tracking - eReg binder (eb_consent_report.php)
3. Delegation Log - eReg binder (create_eb_report.php)
4. Delegation Log (v2) - delegation_log_report.php

# Installation and Requirements
The eReg Binder project is a series of REDCap Plugins. More information regarding plugins can be found the REDCap FAQ.
The eReg Binder Project was developed and tested to be compatible with PHP 5.6 or above.

In order to setup the eReg Binder project, you need to:
- Have access to your REDCap server (SSh/Remote Desktop)
- Have the "redcap_connect.php" file present and up-to-date within your REDCap webroot (i.e. if your REDCap instance is running in Linux and is setup in /var/www/redcap, then you should have the /var/www/redcap/redcap_connect.php file)
- The REDCap plugins folder should be created and accessible to the web (ex: /var/www/redcap/plugins)

1. Clone the repository. You can clone it directly in your plugins folder.
   git clone https://github.com/PHSERIS/eReg-Binder.git
   (or https://github.com/PHSERIS/eReg-Binder.git eregbinder) ... this is for easier references and will clone the project into a folder called "eregbinder". You can adjust that name as needed.
   when the colne is complete you should have something like "/var/www/redcap/plugins/eReg-Binder")
2. Login to REDCap and navigate to your project's Project Setup screen.
3. Click on the "Add or edit Bookmarks" button in the "Setup Project Bookmarks" section
- In the last row the table provide a:
-- Label for the link in the "Enter the label for the link as it is seen on the left-hand menu" box
-- The web address for the plugins in the "Enter the web address" box
-- Make sure that the "Append project ID to URL" checkbox is selected/checked
Example:
Label: "Consent Form tracking"
URL: https://redcap_example.domain.org/redcap/plugins/eReg-Binder/eb_consent_report.php
Click the "Add" button
A new link should appear in the left-hand navigation menu under "Project Bookmarks"

Repeat these steps with all relevant plugins listed above.

For additional information, please refer to the Manual included in the Manual folder.
