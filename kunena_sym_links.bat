@echo OFF

Set GitSource=
Set GitTarget=
Set OPT_DELETE=0
Set OPT_DELETE_ALL=0

echo:
echo Link Kunena development tree into your web site.
echo You need to define two variables before to launch the script
echo This script needs administrator rights to run correctly
echo:

SET /p GitSource=GIT repository in ........:
SET /p GitTarget=Joomla installation in ...:

pause

echo:
echo You have set the GIT repository in ........:  %GitSource%
echo You have set the Joomla installation in ...:  %GitTarget%
echo:

if exist %GitTarget%\configuration.php (
goto WHATTODO
) else (
echo You need to have a Joomla! installation to run this script
)

:WHATTODO
echo 1 : Make the symbolic links
echo 2 : Delete the symbolic links
echo 3 : Exit

set /p choice=What do-you want to do ? :
(
if %choice%==1 goto MKLINK
if %choice%==2 goto DELETESYM
if %choice%==3 exit
)
goto:eof

:DELETESYM
IF exist %GitTarget%\modules\mod_kunenalatest ( rmdir /S/q %GitTarget%\modules\mod_kunenalatest )
IF exist %GitTarget%\modules\mod_kunenalogin ( rmdir /S/q %GitTarget%\modules\mod_kunenalogin )
IF exist %GitTarget%\modules\mod_kunenasearch ( rmdir /S/q %GitTarget%\modules\mod_kunenasearch )
IF exist %GitTarget%\modules\mod_kunenastats ( rmdir /S/q %GitTarget%\modules\mod_kunenastats )
IF exist %GitTarget%\plugins\search\kunena ( rmdir /S/q %GitTarget%\plugins\search\kunena )
IF exist %GitTarget%\plugins\content\kunenadiscuss ( rmdir /S/q %GitTarget%\plugins\content\kunenadiscuss )

pause
goto:eof

:MKLINK
echo Delete existing directories
IF exist %GitTarget%\modules\mod_kunenalatest ( rmdir /S/q %GitTarget%\modules\mod_kunenalatest )
IF exist %GitTarget%\modules\mod_kunenalogin ( rmdir /S/q %GitTarget%\modules\mod_kunenalogin )
IF exist %GitTarget%\modules\mod_kunenasearch ( rmdir /S/q %GitTarget%\modules\mod_kunenasearch )
IF exist %GitTarget%\modules\mod_kunenastats ( rmdir /S/q %GitTarget%\modules\mod_kunenastats )
IF exist %GitTarget%\plugins\search\kunena ( rmdir /S/q %GitTarget%\plugins\search\kunena )
IF exist %GitTarget%\plugins\content\kunenadiscuss ( rmdir /S/q %GitTarget%\plugins\content\kunenadiscuss )

echo Make symbolic links
mklink /d %GitTarget%\\modules\mod_kunenalatest %GitSource%\modules\kunenalatest
mklink /d %GitTarget%\modules\mod_kunenalogin %GitSource%\modules\kunenalogin
mklink /d %GitTarget%\modules\mod_kunenasearch %GitSource%\modules\kunenasearch
mklink /d %GitTarget%\modules\mod_kunenastats %GitSource%\modules\kunenastats
mklink /d %GitTarget%\plugins\search\kunena %GitSource%\plugins\search\kunena
mklink /d %GitTarget%\plugins\content\kunenadiscuss %GitSource%\plugins\content\kunenadiscuss

pause
goto:eof







