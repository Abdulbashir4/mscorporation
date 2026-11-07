"""Upload built application files into the dedicated FTP account's root.

Never upload .env, storage, or generated Laravel caches; never delete remote files.
"""

import ftplib
import io
import os
from pathlib import Path
import ssl


def deploy():
    required = ("FTP_SERVER", "FTP_USERNAME", "FTP_PASSWORD")
    if any(not os.environ.get(name) for name in required):
        raise SystemExit("Set FTP_SERVER, FTP_USERNAME and FTP_PASSWORD in Actions secrets.")
    host = os.environ["FTP_SERVER"]
    if "/" in host or ":" in host:
        raise SystemExit("FTP_SERVER must be a hostname only, without a URL or port.")

    with ftplib.FTP_TLS(context=ssl.create_default_context(), timeout=90) as ftp:
        ftp.connect(host, 21)
        ftp.login(os.environ["FTP_USERNAME"], os.environ["FTP_PASSWORD"])
        ftp.prot_p()
        ftp.cwd("/")
        ftp.storbinary("STOR .deploy-uploading", io.BytesIO(b"uploading"))
        directories = set()
        for local in sorted(Path("release").rglob("*")):
            if not local.is_file() or local.is_symlink():
                continue
            relative = local.relative_to("release")
            if relative.parts[0] not in {
                "app", "bootstrap", "config", "database", "public", "resources",
                "routes", "vendor", "artisan", "composer.json", "composer.lock",
            }:
                raise SystemExit("Unexpected deployment file.")
            parent = Path()
            for part in relative.parts[:-1]:
                parent /= part
                remote_dir = parent.as_posix()
                if remote_dir not in directories:
                    try:
                        ftp.mkd(remote_dir)
                    except ftplib.error_perm:
                        ftp.cwd("/" + remote_dir)
                        ftp.cwd("/")
                    directories.add(remote_dir)
            with local.open("rb") as source:
                ftp.storbinary("STOR " + relative.as_posix(), source)
        ftp.storbinary("STOR .deploy-pending", io.BytesIO(b"ready"))
        ftp.delete('.deploy-uploading')
        print("Application uploaded. Waiting for the server deployment cron job.")


if __name__ == "__main__":
    deploy()
