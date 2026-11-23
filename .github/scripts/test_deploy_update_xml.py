import ftplib
import importlib.util
import os
from pathlib import Path
import tempfile
import unittest
from unittest import mock
import xml.etree.ElementTree as ET


spec = importlib.util.spec_from_file_location(
    "deploy_update_xml", Path(__file__).with_name("deploy-update-xml.py")
)
deployment = importlib.util.module_from_spec(spec)
spec.loader.exec_module(deployment)


class DeployUpdateXmlTests(unittest.TestCase):
    def setUp(self):
        self.temp_dir = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp_dir.cleanup)
        self.directory = Path(self.temp_dir.name)
        (self.directory / "list.xml").write_text("<extensionset/>")
        (self.directory / "mod_example.xml").write_text("<updates/>")
        self.environment = mock.patch.dict(
            os.environ,
            {"FTP_HOST": "example.org", "FTP_USERNAME": "user", "FTP_PASSWORD": "password"},
        )
        self.environment.start()
        self.addCleanup(self.environment.stop)

    def test_invalid_or_unprocessed_xml_is_rejected_before_connection(self):
        for content, error in (
            ("<updates><version>@kunenaversion@</version></updates>", ValueError),
            ("<updates>", ET.ParseError),
        ):
            with self.subTest(content=content), mock.patch.object(deployment.ftplib, "FTP_TLS") as ftp:
                (self.directory / "mod_example.xml").write_text(content)
                with self.assertRaises(error):
                    deployment.deploy(self.directory)
                ftp.assert_not_called()

    def test_uploads_all_files_before_publishing_list_last(self):
        with mock.patch.object(deployment.ftplib, "FTP_TLS") as factory:
            ftp = factory.return_value.__enter__.return_value
            deployment.deploy(self.directory)

        ftp.connect.assert_called_once_with("example.org")
        ftp.login.assert_called_once_with("user", "password")
        ftp.prot_p.assert_called_once_with()
        ftp.cwd.assert_called_once_with("/public_html/update/addons")
        calls = ftp.method_calls
        upload_indices = [i for i, call in enumerate(calls) if call[0] == "storbinary"]
        rename_indices = [i for i, call in enumerate(calls) if call[0] == "rename"]
        self.assertEqual(len(upload_indices), 2)
        self.assertLess(max(upload_indices), min(rename_indices))
        self.assertEqual([calls[i].args[1] for i in rename_indices], ["mod_example.xml", "list.xml"])

    def test_upload_failure_does_not_publish_staged_files(self):
        with mock.patch.object(deployment.ftplib, "FTP_TLS") as factory:
            ftp = factory.return_value.__enter__.return_value
            ftp.storbinary.side_effect = [None, ftplib.error_temp("upload failed")]
            with self.assertRaises(ftplib.error_temp):
                deployment.deploy(self.directory)
            ftp.rename.assert_not_called()


if __name__ == "__main__":
    unittest.main()
