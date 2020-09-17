# aj_install_updater
Updates an AtomJump Messaging installation and the associated plugins.

For this plugin to work, you should install the main messaging server product, and the associated plugins by using a 'git clone' of the public repositories.

You should run the index.php as a regular 5 minute cron job on your server. This will update the front-end files, the latest messages files, the main AtomJump Messaging Server, and it's plugins if there are any changes. If you configure it in a 'stable' state, it will check approximately once an hour at random 5-minute intervals.

You should have another source for the configuration files (either your own git project, or files that you edit directly on your server), which are also updated either automatically or manually.

You need to copy config/messaging-versionsORIGINAL.json to config/messaging-versions.json, and configure the .json parameters to meet your server.

DRAFT version. We haven't yet tested this.