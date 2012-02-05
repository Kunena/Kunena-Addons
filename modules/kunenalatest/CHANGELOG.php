<?php
/**
* KunenaLatest Module
* @package Kunena latest
*
* @Copyright (C)2010-2012 Kunena Project. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.kunena.org
*/

// no direct access
die( '' );
?>
<!--
Changelog
------------
This is a non-exhaustive (but still near complete) changelog for
the KunenaLatest module, including beta and release candidate versions.
Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

KunenaLatest 1.7.2

5-February-2012 Matias
^ Release 1.7.2

25-January-2012 Matias
# Make module Joomla 2.5 native

28-December-2011 Xillibit
# Parameter favorite under Joomla 1.7 has the wrong label

KunenaLatest 1.7.1

16-November-2011 Matias
^ Prepare 1.7.1 Release

15-November-2011 Matias
^ Update language files

10-November-2011 Xillibit
# [#17] Follow Kunena date format settings (missing part)

1-November-2011 Xillibit
# [#24] Remove setting horizontal/vertical display
# [#21] Kunena Latest Messed Up First Parameter

9-October-2011 Xillibit
^ [#17] Follow Kunena date format settings

4-September-2011 Xillibit
# [#14] Somes issues with validation
# [#14] Somes issues with validation (Part 2)

KunenaLatest 1.7.0

29-August-2011 Matias
^ Kunena Latest 1.7.0 Release

22-August-2011 Matias
^ Updated version to 1.7.0-DEV, Kunena 1.7 required
# [#8] Kunena Latest doesn't obey Load CSS configuration option (if not saved)

09-July-2011 lorddarkangel
- MOD_KUNENALATEST_PARAM_DATEFORMAT_DEFAULT in en-GB. it's not used

04-July-2011 lorddarkangel
+ add el-GR (thanks etkritikonspoudon)

22-June-2011 Matias
# [#25944] Fix missing CSS styling for visitors if caching is turned on

14-June-2011 Xillibit
# [#25944] Typo in en-GB language file

26-May-2011 LDA
# [#25944] added missing strings

24-May-2011 Xillibit
# [#25945] Missing strings in french translation and missing settings in J!1.6

20-May-2011 Xillibit
# [#25945] Under Joomla! 1.6 not possible to see categories list

20-May-2011 LDA
^ [#25944] udpate de-DE (thanks rich)
^ [#25944] update hu-HU (thanks pedrohsi)
+ [#25944] add th-TH (thanks drlovecat)

KunenaLatest 1.6.4

11-May-2011 Xillibit
+ [#24847] Add setting to show number post near to the topic title

03-April-2011 LDA
^ [#24847] update es-ES (thanks Kunena Spanish Team)
^ [#24847] update fi-FI (thanks Mortti)
^ [#24847] update de-DE (thanks rich)

19-March-2011 Severdia
^ [#25396] Added param to not load module CSS file

14-March-2011 Matias
^ [#24415] Update version to 1.6.4

12-March-2011 svens(LDA)
^ [#24847] update ru-RU (thanks Zarkos)

9-March-2011 Matias
- [#24415] Remove category subscriptions option (doesn't work)
^ [#24415] Add 3 minute caching for guests
^ [#24415] Fixed all languages to show right version when installing

6-March-2011 svens(LDA)
^ [#24847] update nb-NO (thanks Joomla! i Norge)

5-March-2011 Xillibit
^ [#25137] mod_kunenalatest lacks paragraph

4-March-2011 svens(LDA)
^ [#24847] add da-DK (thanks Lars Westermann)

20-February-2011 svens(LDA)
^ [#24847] update hu-HU (thanks pedrohsi)

19-February-2011 svens(LDA)
+ [#24847] add tr-TR (thanks cumla)

18-February-2011 svens(LDA)
^ [#24847] update fi-FI (thanks Mortti)

17-February-2011 Matias
# [#24959] Always initialize session if allowed=na

16-February-2011 Xillibit
^ [#24415] Update manifest file to work both on Joomla! 1.5 and Joomla! 1.6

8-February-2011 Severdia
^ [#24823] Optmized CSS selectors based on Page Speed

23-January-2011 LDA(svens)
^ [#24560] update de-DE (thanks rich)
^ [#24560] update ru-RU (thanks Zarkos)
^ [#24560] update fi-FI (thanks Mortti)

23-January-2011 Xillibit
# [#24416] Make tooltips visible when you choose others models than latestposts and latesttopics (Part 2)

16-January-2011 Xillibit
^ [#24415] Update version to 1.6.3-DEV
+ [#24415] New configuration setting to show or hide more link
# [#24416] Make tooltips visible when you choose others models than latestposts and latesttopics
# [#24416] Wrong path for sticky image
# [#24416] Sticky icon is displayed twice is setting is enabled
^ [#24415] Update version to 1.6.3-DEV (missing in mod_kunenalatest.xml)

KunenaLatest 1.6.2

25-December-2010 fxstein
^ [#23293] Update version info to 1.6.2 stable
^ [#23293] Replace hard coded version info in language files with auto expansion
+ [#23293] Add version info auto expansion to builder
^ [#23293] Update min Kunena version requirements to Kunena 1.6.2

24-December-2010 fxstein
^ [#23293] Merged revisions 3803-4058 from /branches/1.6-addons-LDAsvens-language-20101030

22-December-2010 svens(LDA)
^ [#23293] update ru-RU (thanks Zarkos)
^ [#23293] updated fi-FI (thanks Mortti)

21-December-2010 Severdia
^ [#23900] Made file naming and folder structure consistent among Kunena modules
# [#23900] Made CSS class names consistent with prefix

19-December-2010 Severdia
# [#23900] Cleaned up English, updated copyright dates and URLs
^ [#23900] Changed admin UI, grouped elements better
^ [#23900] Added CSS and images folder, minified CSS file

19-November-2010 svens(LDA)
+ [#23293] add es-ES (thanks Alakakentu)

12-Nov-2010 svens(LDA)
+ [#23293] add de-DE (thanks rich)

04-Nov-2010 svens(LDA)
^ [#22975] update ru-RU (thanks ZARKOS)

11-November-2010 Xillibit
+ [#20081] Set the time since which to show the last topics

31-October-2010 Xillibit
# [#20081] Fix undefined variable $myprofile in class.php line 43

30-October-2010 svens LDA
+ [#22975] added nb-NO (thanks rued)
^ [#22975] update ru-RU (thanks ZARKOS)

30-October-2010 fxstein
# [#20081] Fixed syntax error in default.php

30-October-2010 Xillibit
# [#20081] When you choose latestposts or latestmessages the post link open the message at the right page

29-October-2010 Xillibit
^ [#20081] Updated finish language file fi-FI (thanks Mortti)
^ [#20081] Change version to RC3
# [#20081] When you use model=latestposts the time is the same for all messages
^ [#20081] Update in all language files the string description to display RC3
# [#20081] When you use model=latestposts the time is the same for all messages (Part 2 : typo)

24-October-2010 Xillibit
# [#20081] Little change for default unread indicator

17-October-2010 Xillibit
# [#20081] Missing string for default undread indicator
# [#20081] Some functions doesn't go to the right page

16-September-2010 Xillibit
+ [#20081] Added fr-FR translation

16-September-2010 Matias
+ [#20081] Added fi-FI (thanks Mortti), hu-HU (thanks pedrohsi), ru-RU (ZARKOS)
# [#20081] Default new indicator cannot be translated, use ! instead
# [#20081] Category List allowed to be displayed: title for all/none
# [#20081] Default value for display type

KunenaLatest 1.6.0-RC2

30-Aug-2010 fxstein
# [#20081] Use JString:substr to be UTF8 aware

28-Aug-2010 fxstein
# [#20081] Fixed undefined variable error in custom reply mode

25-Aug-2010 fxstein
# [#20081] Display correct name format as avatar tooltip
+ [#20081] Make category, author and time information display optional
- [#20081] Remove retired language string
# [#20081] Convert css ID into classes - modify css
# [#20081] Move main class and helper into class.php to allow for multiple instances on same page
# [#20081] Allow for multiple model instances

24-Aug-2010 fxstein
^ [#20081] Update minimum required Kunena build number to sync up API
+ [#20081] Add TODO list for outstanding work
^ [#20081] New threaded mode logic to merge topic and latest post info for correct display
^ [#20081] Match up new indicator formatting with Kunena default template styling
^ [#20081] Move time display into new line to avoid line breaks
- [#20081] Remove username vs real name config setting as Kunena core is handling this

23-Aug-2010 fxstein
+ [#20081] Load Kunena language file to have access to all Kunena strings in frontend
- [#20081] remove all COM_KUNENA_XXX language string replicas from local language file

23-Aug-2010 Xillibit
+ [#20081] Add configuration setting to choose between name or real name

22-Aug-2010 fxstein
^ [#20081] Simplified latest logic
^ [#20081] Updated formatting
^ [#20081] Pickup date/time display format from Kunena config
^ [#20081] Time format override - leave empty for Kunena config setting
^ [#20081] Separate image formating for avatar vs topic icon
^ [#20081] Cleanup of english config screen language strings

22-Aug-2010 Matias
^ [#20081] Use KunenaTemplate class to load correct icons

21-Aug-2010 Matias
^ [#20081] Better detection on Kunena, minimum required version is Kunena 1.6.0-RC2

19-Aug-2010 fxstein
^ [#20081] Change version to 1.6.0-RC2
# [#20081] Fix: English language file updated
+ [#20081] Add default display model if none of the valid ones have been selected.
^ [#20081] Default NEW indicator moved to language file
^ [#20081] Default NEW indicator changed to (NEW) - like Kunena
^ [#20081] Default date format override move to language file
^ [#20081] Default date format override changed to empty - sample moved to tooltip
^ [#20081] All displaymodel options lowercase
+ [#20081] Additional language strings for various errors
+ [#20081] Add category subscriptions view
- [#20081] Disabled unsupported view types for now

KunenaLatest 1.6.0-RC1

13-Aug-2010 Xillibit
^ [#20081] Little change to show properly the latest messages

11-Aug-2010 Matias
^ [#20081] Change version to 1.6.0-RC1
^ [#20081] Remove elements from module and use the ones in Kunena
^ [#20081] Format php files

08-Aug-2010 Xillibit
^ [#20081] Wrong image link for locked icon
# [#20081] Show messages in right way when you want show only messages

24-July-2010 Xillibit
^ [#20081] Not display posts when the forum is offline (thanks etusha)

11-July-2010 Xillibit
+ [#20081] Add setting to show latest messages or topics

28-Jun-2010 Xillibit
# [#20081] Change link more recent topics when you change view in module config

27-Jun-2010 Xillibit
# [#20081] Put a list to choose the different views to display
# [#20081] Add option to show nothing instead to have avatar or topicicons

27-Jun-2010 Xillibit
# [#20081] Add a configuration setting to choose the view to display (latest post, favorites, subcriptions...)

25-Jun-2010 Xillibit
# [#20081] Fix issues on topic icons and remove all contants depencies
# [#20081] Sow first part of content and add configuration settings for this

24-Jun-2010 Severdia
^ [#20081] Vertical position change, new CSS
^ [#20081] Languages fixes
^ [#20081] Changed date format

24-Jun-2010 Matias
# [#20081] Get date format from configuration option

23-Jun-2010 Matias
# [#20081] Better detection for K1.6
# [#20081] Fix Zend warnings
^ [#20081] Cleanup files
# [#20081] Fix date to use CKunenaTimeformat

19-May-2010 Xillibit
# [#20081] Set configuration setting to choose between categories in or not in
# [#20081] Fix issue after changes which doesn't take care of categories selected


18-May-2010 Xillibit
# [#20081] Leverage directly the CKunenaLatestX instead to have a copy of the class
# [#20081] Put configuration setting to choose vertical or horizontal display type (need css)
# [#20081] Strip BBCodes when you over the thread title

29-Apr-2010 Xillibit
# [#20081] Add option to choose between topic icon or avatar
# [#20081] Add option for choose which categories shows
# [#20081] Add option to define the avatar size

26-Apr-2010 Matias
# [#20081] Small layout fixes
# [#20081] Show the same items as Recent Topics in Kunena

18-Apr-2010 Matias
# [#20081] Fixed wrong avatar if user had been deleted

17-Apr-2010 Matias
# [#20081] Fixed fatal error when there are no latest messages
# [#20081] Do not limit latest messages by time, show last post time instead of thread start time
# [#20081] Even more layout fixes

15-Apr-2010 Matias
# [#20081] Removed user posts configuration option
# [#20081] Improve database error detection
^ [#20081] Show always author name
^ [#20081] More layout fixes

14-Apr-2010 Ron
^ [#20081] Layout fixes

13-Apr-2010 Matias
^ [#20081] Changed module name to mod_kunenalatest, cleanup code and css
^ [#20081] Updated language file to use J1.6 style
+ [#20081] Add missing translations, improve others
+ [#20081] Use new API and profile integration, fix links
# [#20081] Fixed bug where no latest posts were shown to the user
# [#20081] Fixed favorites

9-Apr-2010 Xillibit
^ [#20081] Little change to avoid try to list the results when there are nothing to display

2-Apr-2010 Xillibit
^ [#20081] Set new Id to all files and put build files
^ [#20081] Littles changes on the module filenames
+ [#20081] Initial module structure

-->