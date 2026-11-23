
[![Kunena Add-ons](https://www.kunena.org/images/logo.png)](https://www.kunena.org)



## ABOUT

*Kunena* is a native Joomla forum and communications component written in PHP. *Kunena* enables forums, bulletin board, support forums, discussions and comments for a Joomla! base website.

This repository contains Kunena Add-ons.

## UPDATE XML DEPLOYMENT

The GitHub Actions workflow deploys `update/*.xml` from the published release
tag to `/public_html/update/addons/` over explicit FTPS. It runs for stable
releases and pre-releases. The workflow must exist on the repository's default
branch to receive release events.

Configure the repository Actions secrets `FTP_HOST`, `FTP_USERNAME`, and
`FTP_PASSWORD`. The FTP server must support explicit TLS on port 21, a trusted
TLS certificate, and renaming files in the destination directory. The workflow
does not fall back to unencrypted FTP.

Before creating the release tag, generate all update XML files with Phing and
include the generated files in the tag. Deployment stops if the XML is invalid
or still contains build tokens such as `@kunenaversion@`. Extension files are
published first and `list.xml` last; unrelated server files are not removed.

## TRANSLATIONS

Kunena is on Crowdin - Help us to translate : https://crowdin.com/project/kunena-addons/