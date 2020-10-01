#!/bin/sh

set -e

usage() {
    echo 'Usage: create-release.sh dir [--tag] [--upload]'
    echo
    echo 'Creates a zip for themes download and optionally tags the git tree and uploads to our files server'
}

if [ "x$1" = "x-h" -o "x$1" = "x--help" ] ; then
    usage
    exit 0
fi
if [ $# -eq 0 ] ; then
    usage
    exit 1
fi

THEME="${1%/}"

./lint-theme.sh $THEME

if [ ! -f $THEME/theme.json ] ; then
    echo
    echo "Please ensure that you have updated data/themes.py in the website code before running this script."
fi

cat <<END

Continue (y/n)?
END
read do_release

if [ "$do_release" != 'y' ]; then
      exit 100
fi

if [ ! -d "$THEME" ] ; then
    echo "Directory $THEME does not exist!"
    exit 2
fi

shift

TAG=0
UPLOAD=0

while [ $# -gt 0 ] ; do
    case "$1" in
        --tag)
            TAG=1
            shift
            ;;
        --upload)
            UPLOAD=1
            shift
            ;;
        *)
            echo "Unknown parameter: $1"
            usage
            exit 1
            ;;
    esac
done

if [ ! -f "$THEME/info.inc.php" ]; then
  VERSION=`php -r "echo json_decode(file_get_contents('$THEME/theme.json'), true)['version'];"`
else
  VERSION=`php -r "include '$THEME/info.inc.php'; echo \\\$theme_full_version;"`
fi
NAME=$THEME-$VERSION

echo "Creating release for $THEME $VERSION ($NAME)"

mkdir -p release

rm -rf release/$NAME* release/$THEME

cp -r $THEME release/$THEME

cd release

7za a -bd -tzip $NAME.zip $THEME
gpg --detach-sign --armor $NAME.zip
sha1sum $NAME.zip > $NAME.zip.sha1
sha256sum $NAME.zip > $NAME.zip.sha256

cd ..

echo "Release files:"
ls -la release/$NAME.zip

if [ $TAG -eq 1 ] ; then
    git tag -s -a -m "Tagging release of theme $THEME $VERSION" $NAME
fi

if [ $UPLOAD -eq 1 ] ; then
    sftp -P 11022 -f -b - files@hertz.phpmyadmin.net <<EOT
cd /mnt/storage/files/themes
-mkdir $THEME
cd $THEME
mkdir $VERSION
cd $VERSION
put release/$NAME.zip
put release/$NAME.zip.asc
put release/$NAME.zip.sha1
put release/$NAME.zip.sha256
EOT
ssh -xka2 -p 11022 files@hertz.phpmyadmin.net ./bin/sync-files-cdn
fi

