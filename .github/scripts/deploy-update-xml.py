#!/usr/bin/env python3
import argparse
import ftplib
import os
from pathlib import Path
import re
import ssl
import uuid
import xml.etree.ElementTree as ET


def load_xml_files(directory):
    files = sorted(directory.glob("*.xml"))
    list_file = directory / "list.xml"
    if list_file not in files or len(files) < 2:
        raise ValueError(f"{directory} must contain list.xml and extension XML files")

    for path in files:
        data = path.read_bytes()
        ET.fromstring(data)
        if re.search(rb"@[A-Za-z0-9_]+@", data):
            raise ValueError(f"{path} contains an unresolved build token")

    return [path for path in files if path != list_file] + [list_file]


def deploy(directory):
    files = load_xml_files(directory)
    credentials = [os.environ.get(name) for name in ("FTP_HOST", "FTP_USERNAME", "FTP_PASSWORD")]
    if not all(credentials):
        raise ValueError("FTP_HOST, FTP_USERNAME, and FTP_PASSWORD must be configured")

    host, username, password = credentials
    with ftplib.FTP_TLS(context=ssl.create_default_context(), timeout=30) as ftp:
        ftp.connect(host)
        ftp.login(username, password)
        ftp.prot_p()
        ftp.cwd("/public_html/update/addons")

        staged = []
        for path in files:
            temporary_name = f".{path.name}.{uuid.uuid4().hex}.tmp"
            with path.open("rb") as source:
                ftp.storbinary(f"STOR {temporary_name}", source)
            staged.append((temporary_name, path.name))

        for temporary_name, name in staged:
            ftp.rename(temporary_name, name)
            print(f"Published {name}")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Publish generated Joomla update XML files.")
    parser.add_argument("directory", type=Path)
    deploy(parser.parse_args().directory)
